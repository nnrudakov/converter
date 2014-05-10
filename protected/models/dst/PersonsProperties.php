<?php

/**
 * Модель таблицы "{{persons__properties}}".
 *
 * Доступные поля таблицы "{{persons__properties}}":
 *
 * @property string  $property_id Идентификатор свойства.
 * @property integer $set_id      Идентификатор набора свойств.
 * @property string  $name        Имя свойства.
 * @property string  $type        Тип свойства.
 * @property integer $is_require  Обязательное свойство.
 * @property string  $def_value   Значение свойства по умолчанию.
 * @property integer $sort        Порядок сортировки.
 *
 * Доступные отношения:
 * @property PersonsObjectData $data Данные.
 * @property PersonsSets       $set  Набор свойств.
 *
 * @package    converter
 * @subpackage personsproperties
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsProperties extends KitProperties
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__properties}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'data' => [self::HAS_ONE,    'PersonsObjectData', 'property_id'],
            'set'  => [self::BELONGS_TO, 'PersonsSets',       'set_id']
        ];
    }
}
