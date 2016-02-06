<?php

/**
 * Модель таблицы "fc__branches__objects".
 *
 * Доступные поля таблицы "fc__branches__objects":
 *
 * @property string  $object_id        Идентификатор объекта.
 * @property string  $main_category_id Идентификатор главной категории.
 * @property integer $multilang_id     Id.
 * @property string  $name             Имя объекта для URL.
 * @property integer $lang_id          Идентификатор языка.
 * @property string  $title            Заголовок объекта.
 * @property string  $announce         Анонс.
 * @property string  $content          Описание (содержание).
 * @property integer $important        Важный объект.
 * @property integer $publish          Флаг публикации.
 * @property string  $publish_date_on  Дата публикации.
 * @property string  $publish_date_off Дата снятия с публикации.
 * @property string  $source           Источник.
 * @property string  $source_link      Ссылка на источник.
 * @property string  $created          Дата создания объекта.
 * @property string  $meta_title       SEO заголовок.
 * @property string  $meta_description SEO описание.
 * @property string  $meta_keywords    SEO ключевые слова.
 * @property integer $minorCategoryId  Идентификатор не основной категории.
 * @property integer $sort             Порядок в категории.
 *
 * Доступные отношения:
 * @property BranchesCategoryObjects $catLink Связка с категориями.
 * @property FilesLink               $fileLink
 *
 * @package    converter
 * @subpackage branchesobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class BranchesObjects extends KitObjects
{
    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'branches';

    /**
     * @var integer
     */
    const MODULE_ID = 29;

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{branches__objects}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'catLink'  => [self::BELONGS_TO, 'BranchesCategoryObjects', 'object_id'],
            'fileLink' => [self::BELONGS_TO, 'FilesLink',               'object_id',
                'condition' => 'module_id=:module_id',
                'params'    => [':module_id' => self::MODULE_ID]
            ]
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return NewsObjects Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
