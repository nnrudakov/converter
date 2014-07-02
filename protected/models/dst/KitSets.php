<?php

/**
 * Базовая модель наборов свойств конструктора.
 *
 * Доступные поля таблиц:
 * @property integer $set_id Идентификатор набора свойств.
 * @property string $title Заголовок набора свойств.
 *
 * @package    converter
 * @subpackage kitsets
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class KitSets extends DestinationModel
{
    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title', 'length', 'max'=>50],
            ['set_id, title', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'set_id' => 'Идентификатор набора свойств',
            'title' => 'Заголовок набора свойств',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return KitSets Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Получение идентификатора.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->set_id;
    }

    /**
     * Установка новой записи.
     *
     * @return bool
     */
    public function setNew()
    {
        $this->setIsNewRecord(true);
        $this->set_id = null;
        $this->lang = self::LANG_EN;
    }
}
