<?php

/**
 * Модель таблицы "{{files__link}}".
 *
 * Доступные поля таблицы "{{files__link}}":
 * @property integer $file_id Идентификатор файла.
 * @property integer $module_id Идентификатор модуля.
 * @property integer $category_id Идентификатор категории.
 * @property integer $object_id Идентификатор объекта.
 * @property string $field_id Идентификатор поля объекта.
 * @property string $title Заголовок связки файла с объектом.
 * @property string $descr Описание связки файла собъектом.
 * @property integer $main Основной файл в списке.
 * @property integer $sort Сортировка.
 *
 * Доступные отношения:
 * @property Files $file Файл.
 *
 * @package    converter
 * @subpackage fileslink
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FilesLink extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{files__link}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['main, sort', 'numerical', 'integerOnly'=>true],
            ['file_id, module_id, category_id, object_id', 'length', 'max'=>10],
            ['field_id', 'length', 'max'=>20],
            ['title', 'length', 'max'=>100],
            ['descr', 'length', 'max'=>500],
            ['file_id, module_id, category_id, object_id, field_id, title, descr, main, sort', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'file' => [self::BELONGS_TO, 'Files', 'file_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'Идентификатор файла',
            'module_id' => 'Идентификатор модуля',
            'category_id' => 'Идентификатор категории',
            'object_id' => 'Идентификатор объекта',
            'field_id' => 'Идентификатор поля объекта',
            'title' => 'Заголовок связки файла с объектом',
            'descr' => 'Описание связки файла собъектом',
            'main' => 'Основной файл в списке',
            'sort' => 'Сортировка',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FilesLink Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
