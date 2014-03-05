<?php

/**
 * Модель таблицы "info_store.news_categs".
 *
 * Доступные поля таблицы "info_store.news_categs":
 *
 * @property string  $id          Идентификатор.
 * @property string  $parentid    Код родителя, максимум - 4, например, новости на главной зенит-м актуальные.
 * @property string  $name        Наименование категории.
 * @property string  $description Описание.
 * @property boolean $hasfilter   Является ли категорией отфильтрованной.
 * @property string  $path        Путь.
 * @property string  $ord         Порядок.
 * @property boolean $hidden      Скрытая.
 *
 * @package    Converter
 * @subpackage newscategs
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class NewsCategs extends CActiveRecord
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return 'info_store.news_categs';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['name', 'length', 'max'=>255],
            ['path', 'length', 'max'=>512],
            ['parentid, description, hasfilter, ord, hidden', 'safe'],
            ['id, parentid, name, description, hasfilter, path, ord, hidden', 'safe', 'on'=>'search'],
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
            'id' => '',
            'parentid' => 'Код родителя',
            'name' => 'Наименование категории',
            'description' => 'Описание',
            'hasfilter' => 'Является ли категорией отфильтрованной',
            'path' => '',
            'ord' => '',
            'hidden' => '',
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
		$criteria->compare('id', $this->id, true);
		$criteria->compare('parentid', $this->parentid, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('description', $this->description, true);
		$criteria->compare('hasfilter', $this->hasfilter);
		$criteria->compare('path', $this->path, true);
		$criteria->compare('ord', $this->ord, true);
		$criteria->compare('hidden', $this->hidden);

        return new CActiveDataProvider($this, ['criteria' => $criteria]);
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return NewsCategs Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
