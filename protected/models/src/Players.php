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
 * @package    converter
 * @subpackage players
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Players extends SourceModel
{
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
        return [];
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
