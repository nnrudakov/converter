<?php

/**
 * Модель таблицы "{{tags__modules}}".
 *
 * Доступные поля таблицы "{{tags__modules}}":
 * @property integer $link_id .
 * @property integer $tag_id .
 * @property integer $module_id .
 * @property integer $publish .
 * @property integer $is_default .
 *
 * @package    converter
 * @subpackage tagsmodules
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class TagsModules extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{tags__modules}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['publish, is_default', 'numerical', 'integerOnly'=>true],
            ['tag_id, module_id', 'length', 'max'=>10],
            ['link_id, tag_id, module_id, publish, is_default', 'safe', 'on'=>'search'],
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
            'tag_id' => '',
            'module_id' => '',
            'publish' => '',
            'is_default' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return TagsModules Модель.
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
        return $this->link_id;
    }

    /**
     * Установка новой записи.
     *
     * @return bool
     */
    public function setNew()
    {
        $this->setIsNewRecord(true);
        $this->link_id = null;
    }
}
