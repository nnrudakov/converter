<?php

/**
 * Модель таблицы "{{tags__sources}}".
 *
 * Доступные поля таблицы "{{tags__sources}}":
 * @property integer $link_id Идентификатор связи тега с модулем.
 * @property integer $object_id Идентификатор объекта.
 *
 * @package    converter
 * @subpackage tagssources
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class TagsSources extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{tags__sources}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['link_id, object_id', 'length', 'max'=>10],
            ['link_id, object_id', 'safe', 'on'=>'search'],
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
            'link_id' => 'Идентификатор связи тега с модулем',
            'object_id' => 'Идентификатор объекта',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return TagsSources Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
