<?php

/**
 * Работа с обычными (не "truecolor") изображениями.
 *
 * @package    SamoletCMS
 * @subpackage image
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2010-2014
 */
class ImagePlain extends ImageCommon
{
    /**
     * Создание изображения.
     *
     * @param int $width  Ширина.
     * @param int $height Высота.
     *
     * @return ImagePlain Изображение.
     *
     * @throws CException
     */
    public function create($width, $height)
    {
        if ($width * $height <= 0 || $width < 0) {
            throw new CException('fail ' . __METHOD__);
        }

        return new ImagePlain(imagecreate($width, $height));
    }

    /**
     * @return false
     *
     * @see ImageCommon#isTrueColor
     */
    public function isTrueColor()
    {
        return false;
    }

    /**
     * @see ImageCommon::asTrueColor()
     */
    public function asTrueColor()
    {
        $width  = $this->getWidth();
        $height = $this->getHeight();

        $new = new ImageTrueColor(null);
        $new = $new->create($width, $height);

        if ($this->isTransparent()) {
            $new->copyTransparencyFrom($this);
        }

        if (!imageCopy($new->getHandle(), $this->handle, 0, 0, 0, 0, $width, $height)) {
            throw new CException('fail ' . __METHOD__);
        }

        return $new;
    }
}
