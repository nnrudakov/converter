<?php

/**
 * Базовая модель наборов свойств.
 *
 * Доступные поля таблиц:
 * @property integer $object_id Идентификатор объекта.
 * @property integer $set_id Идентификатор набора свойств.
 *
 * @package    converter
 * @subpackage kitobjectsets
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class KitObjectSets extends DestinationModel
{
    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['set_id', 'numerical', 'integerOnly'=>true],
            ['object_id', 'length', 'max'=>10],
            ['object_id, set_id', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'object_id' => 'Идентификатор объекта',
            'set_id' => 'Идентификатор набора свойств',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return KitObjectSets Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Установка новой записи.
     *
     * @return bool
     */
    public function setNew()
    {
        $this->setIsNewRecord(true);
        $this->object_id = null;
    }
}
