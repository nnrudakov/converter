<?php

/**
 * Модель таблицы "fc__branches__categories".
 *
 * Доступные поля таблицы "fc__branches__categories":
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
 * @property BranchesCategoryObjects[] $links Связка с объектами.
 *
 * @package    converter
 * @subpackage bannerscategories
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class BannersCategories extends KitCategories
{
    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'banners';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{banners__categories}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'links' => [self::HAS_MANY, 'BannersCategoryObjects', 'category_id']
        ];
    }
}
