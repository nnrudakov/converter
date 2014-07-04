<?php

/**
 * Модель таблицы "{{tags__categories}}".
 *
 * Доступные поля таблицы "{{tags__categories}}":
 * @property integer $category_id .
 * @property string $name .
 * @property string $title .
 * @property string $descr .
 * @property integer $publish .
 * @property integer $module_id Идентификатор модуля.
 * @property string $entity Имя сущности.
 *
 * @package    converter
 * @subpackage tagscategories
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class TagsCategories extends DestinationModel
{
    /**
     * @var integer
     */
    const TEAMS = 3;

    /**
     * @var integer
     */
    const PLAYERS = 4;

    /**
     * @var integer
     */
    const MATCHES = 5;

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{tags__categories}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['publish', 'numerical', 'integerOnly'=>true],
            ['name, entity', 'length', 'max'=>20],
            ['title', 'length', 'max'=>50],
            ['descr', 'length', 'max'=>255],
            ['module_id', 'length', 'max'=>10],
            ['category_id, name, title, descr, publish, module_id, entity', 'safe', 'on'=>'search'],
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
            'category_id' => '',
            'name' => '',
            'title' => '',
            'descr' => '',
            'publish' => '',
            'module_id' => 'Идентификатор модуля',
            'entity' => 'Имя сущности',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return TagsCategories Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
