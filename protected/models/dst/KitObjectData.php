<?php

/**
 * Базовая модель данных объектов конструктора.
 *
 * Доступные поля таблиц:
 * @property string $data_id Идентификатор данных.
 * @property string $object_id Идентификатор объекта.
 * @property string $property_id Идентификатор свойства.
 *
 * @package    converter
 * @subpackage kitobjectdata
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class KitObjectData extends DestinationModel
{
    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['object_id, property_id', 'length', 'max'=>10],
            ['data_id, object_id, property_id', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'data_id' => 'Идентификатор данных',
            'object_id' => 'Идентификатор объекта',
            'property_id' => 'Идентификатор свойства',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return KitObjectData Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Получение идентификатора.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->data_id;
    }
}
