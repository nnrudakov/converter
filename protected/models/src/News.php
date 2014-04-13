<?php

/**
 * Модель таблицы "info_store.news".
 *
 * Доступные поля таблицы "info_store.news":
 *
 * @property integer $id              Код новости.
 * @property integer $date            Дата новости.
 * @property string  $title           Заголовок.
 * @property string  $description     Описание новости.
 * @property string  $message         Текст новости.
 * @property string  $type            Тип.
 * @property integer $ord             Порядок вывода.
 * @property string  $link            ссылка на сторонный обект.
 * @property string  $details         .
 * @property string  $author          Подпись под фото (автор и фотограф).
 * @property boolean $isfront         Показывать на главной.
 * @property string  $tags            .
 * @property string  $zenit           .
 * @property string  $metadescription description для meta.
 * @property string  $metatitle       .
 * @property string  $metakeywords    .
 * @property boolean $isrss           .
 * @property string  $priority        Приоритет новости в рамках дня.
 * @property string  $photo_author    .
 * @property boolean $isyandex        .
 * @property string  $flags           .
 *
 * Доступные отношения:
 * @property NewsLinks $links Связка с категорией.
 *
 * @package    Converter
 * @subpackage news
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class News extends SourceModel
{
    /**
     * Префикс ссылки на фотографии обычных новостей.
     *
     * @var string
     */
    const TEXT_URL = 'http://fckrasnodar.ru/app/mods/info_store/res/';

    /**
     * Префикс ссылки на фотографии фоторепортажей.
     *
     * @var string
     */
    const PHOTO_URL = 'http://media.fckrasnodar.ru/res/';

    /**
     * Префикс ссылки на фотографии видеорепортажей.
     *
     * @var string
     */
    const VIDEO_URL = 'http://media.fckrasnodar.ru/res/';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'info_store.news';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['date', 'length', 'max'=>6],
            ['title, description, link, metakeywords', 'length', 'max'=>1000],
            ['type', 'length', 'max'=>255],
            ['author, photo_author, flags', 'length', 'max'=>512],
            ['metadescription, metatitle', 'length', 'max'=>200],
            ['message, ord, details, isfront, tags, zenit, isrss, priority, isyandex', 'safe'],
            [
                'id, date, title, description, message, type, ord, link, details, author, isfront, tags, zenit, '.
                'metadescription, metatitle, metakeywords, isrss, priority, photo_author, isyandex, flags', 'safe',
                'on'=>'search'
            ],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'links' => [self::HAS_ONE, 'NewsLinks', 'news']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'Код новости',
            'date'            => 'Дата новости',
            'title'           => 'Заголовок',
            'description'     => 'Описание новости',
            'message'         => 'Текст новости',
            'type'            => 'Тип',
            'ord'             => 'Порядок вывода',
            'link'            => 'ссылка на сторонный обект',
            'details'         => '',
            'author'          => 'Подпись под фото (автор и фотограф)',
            'isfront'         => 'Показывать на главной',
            'tags'            => '',
            'zenit'           => '',
            'metadescription' => 'description для meta',
            'metatitle'       => '',
            'metakeywords'    => '',
            'isrss'           => '',
            'priority'        => 'Приоритет новости в рамках дня',
            'photo_author'    => '',
            'isyandex'        => '',
            'flags'           => '',
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
        $criteria->compare('id', $this->$name, true);
        $criteria->compare('date', $this->$name, true);
        $criteria->compare('title', $this->$name, true);
        $criteria->compare('description', $this->$name, true);
        $criteria->compare('message', $this->$name, true);
        $criteria->compare('type', $this->$name, true);
        $criteria->compare('ord', $this->$name, true);
        $criteria->compare('link', $this->$name, true);
        $criteria->compare('details', $this->$name, true);
        $criteria->compare('author', $this->$name, true);
        $criteria->compare('isfront', $this->$name);
        $criteria->compare('tags', $this->$name, true);
        $criteria->compare('zenit', $this->$name, true);
        $criteria->compare('metadescription', $this->$name, true);
        $criteria->compare('metatitle', $this->$name, true);
        $criteria->compare('metakeywords', $this->$name, true);
        $criteria->compare('isrss', $this->$name);
        $criteria->compare('priority', $this->$name, true);
        $criteria->compare('photo_author', $this->$name, true);
        $criteria->compare('isyandex', $this->$name);
        $criteria->compare('flags', $this->$name, true);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return News Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Текстовая новость.
     *
     * @return bool
     */
    public function isText()
    {
        return $this->type == 'text' || !$this->type;
    }

    /**
     * Фоторепортаж.
     *
     * @return bool
     */
    public function isPhoto()
    {
        return $this->type == 'photo';
    }

    /**
     * Видеорепортаж.
     *
     * @return bool
     */
    public function isVideo()
    {
        return $this->type == 'video' || $this->type == 'blog' || $this->type == 'link';
    }

    /**
     * Идентификатор галереии фоторепортажа или видеорепортажа.
     *
     * @return integer
     */
    public function getGalleyId()
    {
        if ($this->isText()) {
            return 0;
        }

        if (!preg_match('/(?<id>\d+)\.html$/', $this->link, $m)) {
             return 0;
        }

        return $m['id'];
    }
}
