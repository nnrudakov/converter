<?php

/**
 * Модель таблицы "tsi.playerstats".
 *
 * Доступные поля таблицы "tsi.playerstats":
 *
*@property integer $id .
 * @property integer $season Сезон.
 * @property integer $tournament Турнир.
 * @property integer $team Команда.
 * @property integer $player .
 * @property integer $played количество проведенных игр.
 * @property integer $begined выходил в стартовом составе.
 * @property integer $wentin выходил на замену.
 * @property integer $wentout был заменен.
 * @property integer $goals голы.
 * @property integer $helps голевые передачи.
 * @property integer $warnings предупрежден.
 * @property integer $removed удален.
 * @property integer $timeplayed время сыгранное.
 *
 * Доступные отношения:
 * @property Seasons     $statSeason
 * @property Tournaments $statTour
 * @property Teams       $statTeam
 * @property Players     $statPlayer
 *
 * @package    converter
 * @subpackage playerstats
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Playerstats extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.playerstats';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['season, tournament, team, player, played, begined, wentin, wentout, goals, helps, warnings, removed, timeplayed', 'safe'],
            ['id, season, tournament, team, player, played, begined, wentin, wentout, goals, helps, warnings, removed, timeplayed', 'safe', 'on'=>'search'],
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
            'statTeam'   => [self::BELONGS_TO, 'Teams', 'team', 'joinType' => 'INNER JOIN'],
            'statPlayer' => [self::BELONGS_TO, 'Players', 'player', 'joinType' => 'INNER JOIN']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id'         => '',
            'season'     => 'Сезон',
            'tournament' => 'Турнир',
            'team'       => 'Команда',
            'player'     => '',
            'played'     => 'количество проведенных игр',
            'begined'    => 'выходил в стартовом составе',
            'wentin'     => 'выходил на замену',
            'wentout'    => 'был заменен',
            'goals'      => 'голы',
            'helps'      => 'голевые передачи',
            'warnings'   => 'предупрежден',
            'removed'    => 'удален',
            'timeplayed' => 'время сыгранное',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Playerstats Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
