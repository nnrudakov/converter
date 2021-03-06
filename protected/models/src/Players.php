<?php

/**
 * Модель таблицы "tsi.players".
 *
 * Доступные поля таблицы "tsi.players":
 * @property string $id Код игрока.
 * @property string $amplua Код амплуа (вратарь, нападающий, и т.д.).
 * @property string $citizenship Гражданство.
 * @property string $resident Cтрана, где игрок является резидентом (проживает).
 * @property string $bio Биография.
 * @property string $surname Фамилия.
 * @property string $first_name Имя.
 * @property string $patronymic Отчество.
 * @property string $nickname Ник.
 * @property string $other_langs ФИО на других языках.
 * @property string $borned Дата д.р..
 * @property integer $height Рост.
 * @property integer $weight Вес.
 * @property string $achivements .
 * @property string $profile .
 * @property string $zenit .
 *
 * Доступные отношения:
 * @property Playerstats[] $stat
 *
 * @package    converter
 * @subpackage players
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Players extends SourceModel
{
    /**
     * Префикс ссылки на фотографии обычных новостей.
     *
     * @var string
     */
    const PHOTO_URL = 'http://fckrasnodar.ru/app/mods/tsi/res/';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.players';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['height, weight', 'numerical', 'integerOnly'=>true],
            ['citizenship, resident', 'length', 'max'=>255],
            ['surname, first_name, patronymic', 'length', 'max'=>150],
            ['nickname', 'length', 'max'=>50],
            ['other_langs', 'length', 'max'=>100],
            ['borned', 'length', 'max'=>6],
            ['amplua, bio, achivements, profile, zenit', 'safe'],
            ['id, amplua, citizenship, resident, bio, surname, first_name, patronymic, nickname, other_langs, borned, height, weight, achivements, profile, zenit', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'stat' => [self::HAS_MANY, 'Playerstats', 'player',
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
                    'team IN (' . implode(', ', array_map(
                        function ($team) {
                            /* @var Teams $season */
                            return $team->id;
                        },
                        Teams::model()->findAll(new CDbCriteria(['select' => 'id']))
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
            'id' => 'Код игрока',
            'amplua' => 'Код амплуа (вратарь, нападающий, и т.д.)',
            'citizenship' => 'Гражданство',
            'resident' => 'Cтрана, где игрок является резидентом (проживает)',
            'bio' => 'Биография',
            'surname' => 'Фамилия',
            'first_name' => 'Имя',
            'patronymic' => 'Отчество',
            'nickname' => 'Ник',
            'other_langs' => 'ФИО на других языках',
            'borned' => 'Дата д.р.',
            'height' => 'Рост',
            'weight' => 'Вес',
            'achivements' => '',
            'profile' => '',
            'zenit' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Players Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
