<?php

/**
 * Модель таблицы "{{fc__teams}}".
 *
 * Доступные поля таблицы "{{fc__teams}}":
 * @property integer $id Идентификатор.
 * @property string $title Название.
 * @property string $info Информация о команде.
 * @property string $city Город.
 * @property string $staff Состав.
 *
 * @package    converter
 * @subpackage fcteams
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcTeams extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__teams}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title', 'required'],
            ['title, city', 'length', 'max'=>128],
            ['staff', 'length', 'max'=>20],
            ['info', 'safe'],
            ['id, title, info, city, staff', 'safe', 'on'=>'search'],
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
            'id' => 'Идентификатор',
            'title' => 'Название',
            'info' => 'Информация о команде',
            'city' => 'Город',
            'staff' => 'Состав',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcTeams Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
