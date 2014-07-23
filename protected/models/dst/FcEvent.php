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
     * Имя сущности для многоязычности.
     *
     * @var string
     */
    const ENTITY = 'event';

    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'fc';

    /**
     * @var string
     */
    const TYPE_COMMENT = 'comment';

    /**
     * @var string
     */
    const TYPE_GOAL = 'goal';

    /**
     * @var string
     */
    const TYPE_AUTOGOAL = 'autogoal';

    /**
     * @var string
     */
    const TYPE_GOALPENALTY = 'goal-penalty';

    /**
     * @var string
     */
    const TYPE_GOALCORNER = 'goal-corner';

    /**
     * @var string
     */
    const TYPE_GOALSHTRAFNOY = 'goal-shtrafnoy';

    /**
     * @var string
     */
    const TYPE_TIMEOUT = 'timeout';

    /**
     * @var string
     */
    const TYPE_REDCARD = 'red-card';

    /**
     * @var string
     */
    const TYPE_YELLOWCARD = 'yellow-card';

    /**
     * @var string
     */
    const TYPE_SECONDYELLOWCARD = 'second-yellow-card';

    /**
     * @var string
     */
    const TYPE_LEFTONBENCH = 'left-on-bench';

    /**
     * @var string
     */
    const TYPE_CAMEOFFBENCH = 'came-off-the-bench';

    /**
     * @var string
     */
    const TYPE_MISSEDPENALTY = 'missed-penalty';

    /**
     * @var string
     */
    const TYPE_ASSISTS = 'assists';

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
