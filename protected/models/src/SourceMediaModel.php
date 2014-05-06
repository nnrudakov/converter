<?php

/**
 * Базовый класс источников.
 *
 * @package    converter
 * @subpackage source
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class SourceMediaModel extends BaseFcModel
{
    /**
     * Соединение с БД.
     *
     * @var CDbConnection
     */
    public static $dbMedia = null;

    /**
     * @return CDbConnection|mixed
     * @throws CDbException
     */
    public function getDbConnection()
    {
        if (is_null(self::$dbMedia)) {

            self::$dbMedia = Yii::app()->db_media;

            if (self::$dbMedia instanceof CDbConnection) {
                self::$dbMedia->setActive(true);
                return self::$dbMedia;
            } else {
                throw new CDbException(
                    Yii::t(
                        'yii',
                        'Active Record requires a "db_media" CDbConnection application component.'
                    )
                );
            }
        }

        return self::$dbMedia;
    }

    public function save($runValidation = true, $attributes = null)
    {
        return false;
    }
}
