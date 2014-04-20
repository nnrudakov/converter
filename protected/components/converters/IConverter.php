<?php

/**
 * Интерфейс конвертеров.
 *
 * @package    converter
 * @subpackage con
 * @author     rudnik <n.rudakov@bstsoft.ru>
 * @copyright  2014
 */
interface IConverter
{
    /**
     * Формат файлов соотвествий.
     *
     * @var string
     */
    const FILE_ACCORDANCE = "<?php\n\nreturn %s;\n";

    /**
     * Запуск преобразований.
     */
    public function convert();
}
