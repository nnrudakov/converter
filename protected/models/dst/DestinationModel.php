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
class DestinationModel extends BaseFcModel
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
    public static $dbDst = null;

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
     * @var bool
     */
    public $setOwner = true;

    /**
     * @var bool
     */
    public $setMultilang = true;

    /**
     * @var bool
     */
    public $setParents = true;

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
     * Получение идентификатора.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Установка параметров файла.
     *
     * @param integer $oldId
     * @param string  $name
     * @param integer $categoryId
     * @param string  $fieldId
     * @param string  $descr
     * @param integer $sort
     * @param integer $videoTime
     * @param integer $thumbs
     * @param string  $path
     */
    public function setFileParams($oldId, $name = null, $categoryId = 0, $fieldId = null, $descr = '', $sort = 1, $videoTime = 0, $thumbs = 0, $path = '')
    {
        $this->fileParams[] = [
            'old_id'      => $oldId,
            'name'        => $name,
            'category_id' => $categoryId,
            'field_id'    => $fieldId,
            'descr'       => $descr,
            'sort'        => $sort,
            'video_time'  => $videoTime,
            'thumbs'      => $thumbs,
            'path'        => $path
        ];
    }

    /**
     * Установка новой записи.
     *
     * @param integer $lang
     *
     * @return bool
     */
    public function setNew($lang = self::LANG_EN)
    {
        $this->setIsNewRecord(true);
        $this->id = null;
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        $const_entity = get_class($this) . '::ENTITY';
        return defined($const_entity) ? constant($const_entity) : '';
    }

    /**
     * @return integer
     */
    public function getPairId()
    {
        if (!$this->multilangId) {
            $this->multilangId = (int) $this->dbConnection->createCommand(
                'SELECT
                    `ml`.`entity_id`
                FROM
                    `fc__core__multilang_link` AS `ml`
                    JOIN `fc__core__multilang` AS `m`
                        ON `m`.`id`=`ml`.`multilang_id`
                        AND `m`.`id`=:id
                        AND `ml`.`lang_id`=:lang_id'
            )->queryScalar([':id' => $this->getMultilangId(), ':lang_id' => BaseFcModel::LANG_EN]);
        }

        return $this->multilangId;
    }

    /**
     * @return integer
     */
    public function getMultilangId()
    {
        return (int) $this->dbConnection->createCommand(
            'SELECT
                `m`.`id` AS `id`
            FROM
                `fc__core__multilang` AS `m`
                JOIN `fc__core__multilang_link` AS `ml`
                    ON `ml`.`multilang_id`=`m`.`id`
                    AND `m`.`module_id`=:module_id
                    AND `m`.`entity`=:entity
                    AND `ml`.`entity_id`=:entity_id'
        )->queryScalar(
            [
                ':module_id' => $this->module->module_id,
                ':entity'    => $this->getEntityName(),
                ':entity_id' => $this->getId()
            ]
        );
    }

    protected function afterSave()
    {
        if ($this->setMultilang) {
            $this->setMultilang();
        }
        $this->saveFile();

        parent::afterSave();
    }
}
