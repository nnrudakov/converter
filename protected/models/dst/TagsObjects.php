<?php

/**
 * Модель таблицы "{{tags__objects}}".
 *
 * Доступные поля таблицы "{{tags__objects}}":
 * @property integer $link_id .
 * @property integer $object_id .
 * @property integer $publish .
 *
 * @package    converter
 * @subpackage tagsobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class TagsObjects extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{tags__objects}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['publish', 'numerical', 'integerOnly'=>true],
            ['link_id, object_id', 'length', 'max'=>10],
            ['link_id, object_id, publish', 'safe', 'on'=>'search'],
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
            'link_id' => '',
            'object_id' => '',
            'publish' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return TagsObjects Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
