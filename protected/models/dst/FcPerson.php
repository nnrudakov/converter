<?php

/**
 * Модель таблицы "fc__fc__person".
 *
 * Доступные поля таблицы "fc__fc__person":
 * @property integer $id Идентификатор.
 * @property string $firstname Имя.
 * @property string $lastname Фамилия.
 * @property string $middlename Отчество.
 * @property string $birthday Дата рождения.
 * @property string $country Страна (гражданство).
 * @property integer $resident Резидент.
 * @property string $city Город.
 * @property string $biograpy Биография.
 * @property string $profile Профиль.
 * @property string $progress Достижения.
 * @property string $nickname Прозвище.
 * @property integer $height Рост.
 * @property integer $weight Вес.
 * @property string $amplua Амплуа.
 * @property string $post Должность.
 *
 * @package    converter
 * @subpackage fcperson
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcPerson extends DestinationModel
{
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
            ['resident, height, weight', 'numerical', 'integerOnly'=>true],
            ['firstname, lastname, middlename, country, city, nickname, post', 'length', 'max'=>128],
            ['profile', 'length', 'max'=>45],
            ['amplua', 'length', 'max'=>25],
            ['birthday, biograpy, progress', 'safe'],
            ['id, firstname, lastname, middlename, birthday, country, resident, city, biograpy, profile, progress, nickname, height, weight, amplua, post', 'safe', 'on'=>'search'],
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
            'id' => 'Идентификатор',
            'firstname' => 'Имя',
            'lastname' => 'Фамилия',
            'middlename' => 'Отчество',
            'birthday' => 'Дата рождения',
            'country' => 'Страна (гражданство)',
            'resident' => 'Резидент',
            'city' => 'Город',
            'biograpy' => 'Биография',
            'profile' => 'Профиль',
            'progress' => 'Достижения',
            'nickname' => 'Прозвище',
            'height' => 'Рост',
            'weight' => 'Вес',
            'amplua' => 'Амплуа',
            'post' => 'Должность',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcPerson Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
