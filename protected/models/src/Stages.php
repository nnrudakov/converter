<?php

/**
 * Модель таблицы "tsi.stages".
 *
 * Доступные поля таблицы "tsi.stages":
 * @property integer $id .
 * @property integer $tournament .
 * @property string $title .
 * @property string $short .
 * @property string $style Стиль проведения этапа: кубковый - cap, круговой - round.
 * @property string $reglamentar .
 * @property integer $ord .
 *
 * Доступные отношения:
 * @property Tournament $tour
 *
 * @package    converter
 * @subpackage stages
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Stages extends SourceModel
{
    /**
     * Кубковый этап.
     *
     * @var string
     */
    const STYLE_CAP = 'cap';

    /**
     * Круговой этап.
     *
     * @var string
     */
    const STYLE_ROUND = 'round';

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'tsi.stages';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title', 'length', 'max'=>255],
            ['short', 'length', 'max'=>20],
            ['style', 'length', 'max'=>10],
            ['reglamentar', 'length', 'max'=>1000],
            ['tournament, ord', 'safe'],
            ['id, tournament, title, short, style, reglamentar, ord', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'tour' => [self::BELONGS_TO, 'Tournaments', 'tournament']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => '',
            'tournament' => '',
            'title' => '',
            'short' => '',
            'style' => 'Стиль проведения этапа: кубковый - cap, круговой - round',
            'reglamentar' => '',
            'ord' => '',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Stages Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function isCap()
    {
        return $this->style == self::STYLE_CAP;
    }

    public function isRound()
    {
        return $this->style == self::STYLE_ROUND;
    }
}
