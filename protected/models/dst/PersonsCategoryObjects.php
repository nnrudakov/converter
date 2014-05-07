<?php

/**
 * Модель таблицы "fc__persons__category_objects".
 *
 * Доступные поля таблицы "fc__persons__category_objects":
 *
 * @property string  $category_id Идентификатор категории.
 * @property string  $object_id   Идентификатор объекта.
 * @property integer $sort        Порядок сортировки объекта в категории.
 *
 * Доступные отношения:
 * @property PersonsCategories $category Категория.
 * @property PersonsObjects    $object   Объект.
 *
 * @package    converter
 * @subpackage newscategoryobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsCategoryObjects extends KitCategoryObjects
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__category_objects}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'category' => [self::HAS_ONE, 'PersonsCategories', 'category_id'],
            'object'   => [self::HAS_ONE, 'PersonsObjects',    'object_id']
        ];
    }
}
