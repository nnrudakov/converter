<?php

/**
 * Обработчик типов файлов.
 *
 * @package    SamoletCMS
 * @subpackage image
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2010-2014
 */
final class TypeHandler
{
    /**
     * Тип изображения (расширение).
     *
     * @var string
     */
    private $type;

    /**
     * Инициализация.
     *
     * @param string $type Тип изображения.
     *
     * @throws CException Выбрасывается если тип не поддреживается.
     */
    public function __construct($type = '')
    {
        $this->type = ucfirst($type);

        if (!method_exists($this, 'load' . $this->type) || !method_exists($this, 'save' . $this->type)) {
            throw new CException('fail ' . __METHOD__);
        }
    }

    /**
     * Общий обработчик загрузки файла.
     *
     * @param string $file Файл.
     *
     * @return resource Дескриптор файла.
     */
    public function load($file)
    {
        return call_user_func(array($this, 'load' . $this->type), $file);
    }

    /**
     * Общий обработчик сохранения файла.
     *
     * @see ImageCommon::save
     */
    public function save()
    {
        $args = func_get_args();
        call_user_func_array(array($this, 'save' . $this->type), $args);
    }

    /**
     * Обработчик загрузки GD файла.
     *
     * @param string $file Файл.
     *
     * @return resource Дескриптор файла.
     */
    private function loadGd($file)
    {
        return imagecreatefromgd($file);
    }

    /**
     * Обработчик сохранения GD файла.
     *
     * Параметры {@link ImageCommon::saveToFile}
     */
    private function saveGd($handle, $file = null)
    {
        is_null($file) ? imagegd($handle) : imagegd($handle, $file);
    }

    /**
     * Обработчик загрузки GD2 файла.
     *
     * @param string $file Файл.
     *
     * @return resource Дескриптор файла.
     */
    private function loadGd2($file)
    {
        return imagecreatefromgd2($file);
    }

    /**
     * Обработчик сохранения GD2 файла.
     *
     * Параметры {@link ImageCommon::saveToFile}
     */
    private function saveGd2($handle, $file = null, $chunk_size = null, $type = null)
    {
        imagegd2($handle, null, $chunk_size, $type);
    }

    /**
     * Обработчик загрузки GIF файла.
     *
     * @param string $file Файл.
     *
     * @return resource Дескриптор файла.
     */
    private function loadGif($file)
    {
        return imagecreatefromgif($file);
    }

    /**
     * Обработчик сохранения GIF файла.
     *
     * Параметры {@link ImageCommon::saveToFile}
     */
    private function saveGif($handle, $file = null)
    {
        ($file) ? imagegif($handle, $file) : imagegif($handle);
    }

    /**
     * Обработчик загрузки JPG файла.
     *
     * @param string $file Файл.
     *
     * @return resource Дескриптор файла.
     */
    private function loadJpg($file)
    {
        return imagecreatefromjpeg($file);
    }

    /**
     * Обработчик сохранения JPG файла.
     *
     * @see ImageCommon::save
     */
    private function saveJpg($handle, $file = null, $quality = 100)
    {
        imagejpeg($handle, $file, $quality);
    }

    /**
     * Обработчик загрузки PNG файла.
     *
     * @param string $file Файл.
     *
     * @return resource Дескриптор файла.
     */
    private function loadPng($file)
    {
        return imagecreatefrompng($file);
    }

    /**
     * Обработчик сохранения PNG файла.
     *
     * Параметры {@link ImageCommon::saveToFile}
     */
    private function savePng($handle, $file = null, $compression = 9, $filters = PNG_ALL_FILTERS)
    {
        imagepng($handle, $file, $compression, $filters);
    }
}
