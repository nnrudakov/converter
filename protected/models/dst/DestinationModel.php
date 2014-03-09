<?php

/**
 * Базовый класс приемников.
 *
 * @package    converter
 * @subpackage destination
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2013 BST//soft
 */
class DestinationModel extends CActiveRecord
{
    /**
     * Соединение с БД.
     *
     * @var CDbConnection
     */
    public static $dbDst = null;

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
}
