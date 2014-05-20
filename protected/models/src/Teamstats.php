<?php

/**
 * Модель таблицы "tsi.teamstats".
 *
 * Доступные поля таблицы "tsi.teamstats":
 * @property integer $id .
 * @property integer $season .
 * @property integer $tournament .
 * @property integer $stage .
 * @property integer $team .
 * @property integer $played Сыгранные матчи.
 * @property integer $won Выигрыши.
 * @property integer $drawn Ничьих.
 * @property integer $lost Проигрышей.
 * @property integer $goalsfor Пропущенные голы.
 * @property integer $goalsagainst Забитые голы.
 * @property integer $points .
 * @property integer $ord .
 *
 * Доступные отношения:
 * @property Seasons     $statSeason
 * @property Tournaments $statTour
 * @property Stages      $statStage
 * @property Teams       $statTeam
 *
 * @package    converter
 * @subpackage teamstats
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Teamstats extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.teamstats';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['season, tournament, stage, team, played, won, drawn, lost, goalsfor, goalsagainst, points, ord', 'safe'],
            ['id, season, tournament, stage, team, played, won, drawn, lost, goalsfor, goalsagainst, points, ord', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'statSeason' => [self::BELONGS_TO, 'Seasons', 'season', 'joinType' => 'INNER JOIN'],
            'statTour'   => [self::BELONGS_TO, 'Tournaments', 'tournament', 'joinType' => 'INNER JOIN'],
            'statStage'  => [self::BELONGS_TO, 'Stages', 'stage', 'joinType' => 'INNER JOIN'],
            'statTeam'   => [self::BELONGS_TO, 'Teams', 'team', 'joinType' => 'INNER JOIN']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'season' => '',
            'tournament' => '',
            'stage' => '',
            'team' => '',
            'played' => 'Сыгранные матчи',
            'won' => 'Выигрыши',
            'drawn' => 'Ничьих',
            'lost' => 'Проигрышей',
            'goalsfor' => 'Пропущенные голы',
            'goalsagainst' => 'Забитые голы',
            'points' => '',
            'ord' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Teamstats Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
