<?php

/**
 * Модель таблицы "{{persons__sets}}".
 *
 * Доступные поля таблицы "{{persons__sets}}":
 *
 * @property integer $set_id Идентификатор набора свойств.
 * @property string  $title  Заголовок набора свойств.
 *
 * Доступные отношения:
 * @property PersonsObjectSets   $object     Объект.
 * @property PersonsProperties[] $properties Свойства.
 *
 * @package    converter
 * @subpackage kitsets
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsSets extends KitSets
{
    /**
     * Необходимый набор свойств.
     *
     * @var integer
     */
    const SET = 1;

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__sets}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'object'     => [self::HAS_ONE,  'PersonsObjectSets', 'object_id'],
            'properties' => [self::HAS_MANY, 'PersonsProperties', 'set_id']
        ];
    }
}
