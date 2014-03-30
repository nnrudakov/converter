<?php

/**
 * Модель таблицы "{{fc__contracts}}".
 *
 * Доступные поля таблицы "{{fc__contracts}}":
 * @property integer $id Идентификатор.
 * @property string $position Должность.
 * @property string $fromtime Дата начала.
 * @property string $untiltime Дата окончания.
 * @property integer $number Номер (для игроков).
 * @property integer $team_id Команда.
 * @property integer $person_id Персона.
 *
 * @package    converter
 * @subpackage fccontracts
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcContracts extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__contracts}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['fromtime, untiltime, team_id, person_id', 'required'],
            ['number, team_id, person_id', 'numerical', 'integerOnly'=>true],
            ['position', 'length', 'max'=>128],
            ['id, position, fromtime, untiltime, number, team_id, person_id', 'safe', 'on'=>'search'],
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
            'position' => 'Должность',
            'fromtime' => 'Дата начала',
            'untiltime' => 'Дата окончания',
            'number' => 'Номер (для игроков)',
            'team_id' => 'Команда',
            'person_id' => 'Персона',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcContracts Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
