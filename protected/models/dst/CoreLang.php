<?php

/**
 * Модель таблицы "fc__core__lang".
 *
 * Доступные поля таблицы "fc__core__lang":
 *
 * @property integer $lang_id       Идентификатор.
 * @property string  $name          Name.
 * @property string  $name_ru       Name ru.
 * @property string  $name_local    Name local.
 *
 * @package    converter
 * @subpackage corelang
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2016
 */
class CoreLang extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{core__lang}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['name, name_ru, name_local', 'required'],
            ['lang_id', 'numerical', 'integerOnly'=>true],
            ['lang_id, name, name_ru, name_local', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'lang_id' => 'Идентификатор модуля',
            'name' => 'Name',
            'name_ru' => 'Name ru',
            'name_local' => 'NAme local',
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
        $criteria->compare('lang_id', $this->$name, true);
        $criteria->compare('name', $this->$name, true);
        $criteria->compare('name_ru', $this->$name);
        $criteria->compare('name_local', $this->$name);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return CoreModules Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
