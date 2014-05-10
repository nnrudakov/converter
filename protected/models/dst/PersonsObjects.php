<?php

/**
 * Модель таблицы "fc__persons__objects".
 *
 * Доступные поля таблицы "fc__persons__objects":
 *
 * @property string  $object_id        Идентификатор объекта.
 * @property string  $main_category_id Идентификатор главной категории.
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
 * @property PersonsCategoryObjects[] $links Связка с категориями.
 * @property PersonsObjectSets        $set   Набор свойств.
 * @property PersonsObjectData[]      $data  Данные.
 *
 * @package    converter
 * @subpackage newsobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsObjects extends KitObjects
{
    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'persons';

    /**
     * Имя файла оригинала персоны.
     *
     * @var string
     */
    const FILE = 'images/person.orig.%d.jpg';

    /**
     * Имя поля связки файла.
     *
     * @param string
     */
    const FILE_FIELD = 'file';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__objects}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'links' => [self::HAS_ONE,  'PersonsCategoryObjects', 'object_id'],
            'set'   => [self::HAS_ONE,  'PersonsObjectSets',      'object_id'],
            'data'  => [self::HAS_MANY, 'PersonsObjectData',      'object_id']
        ];
    }
}
