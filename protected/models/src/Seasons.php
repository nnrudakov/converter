<?php

/**
 * Модель таблицы "tsi.seasons".
 *
 * Доступные поля таблицы "tsi.seasons":
 * @property integer $id .
 * @property string $title Название сезона.
 * @property string $year Год проведения.
 * @property string $dts .
 * @property string $dte .
 * @property string $description .
 * @property string $zenit .
 *
 * @package    converter
 * @subpackage seasons
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Seasons extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.seasons';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title', 'length', 'max'=>255],
            ['year', 'length', 'max'=>20],
            ['dts, dte, description, zenit', 'safe'],
            ['id, title, year, dts, dte, description, zenit', 'safe', 'on'=>'search'],
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
            'id' => '',
            'title' => 'Название сезона',
            'year' => 'Год проведения',
            'dts' => '',
            'dte' => '',
            'description' => '',
            'zenit' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Seasons Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
