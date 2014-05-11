<?php

/**
 * Модель таблицы "tsi.matches".
 *
 * Доступные поля таблицы "tsi.matches":
 * @property integer $id           .
 * @property integer $schedule     .
 * @property integer $team1        .
 * @property integer $team2        .
 * @property string  $date         .
 * @property string  $time         .
 * @property integer $audience     .
 * @property string  $mainreferee  .
 * @property string  $linereferee1 .
 * @property string  $linereferee2 .
 * @property string  $sparereferee .
 * @property string  $delegate     .
 * @property string  $inspector    .
 * @property string  $htmllog      .
 * @property string  $translation  .
 * @property string  $xmllog       .
 * @property string  $summary      .
 * @property string  $zenit        .
 * @property string  $tickets      .
 * @property string  $weather      .
 * @property integer $match_id     .
 * @property integer $state        .
 * @property string  $profile      .
 *
 * Доступные отношения:
 * @property Schedule       $sch
 * @property Matchevents[]  $events
 * @property Matchplayers[] $players
 *
 * @package    converter
 * @subpackage matches
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Matches extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.matches';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['state', 'numerical', 'integerOnly'=>true],
            ['mainreferee, linereferee1, linereferee2, sparereferee, delegate, inspector', 'length', 'max'=>255],
            ['weather', 'length', 'max'=>512],
            ['schedule, team1, team2, date, time, audience, htmllog, translation, xmllog, summary, zenit, tickets, match_id, profile', 'safe'],
            ['id, schedule, team1, team2, date, time, audience, mainreferee, linereferee1, linereferee2, sparereferee, delegate, inspector, htmllog, translation, xmllog, summary, zenit, tickets, weather, match_id, state, profile', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'sch'     => [self::HAS_ONE,  'Schedule', 'schedule'],
            'events'  => [self::HAS_MANY, 'Matchevents', 'match'],
            'players' => [self::HAS_MANY, 'Matchplayers', 'match']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'schedule' => '',
            'team1' => '',
            'team2' => '',
            'date' => '',
            'time' => '',
            'audience' => '',
            'mainreferee' => '',
            'linereferee1' => '',
            'linereferee2' => '',
            'sparereferee' => '',
            'delegate' => '',
            'inspector' => '',
            'htmllog' => '',
            'translation' => '',
            'xmllog' => '',
            'summary' => '',
            'zenit' => '',
            'tickets' => '',
            'weather' => '',
            'match_id' => '',
            'state' => '',
            'profile' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Matches Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
