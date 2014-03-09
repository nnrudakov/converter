<?php

/**
 * Модель таблицы "fc__core__multilang_link".
 *
 * Доступные поля таблицы "fc__core__multilang_link":
 *
 * @property string  $multilang_id Общий идентифкатор.
 * @property integer $entity_id    Идентификатор объекта сущности.
 * @property integer $lang_id      Идентификатор языка.
 *
 * Доступные отношения:
 * @property CoreMultilang $multilang
 *
 * @package    converter
 * @subpackage coremultilanglink
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class CoreMultilangLink extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{core__multilang_link}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['entity_id, lang_id', 'numerical', 'integerOnly'=>true],
            ['multilang_id', 'length', 'max'=>20],
            ['multilang_id, entity_id, lang_id', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'multilang' => [self::BELONGS_TO, 'CoreMultilang', 'multilang_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'multilang_id' => 'Общий идентифкатор',
            'entity_id' => 'Идентификатор объекта сущности',
            'lang_id' => 'Идентификатор языка',
        ];
    }

    /**
     * Построение условий поиска.
     *
     * @return CActiveDataProvider Модели с применением фильтров.
     */
    public function search()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('multilang_id', $this->$name, true);
        $criteria->compare('entity_id', $this->$name);
        $criteria->compare('lang_id', $this->$name);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return CoreMultilangLink Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
