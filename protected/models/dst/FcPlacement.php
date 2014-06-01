<?php

/**
 * Модель таблицы "{{fc__placement}}".
 *
 * Доступные поля таблицы "{{fc__placement}}":
 * @property integer $id Идентификатор.
 * @property integer $match_id Матч.
 * @property integer $team_id Команда.
 * @property integer $person_id Персона.
 * @property integer $captain Капитан.
 * @property double  $xpos Расположение на поле по горизонтали.
 * @property double  $ypos Расположение на поле по вертикали, в минусах - значит в запасе.
 * @property string  $staff В основном или запасном составе.
 *
 * @package    converter
 * @subpackage fcplacement
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcPlacement extends DestinationModel
{
    /**
     * Основной состав.
     *
     * @var string
     */
    const STAFF_MAIN = 'main';

    /**
     * Запасной состав.
     *
     * @var string
     */
    const STAFF_SPARE = 'spare';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__placement}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['match_id, team_id, person_id', 'required'],
            ['match_id, team_id, person_id, captain', 'numerical', 'integerOnly'=>true],
            ['xpos, ypos', 'numerical'],
            ['staff', 'length', 'max'=>5],
            ['id, match_id, team_id, person_id, captain, xpos, ypos, staff', 'safe', 'on'=>'search'],
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
            'match_id' => 'Матч',
            'team_id' => 'Команда',
            'person_id' => 'Персона',
            'captain' => 'Капитан',
            'xpos' => 'Расположение на поле по горизонтали',
            'ypos' => 'Расположение на поле по вертикали, в минусах - значит в запасе',
            'staff' => 'В основном или запасном составе',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcPlacement Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
