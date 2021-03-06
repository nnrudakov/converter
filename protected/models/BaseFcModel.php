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
     * Русский.
     *
     * @var integer
     */
    const LANG_RU = 1;

    /**
     * Английский.
     *
     * @var integer
     */
    const LANG_EN = 2;

    /**
     * Испанский.
     *
     * @var integer
     */
    const LANG_ES = 3;

    /**
     * @var integer
     */
    const FC_MODULE_ID = 26;

    /**
     * @var integer
     */
    const NEWS_MODULE_ID = 27;

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
