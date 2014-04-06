<?php

/**
 * Модель таблицы "{{files}}".
 *
 * Доступные поля таблицы "{{files}}":
 * @property integer $file_id Идентификатор файла.
 * @property string $ext Расширение файла.
 * @property string $name Имя файла.
 * @property string $thumb1 Имя первого превью.
 * @property string $thumb2 Имя второго превью.
 * @property string $thumb3 Имя третьего превью.
 * @property string $thumb4 Имя четвертого превью.
 * @property string $thumb5 Имя пятого превью.
 * @property string $author Автор.
 * @property string $source Источник.
 * @property string $descr Описание файла.
 * @property string $tags Тэги.
 * @property integer $size Размер файла.
 * @property string $load_date Дата загрузки файла.
 *
 * Доступные отношения:
 * @property FilesLink[] $links Свзяки файлов.
 *
 * @package    converter
 * @subpackage files
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class Files extends DestinationModel
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{files}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['load_date', 'required'],
            ['ext', 'length', 'max'=>5],
            ['name, thumb1, thumb2, thumb3, thumb4, thumb5', 'length', 'max'=>200],
            ['author', 'length', 'max'=>50],
            ['source', 'length', 'max'=>255],
            ['descr', 'length', 'max'=>500],
            ['tags', 'length', 'max'=>100],
            ['size', 'length', 'max'=>10],
            ['file_id, ext, name, thumb1, thumb2, thumb3, thumb4, thumb5, author, source, descr, tags, size, load_date', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'links' => [self::HAS_MANY, 'FilesLink', 'file_id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'file_id' => 'Идентификатор файла',
            'ext' => 'Расширение файла',
            'name' => 'Имя файла',
            'thumb1' => 'Имя первого превью',
            'thumb2' => 'Имя второго превью',
            'thumb3' => 'Имя третьего превью',
            'thumb4' => 'Имя четвертого превью',
            'thumb5' => 'Имя пятого превью',
            'author' => 'Автор',
            'source' => 'Источник',
            'descr' => 'Описание файла',
            'tags' => 'Тэги',
            'size' => 'Размер файла',
            'load_date' => 'Дата загрузки файла',
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return Files Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
