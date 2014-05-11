<?php

/**
 * Модель таблицы "{{fc__teamstat}}".
 *
 * Доступные поля таблицы "{{fc__teamstat}}":
 * @property integer $id Идентификатор.
 * @property integer $season_id Идентификатор сезона.
 * @property integer $team_id Идентификатор команды.
 * @property integer $stage_id Идентификатор стадии чемпионата.
 * @property integer $stagegroup Группа в стадии.
 * @property integer $gamecount сыграно.
 * @property integer $wincount выигрыши.
 * @property integer $drawcount ничьи.
 * @property integer $losscount проигрыши.
 * @property integer $goalsconceded пропущенные голы.
 * @property integer $goals забитые голы.
 * @property integer $score очки.
 * @property integer $place место.
 *
 * @package    converter
 * @subpackage fcteamstat
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcTeamstat extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__teamstat}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['season_id, team_id, stage_id', 'required'],
            ['season_id, team_id, stage_id, stagegroup, gamecount, wincount, drawcount, losscount, goalsconceded, goals, score, place', 'numerical', 'integerOnly'=>true],
            ['id, season_id, team_id, stage_id, stagegroup, gamecount, wincount, drawcount, losscount, goalsconceded, goals, score, place', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'season_id' => 'Идентификатор сезона',
            'team_id' => 'Идентификатор команды',
            'stage_id' => 'Идентификатор стадии чемпионата',
            'stagegroup' => 'Группа в стадии',
            'gamecount' => 'сыграно',
            'wincount' => 'выигрыши',
            'drawcount' => 'ничьи',
            'losscount' => 'проигрыши',
            'goalsconceded' => 'пропущенные голы',
            'goals' => 'забитые голы',
            'score' => 'очки',
            'place' => 'место',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcTeamstat Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
