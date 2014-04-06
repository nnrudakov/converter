<?php

/**
 * Базовый класс приемников.
 *
 * @property CoreModules $module Модуль модели сущности. Возвращается в случае если в модели указано имя модуля.
 *
 * @package    converter
 * @subpackage destination
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class DestinationModel extends CActiveRecord
{
    use TMultilang;
    use TFiles;

    /**
     * Язык.
     *
     * @var integer
     */
    const LANG = 1;

    /**
     * Идентификатор пользователя администратора.
     *
     * @var integer
     */
    const ADMIN_ID = 1;

    /**
     * Соединение с БД.
     *
     * @var CDbConnection
     */
    public static $dbDst = null;

    /**
     * Параметры файла модели.
     *
     * @var array
     */
    protected $fileParams = [];

    /**
     * Модуль модели.
     *
     * @var CoreModules
     */
    private $modelModule = null;

    /**
     * Получение свойств.
     *
     * @param string $name Имя.
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ('module' == $name) {
            if (is_null($this->modelModule)) {
                $const = get_class($this) . '::MODULE';

                if (defined($const)) {
                    $this->modelModule = CoreModules::model()->find(
                        new CDbCriteria(
                            [
                                'condition' => 'name=:name',
                                'params'    => [':name' => constant($const)]
                            ]
                        )
                    );
                }
            }

            return $this->modelModule;
        }

        return parent::__get($name);
    }

    /**
     * Представление объекта в виде строки.
     *
     * @return string
     */
    public function __toString()
    {
        return var_export($this->getAttributes(), true);
    }

    /**
     * @return CDbConnection|mixed
     * @throws CDbException
     */
    public function getDbConnection()
    {
        if (is_null(self::$dbDst)) {

            self::$dbDst = Yii::app()->db_dst;

            if (self::$dbDst instanceof CDbConnection) {
                self::$dbDst->setActive(true);
                return self::$dbDst;
            } else {
                throw new CDbException(
                    Yii::t(
                        'yii',
                        'Active Record requires a "db_dst" CDbConnection application component.'
                    )
                );
            }
        }

        return self::$dbDst;
    }

    /**
     * Установка параметров файла.
     *
     * @param integer $oldId
     * @param string  $name
     * @param integer $categoryId
     * @param string  $fieldId
     */
    public function setFileParams($oldId, $name = null, $categoryId = 0, $fieldId = null)
    {
        $this->fileParams = ['old_id' => $oldId, 'name' => $name, 'category_id' => $categoryId, 'field_id' => $fieldId];
    }

    /**
     * Вывод ошибки.
     *
     * @param string           $message
     * @param DestinationModel $model
     *
     * @return string
     */
    public function getErrorMsg($message, $model = null)
    {
        return implode(
            "\n",
            [
                $message,
                'Errors: ' . var_export($this->getErrors(), true),
                isset($model) ? 'Original object: ' . $model : ''
            ]
        ) . "\n";
    }

    protected function afterSave()
    {
        $this->setMultilang();
        $this->saveFile();

        parent::afterSave();
    }
}
