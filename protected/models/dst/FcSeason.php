<?php

/**
 * Модель таблицы "{{fc__season}}".
 *
 * Доступные поля таблицы "{{fc__season}}":
 * @property integer $id Идентификатор.
 * @property string $title Название.
 * @property string $description Описание.
 * @property string $fromtime Дата начала.
 * @property string $untiltime Дата окончания.
 *
 * @package    converter
 * @subpackage fcseason
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcSeason extends DestinationModel
{
    /**
     * Имя сущности для многоязычности.
     *
     * @var string
     */
    const ENTITY = 'season';

    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'fc';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__season}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title, fromtime, untiltime', 'required'],
            ['title', 'length', 'max'=>128],
            ['description', 'safe'],
            ['id, title, description, fromtime, untiltime', 'safe', 'on'=>'search'],
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
            'description' => 'Описание',
            'fromtime' => 'Дата начала',
            'untiltime' => 'Дата окончания',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcSeason Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
