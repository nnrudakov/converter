<?php

/**
 * Основной класс для работы с изображением.
 *
 * @package    SamoletCMS
 * @subpackage image
 * @author     rudnik nnrudakov@gmail.com
 * @copyright  2010-2014
 */
abstract class ImageCommon
{
    /**
     * Дескриптор изображения.
     *
     * @var resource
     */
    protected $handle = null;

    /**
     * Инициализация.
     *
     * @param resource $handle Изображение (дескриптор GD2).
     */
    public function __construct($handle)
    {
        $this->handle = $handle;
    }

    /**
     * Изображение "truecolor".
     *
     * @return bool
     */
    abstract public function isTrueColor();

    /**
     * Возвращает "true-color" копию изображения.
     *
     * @return ImageTrueColor Изображение.
     **/
    abstract public function asTrueColor();

    /**
     * Копия изображения.
     *
     * @return ImageCommon Копия.
     */
    public function copy()
    {
        /* @var ImagePlain|ImageTrueColor $dest */
        $dest = $this->create($this->getWidth(), $this->getHeight());
        $dest->copyTransparencyFrom($this, true);
        $this->copyTo($dest, 0, 0);

        return $dest;
    }

    /**
     * Изменение размеров изображения.
     *
     * Ширина и высота изображения могут быть представлены следующим образом:
     * <ul>
     *  <li>положительные или отрицательные целые числа (100, -20, ...)</li>
     *  <li>положительные или отрицательные проценты (30%, -15%, ...)</li>
     *  <li>смешанное представление (50% - 20, 15 + 30%, ...)</li>
     * </ul>
     *
     * Если ширина не указана, то она вчисляется пропорционально высоте, и наоборот.
     *
     * @param mixed  $width     Ширина изображения.
     * @param mixed  $height    Высота изображения.
     * @param string $fit       Пропорции: <code>width</code> &mdash; по ширине; <code>height</code> &mdash; по высоте;
     *                          <code>exact</code> &mdash; точно по заданным размерам.
     * @param string $scale     Масштаб:
     *                          <ul>
     *                          <li>down. Уменьшить размеры если новое изображение больше, чем исходное;</li>
     *                          <li>up. Увеличть размеры если новое изображение меньше, чем исходное;</li>
     *                          <li>any. Изменить размеры независимо от исходного изображения.</li>
     *                          </ul>
     *
     * @return ImageCommon Измененное изображение.
     *
     * @throws CException
     */
    public function resize($width = null, $height = null, $fit = 'height', $scale = 'any')
    {
        $coordinates = new Coordinates();
        $dim         = $coordinates->prepareCoordinates($this, $width, $height, $fit);
        if (($scale === 'down' && ($dim['width'] >= $this->getWidth() && $dim['height'] >= $this->getHeight())) ||
            ($scale === 'up' && ($dim['width'] <= $this->getWidth() && $dim['height'] <= $this->getHeight()))
        ) {
            $dim = array('width' => $this->getWidth(), 'height' => $this->getHeight());
        }
        if ($dim['width'] <= 0 || $dim['height'] <= 0) {
            throw new CException('fail ' . __METHOD__);
        }
        if ($this->isTransparent()) {
            $new = new ImagePlain($this->handle);
            $new = $new->create($dim['width'], $dim['height']);
            $new->copyTransparencyFrom($this);
            imagecopyresized(
                $new->getHandle(),
                $this->getHandle(),
                0,
                0,
                0,
                0,
                $new->getWidth(),
                $new->getHeight(),
                $this->getWidth(),
                $this->getHeight()
            );
        } else {
            $new = new ImageTrueColor($this->handle);
            $new = $new->create($dim['width'], $dim['height']);
            $new->alphaBlending(false);
            $new->saveAlpha(true);
            imagecopyresampled(
                $new->getHandle(),
                $this->getHandle(),
                0,
                0,
                0,
                0,
                $new->getWidth(),
                $new->getHeight(),
                $this->getWidth(),
                $this->getHeight()
            );
            $new->alphaBlending(true);
        }

        return $new;
    }

    /**
     * Вырезание области изображения.
     *
     * Если указанные размеры выходят за границы изображения, они то ограниченич
     * усекаются до размеров (границ) изображения.
     *
     * Синтаксис указания координат {@link resize}. Также поддерживается
     * словесное указание координат.
     * Пример:
     * <code>
     * $cropped = $img->crop('right', 'bottom', 100, 200); // вырежется изображение размером 100x200 от правого нижнего угла
     * $cropped = $img->crop('center', 'middle', 50, 30);  // вырежется изображение размером 50x30 в центре
     * </code>
     *
     * @param mixed $left   Координаты левого ограничения.
     * @param mixed $top    Координаты верхнего ограничения.
     * @param mixed $width  Ширина вырезаемой области.
     * @param mixed $height Высота вырезаемой области.
     *
     * @return ImageCommon Вырезанная область изображения.
     *
     * @throws CException
     */
    public function crop($left = 0, $top = 0, $width = '100%', $height = '100%')
    {
        $coordinates = new Coordinates();
        $width       = $coordinates->conversion($width, $this->getWidth(), $width);
        $height      = $coordinates->conversion($height, $this->getHeight(), $height);
        $left        = $coordinates->conversion($left, $this->getWidth(), $width);
        $top         = $coordinates->conversion($top, $this->getHeight(), $height);
        if ($left < 0) {
            $width = $left + $width;
            $left  = 0;
        }
        if ($width > $this->getWidth() - $left) {
            $width = $this->getWidth() - $left;
        }
        if ($top < 0) {
            $height = $top + $height;
            $top    = 0;
        }
        if ($height > $this->getHeight() - $top) {
            $height = $this->getHeight() - $top;
        }
        if ($width <= 0 || $height <= 0) {
            throw new CException('fail ' . __METHOD__);
        }

        /* @var ImagePlain|ImageTrueColor $new */
        $new = $this->create($width, $height);
        if ($this->isTransparent() || $this instanceof ImagePlain) {
            $new->copyTransparencyFrom($this);
            imagecopyresized(
                $new->getHandle(),
                $this->getHandle(),
                0,
                0,
                $left,
                $top,
                $width,
                $height,
                $width,
                $height
            );
        } else {
            $new->alphaBlending(false);
            $new->saveAlpha(true);
            imagecopyresampled(
                $new->getHandle(),
                $this->getHandle(),
                0,
                0,
                $left,
                $top,
                $width,
                $height,
                $width,
                $height
            );
        }

        return $new;
    }

    /**
     * Вставка водяного знака в изображение.
     *
     * <code>
     * $watermark = Image->load('logo.gif');
     * $base = Image->load('picture.jpg');
     * $result = $base->watermark($watermark, 'right - 10', 'bottom - 10', 50);
     * // вставит водяной знак в правом нижнем углу с отсупом в 10 пикселей и
     * // прозрачностью 50%
     * </code>
     *
     * @param ImageCommon $watermark Водяной знак.
     * @param mixed       $left      Позиция слева.
     * @param mixed       $top       позиция сверху.
     * @param integer     $opacity   Прозрачность водяного знака.
     *
     * @return ImageCommon $result Изображение с водяным знаком.
     *
     * @throws CException
     */
    public function watermark($watermark, $left = 0, $top = 0, $opacity = 100)
    {
        $coordinates = new Coordinates();
        $x           = $coordinates->conversion($left, $this->getWidth(), $watermark->getWidth());
        $y           = $coordinates->conversion($top, $this->getHeight(), $watermark->getHeight());
        $result      = $this->asTrueColor();
        $result->alphaBlending(true);
        $result->saveAlpha(true);
        if ($opacity <= 0) {
            return $result;
        }
        if ($opacity < 100) {
            if (!imagecopymerge(
                $result->getHandle(),
                $watermark->getHandle(),
                $x,
                $y,
                0,
                0,
                $watermark->getWidth(),
                $watermark->getHeight(),
                $opacity
            )
            ) {
                throw new CException('fail ' . __METHOD__);
            }
        } else {
            if (!imagecopy(
                $result->getHandle(),
                $watermark->getHandle(),
                $x,
                $y,
                0,
                0,
                $watermark->getWidth(),
                $watermark->getHeight()
            )
            ) {
                throw new CException('fail ' . __METHOD__);
            }
        }

        return $result;
    }

    /**
     * Сохранение изображения в файл.
     *
     * Если тип сохраняемого изображения GIF8, "truecolor" изображения
     * преобразовываются в простые (palette).
     *
     * Поддерживается изменение качества изображения и сжатие.
     *
     * @param string $file Имя файла.
     *
     * @return object Обработчик типа файла.
     *
     * @see http://www.php.net/imagejpeg, http://www.php.net/imagepng
     */
    public function save($file)
    {
        $guess = new Guess;
        $type  = $guess->guessType($file);
        $args  = func_get_args();
        array_unshift($args, $this->getHandle());

        return call_user_func_array(array($type, 'save'), $args);
    }

    /**
     * Прозрачность изображения.
     *
     * @return bool
     */
    protected function isTransparent()
    {
        return $this->getTransparentColor() >= 0;
    }

    /**
     * Определение цвета прозрачности.
     *
     * @return integer Индекс цвета.
     *
     * @see imagecolortransparent
     */
    protected function getTransparentColor()
    {
        return imagecolortransparent($this->handle);
    }

    /**
     * Установка цвета прозрачности.
     *
     * @param integer $color Индекс цвета.
     *
     * @see imagecolortransparent
     */
    protected function setTransparentColor($color)
    {
        return imagecolortransparent($this->handle, $color);
    }

    /**
     * Ширина изображения.
     *
     * @return integer Ширина.
     *
     * @see imagesx
     */
    public function getWidth()
    {
        return imagesx($this->handle);
    }

    /**
     * Высота изображения.
     *
     * @return integer Высота.
     *
     * @see imagesy
     */
    public function getHeight()
    {
        return imagesy($this->handle);
    }

    /**
     * Копирование информации о прозрачности.
     *
     * @param object $sourceImage Изображение-источник.
     * @param bool   $fill        Заполнение изображения цветом прозрачности.
     */
    protected function copyTransparencyFrom($sourceImage, $fill = true)
    {
        /* @var ImageTrueColor $this */
        if ($sourceImage->isTransparent()) {
            $rgba = $sourceImage->getTransparentColorRGB();
            if ($rgba !== null) {
                if ($this->isTrueColor()) {
                    $rgba['alpha'] = 127;
                    $color         = $this->allocateColorAlpha($rgba);
                } else {
                    $color = $this->allocateColor($rgba);
                }
                $this->setTransparentColor($color);
                /*if ($fill) {
                    $this->fill(0, 0, $color);
                }*/
            }
        }
    }

    /**
     * Копирование одного изображения в другое.
     *
     * @param ImageCommon $dest Изображение-назначение.
     * @param integer     $left Координаты левой границы.
     * @param integer     $top  Координаты правой границы.
     *
     * @throws CException
     */
    protected function copyTo($dest, $left = 0, $top = 0)
    {
        if (!imagecopy($dest->getHandle(), $this->handle, $left, $top, 0, 0, $this->getWidth(), $this->getHeight())) {
            throw new CException('fail ' . __METHOD__);
        }
    }

    /**
     * Цвет RGB прозрачности.
     *
     * @return mixed Цвет.
     */
    protected function getTransparentColorRGB()
    {
        $total = imagecolorstotal($this->handle);
        $tc    = $this->getTransparentColor();

        return $tc >= $total && $total > 0
            ? null
            : $this->getColorRGB($tc);
    }

    /**
     * Returns the GD image resource
     *
     * @return resource GD image resource
     */
    protected function getHandle()
    {
        return $this->handle;
    }

    /**
     * Установка цвета RGB значениями.
     *
     * @param mixed $r Только красный цвет или полный ассоциативный массив цветов
     *                 array('red' => ..., 'green' => ..., 'blue' => ...).
     * @param integer   $g Значение зеленого цвета.
     * @param integer   $b Значение синего цвета.
     *
     * @return integer Индекс цвета.
     *
     * @see imagecolorallocate
     */
    protected function allocateColor($r, $g = null, $b = null)
    {
        return (is_array($r))
            ? imagecolorallocate($this->handle, $r['red'], $r['green'], $r['blue'])
            : imagecolorallocate($this->handle, $r, $g, $b);
    }

    /**
     * Цвет RGB.
     *
     * @param integer $colorIndex Индекс цвета.
     *
     * @return array RGB массив цвета.
     *
     * @see imagecolorsforindex
     */
    public function getColorRGB($colorIndex)
    {
        return imagecolorsforindex($this->handle, $colorIndex);
    }
}
