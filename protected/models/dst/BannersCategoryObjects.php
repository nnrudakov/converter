<?php

/**
 * Модель таблицы "fc__branches__category_objects".
 *
 * Доступные поля таблицы "fc__branches__category_objects":
 *
 * @property string  $category_id Идентификатор категории.
 * @property string  $object_id   Идентификатор объекта.
 * @property integer $sort        Порядок сортировки объекта в категории.
 *
 * Доступные отношения:
 * @property BannersCategories $category Категория.
 * @property BannersObjects    $object   Объект.
 *
 * @package    converter
 * @subpackage bannerscategoryobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class BannersCategoryObjects extends KitCategoryObjects
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{banners__category_objects}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'category' => [self::HAS_ONE, 'BannersCategories', 'category_id'],
            'object'   => [self::HAS_ONE, 'BannersObjects',    'object_id']
        ];
    }
}
