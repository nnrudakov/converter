<?php

/**
 * Модель таблицы "tsi.schedule".
 *
 * Доступные поля таблицы "tsi.schedule":
 * @property integer $id         .
 * @property integer $season     Сезон проведения матча.
 * @property integer $tournament Турнир проведения матча.
 * @property integer $stage      Этап турнира.
 * @property string  $circle     Тур.
 * @property integer $team1      Команда хозяев.
 * @property integer $team2      Команда гостей.
 * @property string  $date       Дата проведения.
 * @property string  $region     Регион, где будет проводится матч.
 * @property string  $stadium    Старион.
 * @property string  $country    Страна.
 * @property string  $zenit      .
 * @property integer $match_id   .
 *
 * Доступные отношения:
 * @property Matches     $match
 * @property Tournaments $champ
 * @property Seasons     $s
 * @property Stages      $st
 * @property Teams       $homeTeam
 * @property Teams       $guestTeam
 *
 * @package    converter
 * @subpackage schedule
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Schedule extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.schedule';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['circle', 'length', 'max'=>20],
            ['region, stadium, country', 'length', 'max'=>255],
            ['season, tournament, stage, team1, team2, date, zenit, match_id', 'safe'],
            ['id, season, tournament, stage, circle, team1, team2, date, region, stadium, country, zenit, match_id', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'match' => [self::HAS_ONE, 'Matches', 'schedule', 'joinType' => 'INNER JOIN'],
            'champ' => [self::BELONGS_TO, 'Tournaments', 'tournament'],
            's'     => [self::BELONGS_TO, 'Seasons', 'season'],
            'st'    => [self::BELONGS_TO, 'Stages', 'stage'],
            'homeTeam'  => [self::BELONGS_TO, 'Teams', 'team1'],
            'guestTeam' => [self::BELONGS_TO, 'Teams', 'team2']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'season' => 'Сезон проведения матча',
            'tournament' => 'Турнир проведения матча',
            'stage' => 'Этап турнира',
            'circle' => 'Тур',
            'team1' => 'Команда хозяев',
            'team2' => 'Команда гостей',
            'date' => 'Дата проведения',
            'region' => 'Регион, где будет проводится матч',
            'stadium' => 'Старион',
            'country' => 'Страна',
            'zenit' => '',
            'match_id' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Schedule Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
