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
class DestinationBranchModel extends BaseFcModel
{
    use TMultilang;
    use TFiles;

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
    public static $dbDstB = null;

    /**
     * Префикс внешних файлов модели.
     *
     * @var string
     */
    public $filesUrl = '';

    /**
     * Сохранить файлы на диск.
     *
     * @var bool
     */
    public $writeFiles = false;

    /**
     * @var integer
     */
    public $lang = self::LANG_RU;

    /**
     * @var integer
     */
    public $multilangId = 0;

    /**
     * @var integer
     */
    public $importId = 0;

    /**
     * Параметры файла модели.
     *
     * @var array
     */
    public $fileParams = [];

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
     * @return CDbConnection|mixed
     * @throws CDbException
     */
    public function getDbConnection()
    {
        if (is_null(self::$dbDstB)) {

            self::$dbDstB = Yii::app()->db_dst_b;

            if (self::$dbDstB instanceof CDbConnection) {
                self::$dbDstB->setActive(true);
                return self::$dbDstB;
            } else {
                throw new CDbException(
                    Yii::t(
                        'yii',
                        'Active Record requires a "db_dst_b" CDbConnection application component.'
                    )
                );
            }
        }

        return self::$dbDstB;
    }

    /**
     * Получение идентификатора.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Установка новой записи.
     *
     * @return bool
     */
    public function setNew()
    {
        $this->setIsNewRecord(true);
        $this->id = null;
        $this->lang = self::LANG_EN;
    }

    protected function afterSave()
    {
        $this->setMultilang();
        $this->saveFile();

        parent::afterSave();
    }
}
