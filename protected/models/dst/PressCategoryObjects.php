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
 * @property PressCategories $category Категория.
 * @property PressObjects    $object   Объект.
 *
 * @package    converter
 * @subpackage presscategoryobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PressCategoryObjects extends KitCategoryObjects
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{press__category_objects}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'category' => [self::HAS_ONE, 'PressCategories', 'category_id'],
            'object'   => [self::HAS_ONE, 'PressObjects',    'object_id']
        ];
    }
}
