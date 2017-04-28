<?php

/**
 * Конвертер многоязычности ФК.
 * Присвоение англоязычным матчам тех же многоязычных идентификаторов, что в русскоязычных матчах.
 *
 * @package    converter
 * @subpackage move_fc
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2017
 */
class FcMultilangFixConverter implements IConverter
{
    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rMatches: %d";

    /**
     * @var integer
     */
    private $doneMatches = 0;

    /**
     * Запуск преобразований.
     *
     * @throws CDbException
     */
    public function convert()
    {
        $this->progress();
        $mcriteria = new CDbCriteria();
        $mcriteria->addCondition('lang_id=' . BaseFcModel::LANG_EN);
        $mcriteria->order = 'id';
        $criteria = new CDbCriteria([
            'condition' => 'championship_id=:champ AND season_id=:season AND stage_id=:stage AND tour=:tour AND ' .
                'home_team_id=:home AND guest_team_id=:guest AND matchtime=:time AND lang_id=' . BaseFcModel::LANG_RU
        ]);

        /** @var FcMatch $match */
        foreach (FcMatch::model()->findAll($mcriteria) as $match) {
            if (!$match->stage) {
                continue;
            }
            $criteria->params = [
                ':champ'  => $match->champ->getPairId(BaseFcModel::LANG_RU),
                ':season' => $match->season->getPairId(BaseFcModel::LANG_RU),
                ':stage'  => $match->stage->getPairId(BaseFcModel::LANG_RU),
                ':tour'   => $match->tour,
                ':home'   => $match->homeTeam->getPairId(BaseFcModel::LANG_RU),
                ':guest'  => $match->guestTeam->getPairId(BaseFcModel::LANG_RU),
                ':time'   => $match->matchtime
            ];
            /** @var FcMatch $match_ru */
            if ($match_ru = FcMatch::model()->find($criteria)) {
                if ((int) $match_ru->multilang_id !== (int) $match->multilang_id) {
                    $match->saveAttributes(['multilang_id' => $match_ru->multilang_id]);
                    $this->doneMatches++;
                    $this->progress();
                }
            }
        }
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneMatches);
    }
}
