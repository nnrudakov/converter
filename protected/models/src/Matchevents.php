<?php

/**
 * Модель таблицы "tsi.matchevents".
 *
 * Доступные поля таблицы "tsi.matchevents":
 * @property integer $id                .
 * @property integer $match             .
 * @property integer $team              .
 * @property integer $player            .
 * @property integer $firetime          .
 * @property integer $injurytime        .
 * @property string  $comment           .
 * @property boolean $timeout           .
 * @property boolean $goal              .
 * @property boolean $autogoal          .
 * @property boolean $goalfrompenalty   .
 * @property boolean $pin               .
 * @property boolean $yc                .
 * @property boolean $ycyc              .
 * @property boolean $rc                .
 * @property boolean $unrealizedpenalty .
 * @property boolean $pout              .
 * @property string  $zenit             .
 * @property boolean $pinout            .
 * @property integer $realtime          .
 * @property boolean $cornergoal        .
 * @property boolean $finegoal          .
 * @property boolean $help              .
 *
 * Доступные отношения:
 * @property Matches $eventMatch
 *
 * @package    converter
 * @subpackage matchevents
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Matchevents extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.matchevents';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['match, team, player, firetime, injurytime, comment, timeout, goal, autogoal, goalfrompenalty, pin, yc, ycyc, rc, unrealizedpenalty, pout, zenit, pinout, realtime, cornergoal, finegoal, help', 'safe'],
            ['id, match, team, player, firetime, injurytime, comment, timeout, goal, autogoal, goalfrompenalty, pin, yc, ycyc, rc, unrealizedpenalty, pout, zenit, pinout, realtime, cornergoal, finegoal, help', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'eventMatch' => [self::BELONGS_TO, 'Matches', 'match']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'match' => '',
            'team' => '',
            'player' => '',
            'firetime' => '',
            'injurytime' => '',
            'comment' => '',
            'timeout' => '',
            'goal' => '',
            'autogoal' => '',
            'goalfrompenalty' => '',
            'pin' => '',
            'yc' => '',
            'ycyc' => '',
            'rc' => '',
            'unrealizedpenalty' => '',
            'pout' => '',
            'zenit' => '',
            'pinout' => '',
            'realtime' => '',
            'cornergoal' => '',
            'finegoal' => '',
            'help' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Matchevents Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
