<?php

/**
 * Модель таблицы "{{fc__contract}}".
 *
 * Доступные поля таблицы "{{fc__contract}}":
 * @property integer $id Идентификатор.
 * @property string $position Должность.
 * @property string $fromtime Дата начала.
 * @property string $untiltime Дата окончания.
 * @property integer $number Номер (для игроков).
 * @property integer $team_id Команда.
 * @property integer $person_id Персона.
 *
 * Доступные отношения:
 * @property FcTeams $team
 * @property FcPerson $person
 *
 * @package    converter
 * @subpackage fccontract
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcContracts extends DestinationModel
{
    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'fc';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__contract}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['team_id, person_id', 'required'],
            ['number, team_id, person_id', 'numerical', 'integerOnly'=>true],
            ['position', 'length', 'max'=>128],
            ['fromtime, untiltime', 'safe'],
            ['id, position, fromtime, untiltime, number, team_id, person_id', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'team'   => [self::BELONGS_TO, 'FcTeams', 'team_id'],
            'person' => [self::BELONGS_TO, 'FcPerson', 'person_id']
        ];
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
