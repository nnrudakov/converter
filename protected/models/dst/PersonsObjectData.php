<?php

/**
 * Модель таблицы "{{persons__object_data}}".
 *
 * Доступные поля таблицы "{{persons__object_data}}":
 *
 * @property string $data_id     Идентификатор данных.
 * @property string $object_id   Идентификатор объекта.
 * @property string $property_id Идентификатор свойства.
 *
 * Доступные отношения:
 * @property PersonsObjects          $object
 * @property PersonsProperties       $property
 * @property PersonsObjectDataText[] $values
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
        return [
            'object'   => [self::HAS_ONE,  'PersonsObjects',        'object_id'],
            'property' => [self::HAS_ONE,  'PersonsProperties',     'property_id'],
            'values'   => [self::HAS_MANY, 'PersonsObjectDataText', 'data_id']
        ];
    }
}
