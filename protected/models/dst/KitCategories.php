<?php

/**
 * Базовая модель категорий коструктора.
 *
 * Доступные поля таблиц:
 *
 * @property string  $category_id      Идентификатор категории.
 * @property string  $parent_id        Идентификатор родительской категории.
 * @property integer $multilang_id     Id.
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
 * @package    converter
 * @subpackage kitcategories
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class KitCategories extends DestinationModel
{
    /**
     * Сущность.
     *
     * @var string
     */
    const ENTITY = 'category';

    /**
     * @var integer
     */
    const MODULE_ID = 11;

    /**
     * Без привязки к категории.
     *
     * @var integer
     */
    const NO_CAT = 0;

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{kit__categories}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['multilang_id', 'lang_id, publish, share, sort', 'numerical', 'integerOnly'=>true],
            ['parent_id', 'length', 'max'=>10],
            ['name', 'length', 'max'=>40],
            ['title', 'length', 'max'=>50],
            ['meta_title, meta_description, meta_keywords', 'length', 'max'=>255],
            ['content', 'safe'],
            [
                'category_id, parent_id, lang_id, name, title, content, publish, share, sort, meta_title, '.
                'meta_description, meta_keywords', 'safe', 'on'=>'search'
            ],
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'category_id'      => 'Идентификатор категории',
            'parent_id'        => 'Идентификатор родительской категории',
            'lang_id'          => 'Идентификатор языка',
            'name'             => 'Имя для URL',
            'title'            => 'Заголовок категории',
            'content'          => 'Описание категории',
            'publish'          => 'Флаг публикации',
            'share'            => 'Доступна подмодулям',
            'sort'             => 'Порядок сортировки',
            'meta_title'       => 'SEO заголовок',
            'meta_description' => 'SEO описание',
            'meta_keywords'    => 'SEO ключевые слова',
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
        $criteria->compare('category_id', $this->category_id, true);
        $criteria->compare('parent_id', $this->parent_id, true);
        $criteria->compare('lang_id', $this->lang_id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('publish', $this->publish);
        $criteria->compare('share', $this->share);
        $criteria->compare('sort', $this->sort);
        $criteria->compare('meta_title', $this->meta_title, true);
        $criteria->compare('meta_description', $this->meta_description, true);
        $criteria->compare('meta_keywords', $this->meta_keywords, true);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return NewsCategories Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Получение идентификатора.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->category_id;
    }

    /**
     * Установка новой записи.
     *
     * @return bool
     */
    public function setNew()
    {
        $this->setIsNewRecord(true);
        $this->category_id = null;
        $this->lang = $this->lang_id = self::LANG_EN;
    }
}
