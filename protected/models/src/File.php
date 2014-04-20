<?php

/**
 * Модель таблицы "public.file".
 *
 * Доступные поля таблицы "public.file":
 * @property integer $id .
 * @property integer $gallery .
 * @property string $type .
 * @property string $subject .
 * @property string $caption .
 * @property string $country .
 * @property string $region .
 * @property string $address .
 * @property string $taken .
 * @property string $loaded .
 * @property integer $duration .
 * @property boolean $is16x9 .
 * @property boolean $disabled .
 * @property string $location .
 * @property string $tags .
 * @property integer $ord .
 * @property integer $views .
 *
 * Доступные отношения:
 * @property Gallery $parent_gallery
 *
 * @package    converter
 * @subpackage file
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class File extends SourceMediaModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'public.file';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['duration', 'numerical', 'integerOnly'=>true],
            ['subject, caption, location', 'length', 'max'=>512],
            ['country, region', 'length', 'max'=>255],
            ['address', 'length', 'max'=>1024],
            ['gallery, type, taken, loaded, is16x9, disabled, tags, ord, views', 'safe'],
            ['id, gallery, type, subject, caption, country, region, address, taken, loaded, duration, is16x9, disabled, location, tags, ord, views', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'parent_gallery' => [self::BELONGS_TO, 'Galley', 'gallery']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'gallery' => '',
            'type' => '',
            'subject' => '',
            'caption' => '',
            'country' => '',
            'region' => '',
            'address' => '',
            'taken' => '',
            'loaded' => '',
            'duration' => '',
            'is16x9' => '',
            'disabled' => '',
            'location' => '',
            'tags' => '',
            'ord' => '',
            'views' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return File Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
