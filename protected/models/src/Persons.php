<?php

/**
 * Модель таблицы "info_store.persons".
 *
 * Доступные поля таблицы "info_store.persons":
 * @property string $id Код персоны.
 * @property string $citizenship Город.
 * @property string $surname Фамилия.
 * @property string $first_name Имя.
 * @property string $patronymic Отчество.
 * @property string $bio Биография.
 * @property string $borned Д.Р..
 * @property string $post Должность.
 * @property string $path .
 * @property string $ord Порядок вывода.
 * @property string $relations связанные новости.
 * @property string $achivements .
 * @property string $zenit .
 * @property string $profile .
 *
 * @package    converter
 * @subpackage persons
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Persons extends SourceModel
{
    /**
     * Префикс ссылки на фотографии.
     *
     * @var string
     */
    const PHOTO_URL = 'http://fckrasnodar.ru/app/mods/info_store/res/';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'info_store.persons';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['citizenship, post', 'length', 'max'=>255],
            ['surname, first_name, patronymic', 'length', 'max'=>150],
            ['borned', 'length', 'max'=>6],
            ['path, relations', 'length', 'max'=>512],
            ['bio, ord, achivements, zenit, profile', 'safe'],
            ['id, citizenship, surname, first_name, patronymic, bio, borned, post, path, ord, relations, achivements, zenit, profile', 'safe', 'on'=>'search'],
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
            'id' => 'Код персоны',
            'citizenship' => 'Город',
            'surname' => 'Фамилия',
            'first_name' => 'Имя',
            'patronymic' => 'Отчество',
            'bio' => 'Биография',
            'borned' => 'Д.Р.',
            'post' => 'Должность',
            'path' => '',
            'ord' => 'Порядок вывода',
            'relations' => 'связанные новости',
            'achivements' => '',
            'zenit' => '',
            'profile' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Persons Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
