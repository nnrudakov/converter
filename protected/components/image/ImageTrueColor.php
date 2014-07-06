<?php

/**
 * Работа с "truecolor" изображениями.
 *
 * @package    SamoletCMS
 * @subpackage image
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2010-2014
 */
class ImageTrueColor extends ImageCommon
{
    /**
     * Инициализация.
     *
     * @param resource $handle Изображение (дескриптор).
     *
     * @return ImageTrueColor Изображение.
     */
    public function __construct($handle)
    {
        if (is_null($handle)) {
            return null;
        }

        parent::__construct($handle);

        $this->alphaBlending(false);
        $this->saveAlpha(true);
    }

    /**
     * Создание изображения.
     *
     * @param int $width  Ширина.
     * @param int $height Высота.
     *
     * @return ImageTrueColor Изображение.
     *
     * @throws CException
     */
    public function create($width, $height)
    {
        if ($width * $height <= 0 || $width < 0) {
            throw new CException('fail ' . __METHOD__);
        }

        return new ImageTrueColor(imagecreatetruecolor($width, $height));
    }

    /**
     * Установка режима смешивания.
     *
     * @param bool $mode Установить режим.
     *
     * @return bool
     *
     * @see imagealphablending
     */
    public function alphaBlending($mode)
    {
        return imagealphablending($this->handle, $mode);
    }

    /**
     * Сохранение сведений об альфа-канале.
     *
     * @param bool $on Сохранить сведения.
     *
     * @return bool
     *
     * @see imagesavealpha
     */
    public function saveAlpha($on)
    {
        return imagesavealpha($this->handle, $on);
    }

    /**
     * @return true
     *
     * @see ImageCommon#isTrueColor
     */
    public function isTrueColor()
    {
        return true;
    }

    /**
     * @see ImageCommon#asTrueColor()
     */
    public function asTrueColor()
    {
        return $this->copy();
    }

    /**
     *  Установка цвета RGB значениями.
     *
     * @param mixed $r Только красный цвет или полный ассоциативный массив цветов
     *                 array('red' => ..., 'green' => ..., 'blue' => ...).
     * @param int   $g Значение зеленого цвета.
     * @param int   $b Значение синего цвета.
     * @param int   $a Альфа-значение.
     *
     * @return int Индекс цвета.
     *
     * @see imagecolorallocatealpha
     */
    public function allocateColorAlpha($r, $g = null, $b = null, $a = null)
    {
        return (is_array($r))
            ? imageColorAllocateAlpha($this->handle, $r['red'], $r['green'], $r['blue'], $r['alpha'])
            : imageColorAllocateAlpha($this->handle, $r, $g, $b, $a);
    }
}
