<?php

/**
 * Модель таблицы "{{fc__stage}}".
 *
 * Доступные поля таблицы "{{fc__stage}}":
 * @property integer $id Идентификатор.
 * @property integer $championship_id Идентификатор чемпионата.
 * @property string $title Короткое название.
 * @property string $fullTitle Полное название.
 * @property string $style Cтиль проведение.
 * @property string $reglament Регламент.
 * @property string $group0title Название группы 0.
 * @property integer $group0placefrom В группу 0 с места.
 * @property integer $group0placeto В группу 0 по место.
 * @property string $group1title Название группы 1.
 * @property integer $group1placefrom В группу 1 с места.
 * @property integer $group1placeto В группу 1 по место.
 * @property string $group2title Название группы 2.
 * @property integer $group2placefrom В группу 2 с места.
 * @property integer $group2placeto В группу 2 по место.
 * @property string $group3title Название группы 3.
 * @property integer $group3placefrom В группу 3 с места.
 * @property integer $group3placeto В группу 3 по место.
 * @property string $group4title Название группы 4.
 * @property integer $group4placefrom В группу 4 с места.
 * @property integer $group4placeto В группу 4 по место.
 *
 * Доступные отношения:
 * @property FcChampionship $champ
 *
 * @package    converter
 * @subpackage fcstage
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcStage extends DestinationModel
{
    /**
     * Круговой этап.
     *
     * @var string
     */
    const STYLE_ROUND = 'круговой';

    /**
     * Кубковый этап.
     *
     * @var string
     */
    const STYLE_CAP = 'круговой';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__stage}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['championship_id, title, fullTitle', 'required'],
            ['group0placefrom, group0placeto, group1placefrom, group1placeto, group2placefrom, group2placeto, group3placefrom, group3placeto, group4placefrom, group4placeto', 'numerical', 'integerOnly'=>true, 'allowEmpty' => true],
            ['title, fullTitle, group0title, group1title, group2title, group3title, group4title', 'length', 'max'=>128],
            ['style', 'length', 'max'=>16],
            ['reglament', 'safe'],
            ['id, championship_id, title, fullTitle, style, reglament, group0title, group0placefrom, group0placeto, group1title, group1placefrom, group1placeto, group2title, group2placefrom, group2placeto, group3title, group3placefrom, group3placeto, group4title, group4placefrom, group4placeto', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'champ' => [self::BELONGS_TO, 'FcChampionship', 'championship_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'championship_id' => 'Идентификатор чемпионата',
            'title' => 'Короткое название',
            'fullTitle' => 'Полное название',
            'style' => 'Cтиль проведение',
            'reglament' => 'Регламент',
            'group0title' => 'Название группы 0',
            'group0placefrom' => 'В группу 0 с места',
            'group0placeto' => 'В группу 0 по место',
            'group1title' => 'Название группы 1',
            'group1placefrom' => 'В группу 1 с места',
            'group1placeto' => 'В группу 1 по место',
            'group2title' => 'Название группы 2',
            'group2placefrom' => 'В группу 2 с места',
            'group2placeto' => 'В группу 2 по место',
            'group3title' => 'Название группы 3',
            'group3placefrom' => 'В группу 3 с места',
            'group3placeto' => 'В группу 3 по место',
            'group4title' => 'Название группы 4',
            'group4placefrom' => 'В группу 4 с места',
            'group4placeto' => 'В группу 4 по место',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcStage Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
