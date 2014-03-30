<?php

/**
 * Модель таблицы "tsi.teams".
 *
 * Доступные поля таблицы "tsi.teams":
 * @property string $id Код команды.
 * @property string $title Заголовок.
 * @property string $info Информация о команде.
 * @property string $region Город.
 * @property string $country .
 * @property string $zenit .
 * @property string $key .
 * @property string $profile .
 * @property string $web .
 *
 * @package    converter
 * @subpackage teams
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Teams extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.teams';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title, region, country', 'length', 'max'=>255],
            ['key', 'length', 'max'=>20],
            ['web', 'length', 'max'=>512],
            ['info, zenit, profile', 'safe'],
            ['id, title, info, region, country, zenit, key, profile, web', 'safe', 'on'=>'search'],
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
            'id' => 'Код команды',
            'title' => 'Заголовок',
            'info' => 'Информация о команде',
            'region' => 'Город',
            'country' => '',
            'zenit' => '',
            'key' => '',
            'profile' => '',
            'web' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Teams Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
