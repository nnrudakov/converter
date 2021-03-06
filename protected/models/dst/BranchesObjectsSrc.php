<?php

/**
 * Базовая модель объектов конструктора.
 *
 * Доступные поля таблиц:
 *
 * @property string  $object_id        Идентификатор объекта.
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
 * @property BranchesCategoryObjectsSrc[] $catLink
 * @property FilesLinkBranches          $fileLink
 *
 * @package    converter
 * @subpackage branchesobjects
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class BranchesObjectsSrc extends DestinationBranchModel
{
    /**
     * Сущность.
     *
     * @var string
     */
    const ENTITY = 'object';

    /**
     * @var integer
     */
    const MODULE_ID = 29;

    /**
     * Идентификатор не основной категории.
     *
     * @var integer
     */
    public $minorCategoryId = 0;

    /**
     * Порядок в категории.
     *
     * @var integer
     */
    public $sort = 0;

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{branches__objects}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['created', 'required'],
            ['lang_id, important, publish', 'numerical', 'integerOnly'=>true],
            ['main_category_id', 'length', 'max'=>10],
            ['name', 'length', 'max'=>50],
            ['title, source_link, meta_title, meta_description, meta_keywords', 'length', 'max'=>255],
            ['source', 'length', 'max'=>100],
            ['announce, content, publish_date_on, publish_date_off', 'safe'],
            [
                'object_id, main_category_id, name, lang_id, title, announce, content, important, publish, ' .
                'publish_date_on, publish_date_off, source, source_link, created, meta_title, meta_description, ' .
                'meta_keywords', 'safe', 'on'=>'search'
            ],
        ];
    }

    /**
     * @return array
     */
    public function relations()
    {
        return [
            'catLink'  => [self::HAS_MANY, 'BranchesCategoryObjectsSrc', 'object_id'],
            'fileLink' => [self::BELONGS_TO, 'FilesLinkBranches',          'object_id',
                'condition' => 'module_id=:module_id',
                'params'    => [':module_id' => self::MODULE_ID]
            ]
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'object_id' => 'Идентификатор объекта',
            'main_category_id' => 'Идентификатор главной категории',
            'name' => 'Имя объекта для URL',
            'lang_id' => 'Идентификатор языка',
            'title' => 'Заголовок объекта',
            'announce' => 'Анонс',
            'content' => 'Описание (содержание)',
            'important' => 'Важный объект',
            'publish' => 'Флаг публикации',
            'publish_date_on' => 'Дата публикации',
            'publish_date_off' => 'Дата снятия с публикации',
            'source' => 'Источник',
            'source_link' => 'Ссылка на источник',
            'created' => 'Дата создания объекта',
            'meta_title' => 'SEO заголовок',
            'meta_description' => 'SEO описание',
            'meta_keywords' => 'SEO ключевые слова',
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
        $criteria->compare('object_id', $this->$name, true);
        $criteria->compare('main_category_id', $this->$name, true);
        $criteria->compare('name', $this->$name, true);
        $criteria->compare('lang_id', $this->$name);
        $criteria->compare('title', $this->$name, true);
        $criteria->compare('announce', $this->$name, true);
        $criteria->compare('content', $this->$name, true);
        $criteria->compare('important', $this->$name);
        $criteria->compare('publish', $this->$name);
        $criteria->compare('publish_date_on', $this->$name, true);
        $criteria->compare('publish_date_off', $this->$name, true);
        $criteria->compare('source', $this->$name, true);
        $criteria->compare('source_link', $this->$name, true);
        $criteria->compare('created', $this->$name, true);
        $criteria->compare('meta_title', $this->$name, true);
        $criteria->compare('meta_description', $this->$name, true);
        $criteria->compare('meta_keywords', $this->$name, true);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
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

    /**
     * Получение идентификатора.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->object_id;
    }
}
