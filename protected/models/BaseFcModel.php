<?php

/**
 * Базовая модель для моделей ФК.
 *
 * @package    converter
 * @subpackage base
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class BaseFcModel extends CActiveRecord
{
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
     * Вывод ошибки.
     *
     * @param string                                        $message
     * @param DestinationModel|SourceModel|SourceMediaModel $model
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
}
