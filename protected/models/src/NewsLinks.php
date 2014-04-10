<?php

/**
 * Модель таблицы "info_store.news_links".
 *
 * Доступные поля таблицы "info_store.news_links":
 * @property integer $news Код новости.
 * @property integer $category Код категории.
 *
 * Доступные отношения:
 * @property News[]       $news_obj Объект.
 * @property NewsCategs[] $cat_obj  Категория.
 *
 * @package    converter
 * @subpackage newslinks
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class NewsLinks extends SourceModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'info_store.news_links';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['news, category', 'required'],
            ['news, category', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'news_obj' => [self::HAS_MANY, 'News',       'id',
                'select' => [
                    'id', 'date', 'title', 'message', 'type', 'link', 'details', 'metadescription', 'metatitle',
                    'metakeywords', 'priority'
                ],
                'condition' => 'news_obj.title!=\'\''
            ],
            'cat_obj'  => [self::HAS_MANY, 'NewsCategs', 'id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'news' => 'Код новости',
            'category' => 'Код категории',
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
        $criteria->compare('news', $this->$name, true);
        $criteria->compare('category', $this->$name, true);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return NewsLinks Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
