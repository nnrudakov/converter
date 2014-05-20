<?php

/**
 * Модель таблицы "{{persons__object_sets}}".
 *
 * Доступные поля таблицы "{{persons__object_sets}}":
 * @property string  $object_id Идентификатор объекта.
 * @property integer $set_id    Идентификатор набора свойств.
 *
 * Доступные отношения:
 * @property PersonsObjects $object Объект.
 *
 * @package    converter
 * @subpackage personsobjectsets
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsObjectSets extends KitObjectSets
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__object_sets}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return ['object' => [self::HAS_ONE, 'PersonsObjects', 'object_id']];
    }
}
