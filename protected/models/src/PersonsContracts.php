<?php

/**
 * Модель таблицы "info_store.contracts".
 *
 * Доступные поля таблицы "info_store.contracts":
 * @property string $id .
 * @property string $person Персона.
 * @property string $team Команда.
 * @property string $position Должность.
 * @property string $datefrom Дата начала контракта.
 * @property string $dateto Дата окончания контракта.
 *
 * Доступные отношения:
 * @property Teams $personTeam
 * @property Persons $personPerson
 *
 * @package    converter
 * @subpackage personscontracts
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsContracts extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'info_store.contracts';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['person, team, position, datefrom, dateto', 'required'],
            ['position', 'length', 'max'=>255],
            ['id, person, team, position, datefrom, dateto', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'personTeam'   => [self::BELONGS_TO, 'Teams', 'team', 'joinType'=>'INNER JOIN'],
            'personPerson' => [self::BELONGS_TO, 'Persons', 'person', 'joinType'=>'INNER JOIN']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'person' => 'Персона',
            'team' => 'Команда',
            'position' => 'Должность',
            'datefrom' => 'Дата начала контракта',
            'dateto' => 'Дата окончания контракта',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return PersonsContracts Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
