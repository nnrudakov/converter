<?php

/**
 * Модель таблицы "tsi.tournaments".
 *
 * Доступные поля таблицы "tsi.tournaments":
 * @property integer $id .
 * @property string $title Наименование турнира.
 * @property string $short .
 * @property string $sponsor Спонсор турнира.
 * @property boolean $is_dubl .
 *
 * Доступные отношения:
 * @property Stages[] $stages
 *
 * @package    converter
 * @subpackage tournaments
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Tournaments extends SourceModel
{
    /**
     * Список чемпионатов молодежных составов.
     *
     * @var array
     */
    public static $junior = [6, 8, 50];

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.tournaments';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title, sponsor', 'length', 'max'=>255],
            ['short', 'length', 'max'=>100],
            ['is_dubl', 'safe'],
            ['id, title, short, sponsor, is_dubl', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'stages' => [self::HAS_MANY, 'Stages', 'tournament',
                'select' => ['id', 'title', 'short', 'style', 'reglamentar'],
                'order'  => 'ord'
            ]
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'title' => 'Наименование турнира',
            'short' => '',
            'sponsor' => 'Спонсор турнира',
            'is_dubl' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Tournaments Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Чемпионат молодежных составов.
     *
     * @param integer $champId
     *
     * @return bool
     */
    public static function isJunior($champId)
    {
        return in_array($champId, self::$junior);
    }
}
