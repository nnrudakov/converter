<?php

/**
 * Модель таблицы "fc__admin_users__owners".
 *
 * Доступные поля таблицы "fc__admin_users__owners":
 * @property integer $module_id Идентификатор модуля.
 * @property integer $object_id Идентификатор объекта.
 * @property integer $extend_id Дополнительный идентификатор.
 * @property integer $user_id   Идентификатор пользователя.
 *
 * Доступные отношения:
 * @property CoreModules $module
 *
 * @package    converter
 * @subpackage adminusersowners
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class AdminUsersOwners extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{admin_users__owners}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['module_id, object_id, extend_id, user_id', 'length', 'max'=>10],
            ['module_id, object_id, extend_id, user_id', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'module' => [self::BELONGS_TO, 'CoreModules', 'module_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'module_id' => 'Идентификатор модуля',
            'object_id' => 'Идентификатор объекта',
            'extend_id' => 'Дополнительный идентификатор',
            'user_id' => 'Идентификатор пользователя',
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
        $criteria->compare('object_id', $this->$name, true);
        $criteria->compare('extend_id', $this->$name, true);
        $criteria->compare('user_id', $this->$name, true);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return AdminUsersOwners Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
