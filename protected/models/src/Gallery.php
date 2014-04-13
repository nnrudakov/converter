<?php

/**
 * Модель таблицы "public.gallery".
 *
 * Доступные поля таблицы "public.gallery":
 * @property integer $id .
 * @property string $caption .
 * @property string $date .
 * @property string $author .
 * @property string $location .
 * @property boolean $disabled .
 * @property string $tags .
 * @property integer $ord .
 *
 * Доступные отношения:
 * @property File[] $files
 *
 * @package    converter
 * @subpackage gallery
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Gallery extends SourceMediaModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'public.gallery';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['caption, location', 'length', 'max'=>512],
            ['author', 'length', 'max'=>255],
            ['date, disabled, tags, ord', 'safe'],
            ['id, caption, date, author, location, disabled, tags, ord', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'files' => [self::HAS_MANY, 'File', 'gallery', 'order' => 'ord']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'caption' => '',
            'date' => '',
            'author' => '',
            'location' => '',
            'disabled' => '',
            'tags' => '',
            'ord' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Gallery Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
