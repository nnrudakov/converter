<?php

/**
 * Модель таблицы "{{fc__match}}".
 *
 * Доступные поля таблицы "{{fc__match}}":
 *
 * @property integer        $id                  Идентификатор.
 * @property integer        $multilang_id        Id.
 * @property integer        $championship_id     Идентификатор чемпионата.
 * @property integer        $season_id           Идентификатор сезона.
 * @property integer        $stage_id            Идентификатор стадии чемпионата.
 * @property string         $tour                Номер тура.
 * @property integer        $home_team_id        Идентификатор команды хозяев.
 * @property integer        $guest_team_id       Идентификатор команды гостей.
 * @property string         $country             Страна.
 * @property string         $city                Город.
 * @property string         $stadium             Стадион.
 * @property integer        $viewers             Количество зрителей.
 * @property string         $referee_main        Главный арбитр.
 * @property string         $referee_line_1      Линейный арбитр 1.
 * @property string         $referee_line_2      Линейный арбитр 2.
 * @property string         $referee_main_helper Помощник главного арбитра.
 * @property string         $delegate            Делегат.
 * @property string         $inspector           Инспектор.
 * @property string         $weather             Погода.
 * @property integer        $held                Состоялся.
 * @property string         $matchtime           Время начала.
 * @property integer        $home_score          Счет хозяев.
 * @property integer        $guest_score         Счет гостей.
 * @property integer        $lang_id             Идентификатор языка.
 *
 * @property FcChampionship $champ
 * @property FcSeason       $season
 * @property FcStage        $stage
 * @property FcTeams        $homeTeam
 * @property FcTeams        $guestTeam
 *
 * @package    converter
 * @subpackage fcmatch
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcMatch extends DestinationModel
{
    /**
     * Имя сущности для многоязычности.
     *
     * @var string
     */
    const ENTITY = 'match';

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
        return '{{fc__match}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['season_id, home_team_id, guest_team_id', 'required'],
            [
                'multilang_id, lang_id, championship_id, season_id, stage_id, home_team_id, guest_team_id, viewers, held, home_score, guest_score',
                'numerical',
                'integerOnly' => true
            ],
            [
                'tour, country, city, stadium, referee_main, referee_line_1, referee_line_2, referee_main_helper, delegate, inspector, weather',
                'length',
                'max' => 128
            ],
            ['matchtime', 'safe'],
            [
                'id, championship_id, season_id, stage_id, tour, home_team_id, guest_team_id, country, city, stadium, viewers, referee_main, referee_line_1, referee_line_2, referee_main_helper, delegate, inspector, weather, held, matchtime, home_score, guest_score',
                'safe',
                'on' => 'search'
            ],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'champ'     => [self::BELONGS_TO, FcChampionship::class, 'championship_id'],
            'season'    => [self::BELONGS_TO, FcSeason::class, 'season_id'],
            'stage'     => [self::BELONGS_TO, FcStage::class, 'stage_id'],
            'homeTeam'  => [self::BELONGS_TO, FcTeams::class, 'home_team_id'],
            'guestTeam' => [self::BELONGS_TO, FcTeams::class, 'guest_team_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id'                  => 'Идентификатор',
            'championship_id'     => 'Идентфикатор чемпионата',
            'season_id'           => 'Идентификатор сезона',
            'stage_id'            => 'Идентификатор стадии чемпионата',
            'tour'                => 'Номер тура',
            'home_team_id'        => 'Идентификатор команды хозяев',
            'guest_team_id'       => 'Идентификатор команды гостей',
            'country'             => 'Страна',
            'city'                => 'Город',
            'stadium'             => 'Стадион',
            'viewers'             => 'Количество зрителей',
            'referee_main'        => 'Главный арбитр',
            'referee_line_1'      => 'Линейный арбитр 1',
            'referee_line_2'      => 'Линейный арбитр 2',
            'referee_main_helper' => 'Помощник главного арбитра',
            'delegate'            => 'Делегат',
            'inspector'           => 'Инспектор',
            'weather'             => 'Погода',
            'held'                => 'Состоялся',
            'matchtime'           => 'Время начала',
            'home_score'          => 'Счет хозяев',
            'guest_score'         => 'Счет гостей',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     *
     * @return FcMatch Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
