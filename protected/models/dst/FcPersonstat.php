<?php

/**
 * Модель таблицы "{{fc__personstat}}".
 *
 * Доступные поля таблицы "{{fc__personstat}}":
 * @property integer $id Идентификатор.
 * @property integer $person_id Футболист.
 * @property integer $team_id Команда.
 * @property integer $season_id Сезон.
 * @property integer $championship_id Чемпионат.
 * @property integer $gamecount Количество игр.
 * @property integer $startcount Количество выходов в стартовом составе.
 * @property integer $benchcount Количество выходов на замену.
 * @property integer $replacementcount Количество замен.
 * @property integer $goalcount Количество голов.
 * @property integer $assistcount Количество голевых передач.
 * @property integer $yellowcount Количество предупреждений.
 * @property integer $redcount Количество удалений.
 * @property integer $playtime Общее сыгранное время.
 *
 * @package    converter
 * @subpackage fcpersonstat
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcPersonstat extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__personstat}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['team_id, season_id, championship_id', 'required'],
            ['person_id, team_id, season_id, championship_id, gamecount, startcount, benchcount, replacementcount, goalcount, assistcount, yellowcount, redcount, playtime', 'numerical', 'integerOnly'=>true],
            ['id, person_id, team_id, season_id, championship_id, gamecount, startcount, benchcount, replacementcount, goalcount, assistcount, yellowcount, redcount, playtime', 'safe', 'on'=>'search'],
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
            'person_id' => 'Футболист',
            'team_id' => 'Команда',
            'season_id' => 'Сезон',
            'championship_id' => 'Чемпионат',
            'gamecount' => 'Количество игр',
            'startcount' => 'Количество выходов в стартовом составе',
            'benchcount' => 'Количество выходов на замену',
            'replacementcount' => 'Количество замен',
            'goalcount' => 'Количество голов',
            'assistcount' => 'Количество голевых передач',
            'yellowcount' => 'Количество предупреждений',
            'redcount' => 'Количество удалений',
            'playtime' => 'Общее сыгранное время',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcPersonstat Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
