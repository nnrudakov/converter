<?php

/**
 * Модель таблицы "fc__fc__person".
 *
 * Доступные поля таблицы "fc__fc__person":
 *
 * @property integer $id               Идентификатор.
 * @property integer $multilang_id     Id.
 * @property string  $firstname        Имя.
 * @property string  $lastname         Фамилия.
 * @property string  $middlename       Отчество.
 * @property string  $birthday         Дата рождения.
 * @property string  $citizenship      Страна (гражданство).
 * @property integer $resident         Резидент.
 * @property string  $city             Город.
 * @property string  $biograpy         Биография.
 * @property string  $profile          Профиль.
 * @property string  $progress         Достижения.
 * @property string  $nickname         Прозвище.
 * @property integer $height           Рост.
 * @property integer $weight           Вес.
 * @property string  $amplua           Амплуа.
 * @property string  $post             Должность.
 * @property integer $lang_id          Идентификатор языка.
 *
 * @package    converter
 * @subpackage fcperson
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcPerson extends DestinationModel
{
    /**
     * Имя сущности для многоязычности.
     *
     * @var string
     */
    const ENTITY = 'person';

    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'fc';

    /**
     * Имя файла игрока на его странице.
     *
     * @var string
     */
    const FILE = 'images/players.%d.189x319.png';

    /**
     * Имя файла игрока в списке.
     *
     * @var string
     */
    const FILE_LIST = 'images/players.%d.130x189.png';

    /**
     * Имя файла игрока в информере.
     *
     * @var string
     */
    const FILE_INFORMER = 'images/players.%d.61x71.png';

    /**
     * Имя поля связки основного файла.
     *
     * @param string
     */
    const FILE_FIELD = 'player_file';

    /**
     * Имя поля связки файла со списка.
     *
     * @param string
     */
    const FILE_FIELD_LIST = 'player_file_list';

    /**
     * Имя поля связки файла информера.
     *
     * @param string
     */
    const FILE_FIELD_INFORMER = 'player_file_informer';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__person}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['multilang_id, lang_id, resident, height, weight', 'numerical', 'integerOnly' => true],
            ['firstname, lastname, middlename, citizenship, city, nickname, post', 'length', 'max' => 128],
            ['profile', 'length', 'max' => 45],
            ['amplua', 'length', 'max' => 25],
            ['birthday, biograpy, progress', 'safe'],
            [
                'id, firstname, lastname, middlename, birthday, citizenship, resident, city, biograpy, profile, progress, nickname, height, weight, amplua, post',
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
        return [];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'Идентификатор',
            'firstname'   => 'Имя',
            'lastname'    => 'Фамилия',
            'middlename'  => 'Отчество',
            'birthday'    => 'Дата рождения',
            'citizenship' => 'Страна (гражданство)',
            'resident'    => 'Резидент',
            'city'        => 'Город',
            'biograpy'    => 'Биография',
            'profile'     => 'Профиль',
            'progress'    => 'Достижения',
            'nickname'    => 'Прозвище',
            'height'      => 'Рост',
            'weight'      => 'Вес',
            'amplua'      => 'Амплуа',
            'post'        => 'Должность',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     *
     * @return FcPerson Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
