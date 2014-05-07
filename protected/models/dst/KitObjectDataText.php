<?php

/**
 * Базовая модель значений данных объектов конструктора.
 *
 * Доступные поля таблиц:
 * @property string $data_id Идентификатор данных.
 * @property string $data Данные.
 *
 * @package    converter
 * @subpackage kitobjectdatatext
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class KitObjectDataText extends DestinationModel
{
    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['data_id', 'length', 'max'=>10],
            ['data', 'safe'],
            ['data_id, data', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'data_id' => 'Идентификатор данных',
            'data' => 'Данные',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return KitObjectDataText Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
