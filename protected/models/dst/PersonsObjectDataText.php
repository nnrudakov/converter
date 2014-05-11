<?php

/**
 * Модель таблицы "{{persons__object_data_text}}".
 *
 * Доступные поля таблицы "{{persons__object_data_text}}":
 * @property string $data_id Идентификатор данных.
 * @property string $data    Данные.
 *
 * Доступные отношения:
 * @property PersonsObjectData $object_data
 *
 * @package    converter
 * @subpackage personsobjectdatatext
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsObjectDataText extends KitObjectDataText
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__object_data_text}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return ['object_data' => [self::BELONGS_TO, 'PersonsObjectData', 'data_id']];
    }
}
