<?php

/**
 * Модель таблицы "fc__core__modules".
 *
 * Доступные поля таблицы "fc__core__modules":
 *
 * @property integer $module_id Идентификатор модуля.
 * @property string  $name      Имя модуля.
 * @property integer $is_show   Показывать в навигации.
 * @property integer $is_kit    Подмодуль.
 *
 * Доступные отношения:
 * @property CoreMultilang    $multilang
 * @property AdminUsersOwners $admin_owners
 *
 * @package    converter
 * @subpackage coremodules
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class CoreModules extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{core__modules}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['is_show, is_kit', 'numerical', 'integerOnly'=>true],
            ['name', 'length', 'max'=>30],
            ['module_id, name, is_show, is_kit', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'multilang'    => [self::HAS_MANY, 'CoreMutilang',     'module_id'],
            'admin_owners' => [self::HAS_MANY, 'AdminUsersOwners', 'module_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'module_id' => 'Идентификатор модуля',
            'name' => 'Имя модуля',
            'is_show' => 'Показывать в навигации',
            'is_kit' => 'Подмодуль',
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
        $criteria->compare('module_id', $this->$name, true);
        $criteria->compare('name', $this->$name, true);
        $criteria->compare('is_show', $this->$name);
        $criteria->compare('is_kit', $this->$name);

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
