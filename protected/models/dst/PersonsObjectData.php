<?php

/**
 * Модель таблицы "{{persons__object_data}}".
 *
 * Доступные поля таблицы "{{persons__object_data}}":
 * @property string $data_id Идентификатор данных.
 * @property string $object_id Идентификатор объекта.
 * @property string $property_id Идентификатор свойства.
 *
 * @package    converter
 * @subpackage personsobjectdata
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsObjectData extends KitObjectData
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__object_data}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [];
    }
}
