<?php

/**
 * Модель таблицы "{{fc__event}}".
 *
 * Доступные поля таблицы "{{fc__event}}":
 * @property integer $id Идентификатор.
 * @property integer $match_id Матч.
 * @property string  $type Тип события.
 * @property integer $gametime Время события в игре.
 * @property integer $gametimeplus Дополнительное время.
 * @property integer $team_id Команда.
 * @property integer $person_id Футболист.
 * @property string  $comment Комментарий.
 *
 * @package    converter
 * @subpackage fcevent
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcEvent extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__event}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['match_id', 'required'],
            ['match_id, gametime, gametimeplus, team_id, person_id', 'numerical', 'integerOnly'=>true],
            ['type', 'length', 'max'=>18],
            ['comment', 'safe'],
            ['id, match_id, type, gametime, gametimeplus, team_id, person_id, comment', 'safe', 'on'=>'search'],
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
            'type' => 'Тип события',
            'gametime' => 'Время события в игре',
            'gametimeplus' => 'Дополнительное время',
            'team_id' => 'Команда',
            'person_id' => 'Футболист',
            'comment' => 'Комментарий',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcEvent Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
