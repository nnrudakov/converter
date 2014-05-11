<?php

/**
 * Конвертер сезонов, чемпионато и этапов.
 *
 * @package    converter
 * @subpackage contracts
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
    private $progressFormat = "\rSeasons: %d. Championships: %d. Stages: %d.";

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
        $this->convertSeasons();
        $this->convertChamps();

        file_put_contents($this->seasonsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->seasons, true)));
        file_put_contents($this->champsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->champs, true)));
        file_put_contents($this->stagesFile, sprintf(self::FILE_ACCORDANCE, var_export($this->stages, true)));
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
            $season = new FcSeason();
            $season->title   = $s->title;
            $season->description = $s->description;
            $season->fromtime  = $s->dts;
            $season->untiltime = $s->dte;

            if (!$season->save()) {
                throw new CException(
                    'Season not created.' . "\n" .
                    var_export($season->getErrors(), true) . "\n" .
                    $s . "\n"
                );
            }

            $this->doneSeasons++;
            $this->progress();

            $this->seasons[$s->id] = (int) $season->id;
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

            $this->doneChamps++;
            $this->progress();
            $this->champs[$t->id] = $champ->id;

            /* @var Stages $s */
            foreach ($t->stages as $s) {
                $stage = new FcStage();
                $stage->championship_id = $champ->id;
                $stage->title = $s->short;
                $stage->fullTitle = $s->title;
                $stage->style = $s->isCap() ? FcStage::STYLE_CAP : FcStage::STYLE_ROUND;
                $stage->reglament = $s->reglamentar;

                if (!$stage->save()) {
                    throw new CException(
                        'Stage not created.' . "\n" .
                        var_export($stage->getErrors(), true) . "\n" .
                        $s . "\n"
                    );
                }

                $this->doneStages++;
                $this->progress();
                $this->stages[$s->id] = $stage->id;
            }
        }
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneSeasons, $this->doneChamps, $this->doneStages);
    }
}
