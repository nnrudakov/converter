<?php

/**
 * Модель таблицы "{{fc__championship}}".
 *
 * Доступные поля таблицы "{{fc__championship}}":
 * @property integer   $id               Идентификатор.
 * @property integer   $multilang_id     Id.
 * @property string    $title            Короткое название.
 * @property string    $fullTitle        Полное название.
 * @property string    $sponsor          Спонсор.
 * @property integer   $lang_id          Идентификатор языка.
 *
 * Доступные отношения:
 * @property FcStage[] $stages
 *
 * @package    converter
 * @subpackage fcchampionship
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcChampionship extends DestinationModel
{
    /**
     * Сущность.
     *
     * @var string
     */
    const ENTITY = 'championship';

    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'fc';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__championship}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['multilang_id, lang_id', 'numerical', 'integerOnly' => true],
            ['title', 'required'],
            ['title, sponsor', 'length', 'max' => 128],
            ['fullTitle', 'safe'],
            ['id, title, fullTitle, sponsor', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'stages' => [self::HAS_MANY, 'FcStage', 'championship_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'Идентификатор',
            'title'     => 'Короткое название',
            'fullTitle' => 'Полное название',
            'sponsor'   => 'Спонсор',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     *
     * @return FcChampionship Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
