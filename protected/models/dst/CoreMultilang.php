<?php

/**
 * Модель таблицы "fc__core__multilang".
 *
 * Доступные поля таблицы "fc__core__multilang":
 *
 * @property string  $id        Идентификатор.
 * @property integer $module_id Идентификатор модуля.
 * @property string  $entity    Имя сущности.
 * @property integer $import_id Идентификатор импорта.
 *
 * Доступные отношения:
 * @property CoreModules         $module
 * @property CoreMultilangLink[] $entities
 *
 * @package    converter
 * @subpackage coremultilang
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class CoreMultilang extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{core__multilang}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['module_id, import_id', 'numerical', 'integerOnly'=>true],
            ['entity', 'length', 'max'=>20],
            ['id, module_id, entity, import_id', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'module'   => [self::BELONGS_TO, 'CoreModules',       'module_id'],
            'entities' => [self::HAS_MANY,   'CoreMultilangLink', 'multilang_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Идентификатор',
            'module_id' => 'Идентификатор модуля',
            'entity' => 'Имя сущности',
            'import_id' => 'Идентификатор импорта'
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
        $criteria->compare('id', $this->$name, true);
        $criteria->compare('module_id', $this->$name);
        $criteria->compare('entity', $this->$name, true);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return CoreMultilang Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
