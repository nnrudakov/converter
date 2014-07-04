<?php

/**
 * Модель таблицы "{{tags}}".
 *
 * Доступные поля таблицы "{{tags}}":
 * @property integer $tag_id .
 * @property integer $category_id .
 * @property string $name .
 * @property string $title .
 * @property string $alias_name Псевдоним имени.
 * @property string $alias_title Псевдоним заголовка.
 * @property integer $publish .
 * @property integer $priority .
 *
 * @package    converter
 * @subpackage tags
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Tags extends DestinationModel
{
    /**
     * Имя сущности для многоязычности.
     *
     * @var string
     */
    const ENTITY = 'tag';

    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'tags';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{tags}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['publish, priority', 'numerical', 'integerOnly'=>true],
            ['category_id', 'length', 'max'=>10],
            ['name, alias_name', 'length', 'max'=>255],
            ['title, alias_title', 'length', 'max'=>255],
            ['tag_id, category_id, name, title, alias_name, alias_title, publish, priority', 'safe', 'on'=>'search'],
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
            'tag_id' => '',
            'category_id' => '',
            'name' => '',
            'title' => '',
            'alias_name' => 'Псевдоним имени',
            'alias_title' => 'Псевдоним заголовка',
            'publish' => '',
            'priority' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Tags Модель.
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
        return $this->tag_id;
    }

    /**
     * Установка новой записи.
     *
     * @return bool
     */
    public function setNew()
    {
        $this->setIsNewRecord(true);
        $this->tag_id = null;
        $this->lang = self::LANG_EN;
    }
}
