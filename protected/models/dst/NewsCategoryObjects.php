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
class NewsCategoryObjects extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{news__category_objects}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['sort', 'numerical', 'integerOnly'=>true],
            ['category_id, object_id', 'length', 'max'=>10],
            ['category_id, object_id, sort', 'safe', 'on'=>'search'],
        ];
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
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'category_id' => 'Идентификатор категории',
            'object_id' => 'Идентификатор объекта',
            'sort' => 'Порядок сортировки объекта в категории',
        ];
    }

    /**
     * Построение условий поиска.
     *
     * @return CActiveDataProvider Модели с применением фильтров.
     */
    public function search()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('category_id', $this->$name, true);
        $criteria->compare('object_id', $this->$name, true);
        $criteria->compare('sort', $this->$name);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return NewsCategoryObjects Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
