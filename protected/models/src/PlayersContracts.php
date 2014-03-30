<?php

/**
 * Модель таблицы "tsi.contracts".
 *
 * Доступные поля таблицы "tsi.contracts":
 * @property string $id Код контракта с игроком.
 * @property string $team Код команды.
 * @property string $player Код игрока.
 * @property string $date_from С.
 * @property string $date_to По.
 * @property boolean $staff В основном составе?.
 * @property integer $number Номер игрока.
 * @property string $zenit .
 *
 * @package    converter
 * @subpackage playerscontracts
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PlayersContracts extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.contracts';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['number', 'numerical', 'integerOnly'=>true],
            ['team, player, date_from, date_to, staff, zenit', 'safe'],
            ['id, team, player, date_from, date_to, staff, number, zenit', 'safe', 'on'=>'search'],
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
            'id' => 'Код контракта с игроком',
            'team' => 'Код команды',
            'player' => 'Код игрока',
            'date_from' => 'С',
            'date_to' => 'По',
            'staff' => 'В основном составе?',
            'number' => 'Номер игрока',
            'zenit' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return PlayersContracts Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
