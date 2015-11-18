<?php

/**
 * Модель таблицы "fc__news__objects".
 *
 * Доступные поля таблицы "fc__news__objects":
 *
 * @property integer $object_id        Идентификатор объекта.
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
 * @property string  $type             Тип объекта.
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
 * @property NewsCategoryObjects[] $links Связка с категориями.
 *
 * @package    converter
 * @subpackage newsobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class NewsObjects extends KitObjects
{
    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'news';

    /**
     * Имя файла оригинала обычной новости.
     *
     * @var string
     */
    const FILE = 'news.orig.%d.jpg';

    /**
     * Имя поля связки файла.
     *
     * @param string
     */
    const FILE_FIELD = 'file';

    /**
     * Имя файла оригинала фоторепортажа.
     *
     * @var string
     */
    const FILE_PHOTO = 'image.orig.%d.jpg';

    /**
     * Имя файла оригинала видеорепортажа.
     *
     * @var string
     */
    const FILE_VIDEO = 'image.orig.%d.mp4';

    /**
     * Имя файла превью видеорепортажа.
     *
     * @var string
     */
    const FILE_VIDEO_THUMB = 'image.%d.611x360.jpg';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{news__objects}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'links' => [self::HAS_MANY, 'NewsCategoryObjects', 'object_id', 'joinType' => 'INNER JOIN']
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
