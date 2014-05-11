<?php

/**
 * Модель таблицы "fc__news__categories".
 *
 * Доступные поля таблицы "fc__news__categories":
 *
 * @property string  $category_id      Идентификатор категории.
 * @property string  $parent_id        Идентификатор родительской категории.
 * @property integer $lang_id          Идентификатор языка.
 * @property string  $name             Имя для URL.
 * @property string  $title            Заголовок категории.
 * @property string  $content          Описание категории.
 * @property integer $publish          Флаг публикации.
 * @property integer $share            Доступна подмодулям.
 * @property integer $sort             Порядок сортировки.
 * @property string  $meta_title       SEO заголовок.
 * @property string  $meta_description SEO описание.
 * @property string  $meta_keywords    SEO ключевые слова.
 *
 * Доступные отношения:
 * @property NewsCategoryObjects[] $links Связка с объектами.
 *
 * @package    converter
 * @subpackage newscategories
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class NewsCategories extends KitCategories
{
    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'news';

    // Cвои категории в "types" - "Типы категорий", id = 1.

    /**
     * Категория "чужих" новостей.
     * "Категории новостей" - news.
     *
     * @var integer
     */
    const CAT_NEWS_CAT = 2;

    /**
     * Категория новостей.
     * "Новости" - news.
     *
     * @var integer
     */
    const CAT_NEWS = 3;

    /**
     * Категория фоторепортажей.
     * "Фоторепортаж" - photo.
     *
     * @var integer
     */
    const CAT_PHOTO = 4;

    /**
     * Категория видеорепортажей.
     * "Видеорепортаж" - video.
     *
     * @var integer
     */
    const CAT_VIDEO = 5;

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{news__categories}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'links' => [self::HAS_MANY, 'NewsCategoryObjects', 'category_id']
        ];
    }
}
