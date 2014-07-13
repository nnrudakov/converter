<?php

/**
 * Конвертер сезонов, чемпионато и этапов.
 *
 * @package    converter
 * @subpackage champs
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class ChampsConverter implements IConverter
{
    /**
     * Соотвествие текущих сезонов новым.
     *
     * @var array
     */
    private $seasons = [];

    /**
     * Соотвествие чемпионатов.
     *
     * @var array
     */
    private $champs = [];

    /**
     * Соотвествие этапов.
     *
     * @var array
     */
    private $stages = [];

    /**
     * Файл соответствий текущих идентификаторов сезонов новым.
     *
     * @var string
     */
    private $seasonsFile = '';

    /**
     * Файл соответствий текущих идентификаторов чемпионатов новым.
     *
     * @var string
     */
    private $champsFile = '';

    /**
     * Файл соответствий текущих идентификаторов этапов новым.
     *
     * @var string
     */
    private $stagesFile = '';

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rSeasons: %d (%d). Championships: %d (%d). Stages: %d (%d).";

    /**
     * @var integer
     */
    private $doneSeasons = 0;

    /**
     * @var integer
     */
    private $doneChamps = 0;

    /**
     * @var integer
     */
    private $doneStages = 0;

    /**
     * Инициализация.
     *
     * @throws CException
     */
    public function __construct()
    {
        $this->seasonsFile = Yii::getPathOfAlias('accordance') . '/seasons.php';
        $this->champsFile  = Yii::getPathOfAlias('accordance') . '/champs.php';
        $this->stagesFile  = Yii::getPathOfAlias('accordance') . '/stages.php';
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
        $this->seasons = $this->getSeasons();
        $this->convertSeasons();
        //$this->convertChamps();

        ksort($this->seasons);
        /*ksort($this->champs);
        ksort($this->stages);*/
        file_put_contents($this->seasonsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->seasons, true)));
        /*file_put_contents($this->champsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->champs, true)));
        file_put_contents($this->stagesFile, sprintf(self::FILE_ACCORDANCE, var_export($this->stages, true)));*/
    }

    /**
     * Перенос сезонов.
     */
    private function convertSeasons()
    {
        $criteria = new CDbCriteria();
        $criteria->select = ['id', 'title', 'description', 'dts', 'dte'];
        $criteria->order = 'id';
        $src_seasons = new Seasons();

        foreach ($src_seasons->findAll($criteria) as $s) {
            $exists_season = FcSeason::model()->exists(
                new CDbCriteria(['condition' => 'title=:title', 'params' => [':title' => $s->title]])
            );

            if ($exists_season) {
                continue;
            }

            $season = new FcSeason();
            //$season->importId = $s->id;
            $season->title = $s->title;
            $season->description = $s->description;
            $season->fromtime = $s->dts;
            $season->untiltime = $s->dte;

            if (!$season->save()) {
                throw new CException(
                    'Season not created.' . "\n" .
                    var_export($season->getErrors(), true) . "\n" .
                    $s . "\n"
                );
            }

            $this->seasons[$s->id][$season->lang] = (int) $season->id;
            $season->setNew(true);
            $season->save();
            $this->seasons[$s->id][$season->lang] = (int) $season->id;

            $this->doneSeasons++;
            $this->progress();
        }
    }

    /**
     * Перенос чемпионатов.
     */
    private function convertChamps()
    {
        $criteria = new CDbCriteria();
        $criteria->select = ['id', 'title', 'short', 'sponsor'];
        $criteria->order  = 'id';
        $src_champs = new Tournaments();

        foreach ($src_champs->findAll($criteria) as $t) {
            $champ = new FcChampionship();
            //$champ->importId = $t->id;
            $champ->title = $t->short;
            $champ->fullTitle = $t->title;
            $champ->sponsor = $t->sponsor;

            if (!$champ->save()) {
                throw new CException(
                    'Championship not created.' . "\n" .
                    var_export($champ->getErrors(), true) . "\n" .
                    $t . "\n"
                );
            }

            $this->champs[$t->id][$champ->lang] = $champ_ru = (int) $champ->id;
            $champ->setNew(true);
            $champ->save();
            $this->champs[$t->id][$champ->lang] = $champ_en = (int) $champ->id;

            $this->doneChamps++;
            $this->progress();

            /* @var Stages $s */
            foreach ($t->stages as $s) {
                $stage = new FcStage();
                //$stage->importId = $s->id;
                $stage->championship_id = $champ_ru;
                $stage->title = $s->short;
                $stage->fullTitle = $s->title ?: $t->title;
                $stage->style = $s->isCap() ? FcStage::STYLE_CUP : FcStage::STYLE_ROUND;
                $stage->reglament = $s->reglamentar;

                if (!$stage->save()) {
                    throw new CException(
                        'Stage not created.' . "\n" .
                        var_export($stage->getErrors(), true) . "\n" .
                        $s . "\n"
                    );
                }

                $this->stages[$s->id][$stage->lang] = (int) $stage->id;
                $stage->setNew(true);
                $stage->save();
                $this->stages[$s->id][$stage->lang] = (int) $stage->id;

                $this->doneStages++;
                $this->progress();
            }
        }
    }

    public function getSeasons()
    {
        return file_exists($this->seasonsFile) ? include $this->seasonsFile : [];
    }

    public function getChamps()
    {
        return file_exists($this->champsFile) ? include $this->champsFile : [];
    }

    public function getStages()
    {
        return file_exists($this->stagesFile) ? include $this->stagesFile : [];
    }

    private function progress()
    {
        printf(
            $this->progressFormat,
            $this->doneSeasons,
            $this->doneSeasons * 2,
            $this->doneChamps,
            $this->doneChamps * 2,
            $this->doneStages,
            $this->doneStages * 2
        );
    }
}
