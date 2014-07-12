<?php

/**
 * Модель таблицы "fc__news__category_objects".
 *
 * Доступные поля таблицы "fc__news__category_objects":
 *
 * @property string  $category_id Идентификатор категории.
 * @property string  $object_id   Идентификатор объекта.
 * @property integer $sort        Порядок сортировки объекта в категории.
 *
 * Доступные отношения:
 * @property NewsCategories $category Категория.
 * @property NewsObjects    $object   Объект.
 *
 * @package    converter
 * @subpackage newscategoryobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class NewsCategoryObjects extends KitCategoryObjects
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{news__category_objects}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'category' => [self::HAS_ONE, 'NewsCategories', 'category_id'],
            'object'   => [self::HAS_ONE, 'NewsObjects',    'object_id']
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
