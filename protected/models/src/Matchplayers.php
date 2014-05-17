<?php

/**
 * Модель таблицы "tsi.matchplayers".
 *
 * Доступные поля таблицы "tsi.matchplayers":
 * @property integer $id         .
 * @property integer $match      .
 * @property integer $team       .
 * @property integer $player     .
 * @property integer $number     .
 * @property integer $position   .
 * @property integer $captain    .
 * @property integer $staff      .
 * @property string  $zenit      .
 * @property integer $schemaleft .
 * @property integer $schematop  .
 * @property boolean $schemaused .
 *
 * Доступные отношения:
 * @property Matches $playerMatch
 *
 * @package    converter
 * @subpackage matchplayers
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Matchplayers extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.matchplayers';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['match, team, player, number, position, captain, staff, zenit, schemaleft, schematop, schemaused', 'safe'],
            ['id, match, team, player, number, position, captain, staff, zenit, schemaleft, schematop, schemaused', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'playerMatch' => [self::BELONGS_TO, 'Matches', 'match']
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
            'number' => '',
            'position' => '',
            'captain' => '',
            'staff' => '',
            'zenit' => '',
            'schemaleft' => '',
            'schematop' => '',
            'schemaused' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Matchplayers Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Основной состав.
     *
     * @return bool
     */
    public function isMain()
    {
        return (bool) !$this->staff;
    }
}
