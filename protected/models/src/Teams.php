<?php

/**
 * Модель таблицы "tsi.teams".
 *
 * Доступные поля таблицы "tsi.teams":
 * @property string $id Код команды.
 * @property string $title Заголовок.
 * @property string $info Информация о команде.
 * @property string $region Город.
 * @property string $country .
 * @property string $zenit .
 * @property string $key .
 * @property string $profile .
 * @property string $web .
 *
 * Доступные отношения:
 * @property Teamstats[] $stat
 *
 * @package    converter
 * @subpackage teams
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Teams extends SourceModel
{
    /**
     * Префикс ссылки на фотографии обычных новостей.
     *
     * @var string
     */
    const PHOTO_URL = 'http://fckrasnodar.ru/app/mods/tsi/res/';

    /**
     * Идентификатор основной команды.
     *
     * @var integer
     */
    const MAIN_TEAM = 537;

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.teams';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title, region, country', 'length', 'max'=>255],
            ['key', 'length', 'max'=>20],
            ['web', 'length', 'max'=>512],
            ['info, zenit, profile', 'safe'],
            ['id, title, info, region, country, zenit, key, profile, web', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'stat' => [self::HAS_MANY, 'Teamstats', 'team',
                'condition' =>
                    'season IN (' . implode(', ', array_map(
                        function ($season) {
                            /* @var Seasons $season */
                            return $season->id;
                        },
                        Seasons::model()->findAll(new CDbCriteria(['select' => 'id']))
                    )). ') AND ' .
                    'tournament IN (' . implode(', ', array_map(
                        function ($tour) {
                            /* @var Tournaments $season */
                            return $tour->id;
                        },
                        Tournaments::model()->findAll(new CDbCriteria(['select' => 'id']))
                    )) . ') AND '.
                    'stage IN (' . implode(', ', array_map(
                        function ($stage) {
                            /* @var Stages $season */
                            return $stage->id;
                        },
                        Stages::model()->findAll(new CDbCriteria(['select' => 'id']))
                    )) . ')'
            ]
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Код команды',
            'title' => 'Заголовок',
            'info' => 'Информация о команде',
            'region' => 'Город',
            'country' => '',
            'zenit' => '',
            'key' => '',
            'profile' => '',
            'web' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Teams Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
