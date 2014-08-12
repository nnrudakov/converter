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
     * @var array
     */
    private $seasonsM = [];

    /**
     * Соотвествие чемпионатов.
     *
     * @var array
     */
    private $champs = [];

    /**
     * @var array
     */
    private $champsM = [];

    /**
     * Соотвествие этапов.
     *
     * @var array
     */
    private $stages = [];

    /**
     * @var array
     */
    private $stagesM = [];

    /**
     * Файл соответствий текущих идентификаторов сезонов новым.
     *
     * @var string
     */
    private $seasonsFile = '';

    /**
     * @var string
     */
    private $seasonsFileM = '';

    /**
     * Файл соответствий текущих идентификаторов чемпионатов новым.
     *
     * @var string
     */
    private $champsFile = '';

    /**
     * @var string
     */
    private $champsFileM = '';

    /**
     * Файл соответствий текущих идентификаторов этапов новым.
     *
     * @var string
     */
    private $stagesFile = '';

    /**
     * @var array
     */
    private $stagesFileM = '';

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
        $this->seasonsFile  = Yii::getPathOfAlias('accordance') . '/seasons.php';
        $this->seasonsFileM = Yii::getPathOfAlias('accordance') . '/seasons_m.php';
        $this->champsFile   = Yii::getPathOfAlias('accordance') . '/champs.php';
        $this->champsFileM  = Yii::getPathOfAlias('accordance') . '/champs_m.php';
        $this->stagesFile   = Yii::getPathOfAlias('accordance') . '/stages.php';
        $this->stagesFileM  = Yii::getPathOfAlias('accordance') . '/stages_m.php';
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
        $this->seasons  = $this->getSeasons();
        $this->seasonsM = $this->getSeasonsM();
        $this->champs   = $this->getChamps();
        $this->champsM  = $this->getChampsM();
        $this->stages   = $this->getStages();
        $this->stagesM  = $this->getStagesM();

        //$this->convertSeasons();
        $this->convertChamps();

        /*ksort($this->seasons);
        ksort($this->seasonsM);*/
        ksort($this->champs);
        ksort($this->champsM);
        ksort($this->stages);
        ksort($this->stagesM);
        /*file_put_contents($this->seasonsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->seasons, true)));
        file_put_contents($this->seasonsFileM, sprintf(self::FILE_ACCORDANCE, var_export($this->seasonsM, true)));*/
        file_put_contents($this->champsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->champs, true)));
        file_put_contents($this->champsFileM, sprintf(self::FILE_ACCORDANCE, var_export($this->champsM, true)));
        file_put_contents($this->stagesFile, sprintf(self::FILE_ACCORDANCE, var_export($this->stages, true)));
        file_put_contents($this->stagesFileM, sprintf(self::FILE_ACCORDANCE, var_export($this->stagesM, true)));
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
            $exists_season = FcSeason::model()->find(
                new CDbCriteria(['condition' => 'title=:title', 'params' => [':title' => $s->title]])
            );

            if ($exists_season) {
                $this->seasonsM[$s->id] = $exists_season->getMultilangId();
                $season = new FcSeason();
                $season->setAttributes($exists_season->getAttributes());
                $season->multilangId = $exists_season->getMultilangId();
                $season->lang = BaseFcModel::LANG_ES;
                if (!$season->save()) {
                    throw new CException(
                        'Season not created.' . "\n" .
                        var_export($season->getErrors(), true) . "\n" .
                        $s . "\n"
                    );
                }
                $this->seasons[$s->id][BaseFcModel::LANG_ES] = (int) $season->id;
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
            $season->setNew();
            $season->save();
            $this->seasons[$s->id][$season->lang] = (int) $season->id;
            $season->setNew(BaseFcModel::LANG_ES);
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

            $exists_champ = FcChampionship::model()->find(
                new CDbCriteria(
                    [
                        'condition' => 'title=:title AND fullTitle=:full_title',
                        'params' => [':title' => $champ->title, ':full_title' => $champ->fullTitle],
                        'order' => 'id'
                    ]
                )
            );

            if ($exists_champ) {
                $this->champsM[$t->id] = $exists_champ->getMultilangId();
                $champ_ru = $exists_champ->getId();
                $ch_es = new FcChampionship();
                $ch_es->setAttributes($exists_champ->getAttributes());
                $ch_es->multilangId = $exists_champ->getMultilangId();
                $ch_es->setNew(BaseFcModel::LANG_ES);
                $ch_es->save();
                $this->champs[$t->id][$ch_es->lang] = $champ_es = (int) $ch_es->id;
            } else {
                if (!$champ->save()) {
                    throw new CException(
                        'Championship not created.' . "\n" .
                        var_export($champ->getErrors(), true) . "\n" .
                        $t . "\n"
                    );
                }

                $this->champs[$t->id][$champ->lang] = $champ_ru = (int) $champ->id;
                $champ->setNew();
                $champ->save();
                $this->champs[$t->id][$champ->lang] = $champ_en = (int) $champ->id;
                $champ->setNew(BaseFcModel::LANG_ES);
                $champ->save();
                $this->champs[$t->id][$champ->lang] = $champ_es = (int) $champ->id;

                $this->doneChamps++;
                $this->progress();
            }

            /* @var Stages $s */
            foreach ($t->stages as $s) {
                $stage = new FcStage();
                //$stage->importId = $s->id;
                $stage->championship_id = $champ_ru;
                $stage->title = $s->short;
                $stage->fullTitle = $s->title ?: $t->title;
                $stage->style = $s->isCap() ? FcStage::STYLE_CUP : FcStage::STYLE_ROUND;
                $stage->reglament = $s->reglamentar;

                $exists_stage = FcStage::model()->find(
                    new CDbCriteria(
                        [
                            'condition' => 'championship_id=:champ_id AND fullTitle=:full_title',
                            'params' => [':champ_id' => $champ_ru, ':full_title' => $stage->fullTitle]
                        ]
                    )
                );

                if ($exists_stage) {
                    $this->stagesM[$s->id] = $exists_stage->getMultilangId();
                    $st = new FcStage();
                    $st->setAttributes($exists_stage->getAttributes());
                    $st->multilangId = $exists_stage->getMultilangId();
                    $st->setNew(BaseFcModel::LANG_ES);
                    $st->save();
                    $this->stages[$s->id][$st->lang] = (int) $st->id;
                    continue;
                }

                if (!$stage->save()) {
                    throw new CException(
                        'Stage not created.' . "\n" .
                        var_export($stage->getErrors(), true) . "\n" .
                        $s . "\n"
                    );
                }

                $this->stages[$s->id][$stage->lang] = (int) $stage->id;
                $stage->setNew();
                $stage->save();
                $this->stages[$s->id][$stage->lang] = (int) $stage->id;
                $stage->setNew(BaseFcModel::LANG_ES);
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

    public function getSeasonsM()
    {
        return file_exists($this->seasonsFileM) ? include $this->seasonsFileM : [];
    }

    public function getChampsM()
    {
        return file_exists($this->champsFileM) ? include $this->champsFileM : [];
    }

    public function getStagesM()
    {
        return file_exists($this->stagesFileM) ? include $this->stagesFileM : [];
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
