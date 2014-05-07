<?php

/**
 * Модель таблицы "{{persons__properties}}".
 *
 * Доступные поля таблицы "{{persons__properties}}":
 * @property string $property_id Идентификатор свойства.
 * @property integer $set_id Идентификатор набора свойств.
 * @property string $name Имя свойства.
 * @property string $type Тип свойства.
 * @property integer $is_require Обязательное свойство.
 * @property string $def_value Значение свойства по умолчанию.
 * @property integer $sort Порядок сортировки.
 *
 * @package    converter
 * @subpackage personsproperties
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsProperties extends CActiveRecord
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__properties}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['set_id, is_require, sort', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>20],
            ['type', 'length', 'max'=>11],
            ['def_value', 'length', 'max'=>30],
            ['property_id, set_id, name, type, is_require, def_value, sort', 'safe', 'on'=>'search'],
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
            'property_id' => 'Идентификатор свойства',
            'set_id' => 'Идентификатор набора свойств',
            'name' => 'Имя свойства',
            'type' => 'Тип свойства',
            'is_require' => 'Обязательное свойство',
            'def_value' => 'Значение свойства по умолчанию',
            'sort' => 'Порядок сортировки',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return PersonsProperties Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
