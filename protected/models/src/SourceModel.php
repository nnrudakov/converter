<?php

/**
 * Базовый класс источников.
 *
 * @package    converter
 * @subpackage source
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class SourceModel extends CActiveRecord
{
    /**
     * Соединение с БД.
     *
     * @var CDbConnection
     */
    public static $dbSrc = null;

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
        if (is_null(self::$dbSrc)) {

            self::$dbSrc = Yii::app()->db_src;

            if (self::$dbSrc instanceof CDbConnection) {
                self::$dbSrc->setActive(true);
                return self::$dbSrc;
            } else {
                throw new CDbException(
                    Yii::t(
                        'yii',
                        'Active Record requires a "db_src" CDbConnection application component.'
                    )
                );
            }
        }

        return self::$dbSrc;
    }

    public function save($runValidation = true, $attributes = null)
    {
        return false;
    }
}
