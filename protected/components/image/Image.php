<?php

/**
 * Работа с изображениями (загрузка файла).
 *
 * @package    SamoletCMS
 * @subpackage image
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2010-2014
 */
class Image
{
    /**
     * Загрузка изображения.
     *
     * Позволяет загружать изображение из файла, URL, двоичных данных или GD
     * дескриптора (handle) изображения.
     *
     * @param mixed        $file  Файл. Имя файла, URL, двоичные данные или GD дескриптор.
     * @param integer|null $index Индекс файла в массиве если загрузка из $_FILES.
     *
     * @return ImageCommon Объект изображения.
     *
     * @throws CException
     */
    public function load($file = '', $index = null)
    {
        if (empty($file)) {
            throw new CException('fail ' . __METHOD__);
        }

        $func = '';

        // gd дескриптор?
        if ($this->isGdHandle($file)) {
            $func = 'Handle';
        }

        // бинарные данные?
        if (!$func) {
            // ищем первые $bin_length байт символов меньше 32 (бинарные данные изображения)
            $bin_length  = 64;
            $file_length = strlen($file);
            $max_len     = $file_length > $bin_length ? $bin_length : $file_length;

            for ($i = 0; $i < $max_len; $i++) {
                if (ord($file[$i]) < 32) {
                    $func = 'String';

                    break;
                }
            }
        }

        // Uploaded image (array uploads not supported)
        if (isset($_FILES[$file]) && isset($_FILES[$file]['tmp_name'])) {
            $func = 'Upload';
        }

        // остались файл или URL
        if (!$func) {
            $func = 'File';
        }

        return call_user_func_array(array($this, 'loadFrom' . $func), array($file, $index));
    }

    /**
     * Создание объекта изображения из файла или URL.
     *
     * @param string $file Путь к файлу или URL.
     *
     * @return ImageCommon Объект изображения.
     *
     * @throws CException
     */
    private function loadFromFile($file)
    {
        $data   = file_get_contents($file);
        $handle = imagecreatefromstring($data);

        // угадываем тип файла
        if (!$this->isGdHandle($handle)) {
            $guess  = new Guess();
            $type   = $guess->guessType($file);
            $handle = $type->load($file);
        }

        if (!$this->isGdHandle($handle)) {
            throw new CException('fail ' . __METHOD__);
        }

        return $this->loadFromHandle($handle);
    }

    /**
     * Создание и загрузка изображения из двоичных данных.
     *
     * @param string $data Двоичные данные.
     *
     * @return ImageCommon Объект изображения.
     *
     * @throws CException
     */
    private function loadFromString($data)
    {
        $handle = imagecreatefromstring($data);

        if (!$this->isGdHandle($handle)) {
            throw new CException('fail ' . __METHOD__);
        }

        return $this->loadFromHandle($handle);
    }

    /**
     * Загрузка изображений из массива $_FILES.
     *
     * @param string  $file  Имя поля файла в массиве.
     * @param integer $index Индекс файла если файлов несколько и нужно какой-то один.
     *
     * @return mixed Массив изображений или одно изображение.
     *
     * @throws CException
     */
    private function loadFromUpload($file, $index = null)
    {
        if (is_array($_FILES[$file]['tmp_name'])) {
            if (isset($_FILES[$file]['tmp_name'][$index])) {
                $file = $_FILES[$file]['tmp_name'][$index];
            } else {
                $result = array();

                foreach ($_FILES[$file]['tmp_name'] as $idx => $tmp_name) {
                    $result[$idx] = $this->loadFromFile($tmp_name);
                }

                return $result;
            }
        } else {
            $file = $_FILES[$file]['tmp_name'];
        }

        if (!file_exists($file)) {
            throw new CException('fail ' . __METHOD__);
        }

        return $this->loadFromFile($file);
    }

    /**
     * Создание и загрузка изображения из дескриптора.
     *
     * @param resource $handle GD дескриптор изображения.
     *
     * @return ImageCommon Объект изображения.
     *
     * @throws CException
     */
    private function loadFromHandle($handle)
    {
        if (!$this->isGdHandle($handle)) {
            throw new CException('fail ' . __METHOD__);
        }

        return imageistruecolor($handle) ? new ImageTrueColor($handle) : new ImagePlain($handle);
    }

    /**
     * Проверка файла на GD дескриптор.
     *
     * @param mixed $handle Дескриптор.
     *
     * @return bool
     */
    private function isGdHandle($handle)
    {
        return is_resource($handle) && get_resource_type($handle) == 'gd';
    }
}
