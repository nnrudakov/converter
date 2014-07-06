<?php

/**
 * Работа с координатами изображения.
 *
 * Трактует различное представление координат (словесное, процентное) в числовое.
 *
 * @package    SamoletCMS
 * @subpackage image
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2010-2014
 */
final class Coordinates
{
    /**
     * Словесное описание координат.
     *
     * @var array
     */
    private $coord_align = array('left', 'center', 'right', 'top', 'middle', 'bottom');

    /**
     * Числовое представление координат, включая проценты.
     *
     * @var array
     */
    private $coord_numeric = array('[0-9]+', '[0-9]+\.[0-9]+', '[0-9]+%', '[0-9]+\.[0-9]+%');

    /**
     * Преобразование различных представлений координат в числовое.
     *
     * @param ImageCommon $img    Изображение.
     * @param mixed       $width  Ширина.
     * @param mixed       $height Высота.
     * @param string      $fit    . Пропорции.
     *
     * @return array $dim Преобразованные значения.
     *
     * @throws CException
     */
    public function prepareCoordinates($img, $width, $height, $fit)
    {
        list($width, $height) = $this->coordForResize($img, $width, $height);

        if ($width === 0 || $height === 0) {
            return array('width' => 0, 'height' => 0);
        }

        if ($fit == null) {
            $fit = 'height';
        }

        $dim = array();

        if ($fit == 'exact') {
            $dim['width']  = $width;
            $dim['height'] = $height;
        } else {
            if ($fit == 'height' || $fit == 'width') {
                $rx = $img->getWidth() / $width;
                $ry = $img->getHeight() / $height;

                if ($fit == 'height') {
                    $ratio = ($rx > $ry) ? $rx : $ry;
                } else {
                    $ratio = ($rx < $ry) ? $rx : $ry;
                }

                $dim['width']  = round($img->getWidth() / $ratio);
                $dim['height'] = round($img->getHeight() / $ratio);
            } else {
                throw new CException('fail ' . __METHOD__);
            }
        }

        return $dim;
    }

    /**
     * Преобразование описаний координат в числовые значения.
     *
     * @param mixed $value  Описание координаты.
     * @param int   $dim    Значение координаты (привязано к $value).
     * @param int   $secDim Координаты выравнивания.
     *
     * @return int $value Преобразованное значение.
     *
     * @throws CException
     */
    public function conversion($value, $dim, $secDim = null)
    {
        $coord_tokens = $this->parse($value);

        if (count($coord_tokens) == 0 || $coord_tokens[count($coord_tokens) - 1]['type'] != 'operand') {
            throw new CException('fail ' . __METHOD__);
        }

        $value     = 0;
        $operation = 1;

        foreach ($coord_tokens as $token) {
            if ($token['type'] == 'operand') {
                $operand_value = $this->evaluate($token['value'], $dim, $secDim);

                if ($operation == 1) {
                    $value += $operand_value;
                } else {
                    if ($operation == -1) {
                        $value -= $operand_value;
                    } else {
                        throw new CException('fail ' . __METHOD__);
                    }
                }

                $operation = 0;
            } else {
                if ($token['type'] == 'operator') {
                    if ($token['value'] == '-') {
                        if ($operation == 0) {
                            $operation = -1;
                        } else {
                            $operation *= -1;
                        }
                    } else {
                        if ($token['value'] == '+') {
                            if ($operation == 0) {
                                $operation = 1;
                            }
                        }
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Преобразование координат в структуру.
     *
     * @param string $coord Координата.
     *
     * @return array $tokens Преобразованое значение.
     */
    private function parse($coord)
    {
        $tokens    = array();
        $operators = array('+', '-');

        $flush_operand    = false;
        $flush_operator   = false;
        $current_operand  = '';
        $coordinate       = strval($coord);
        $expr_len         = strlen($coordinate);

        for ($i = 0; $i < $expr_len; $i++) {
            $char = $coordinate[$i];

            if (in_array($char, $operators)) {
                $flush_operand    = true;
                $flush_operator   = true;
            } else {
                $current_operand .= $char;
                if ($i == $expr_len - 1) {
                    $flush_operand = true;
                }
            }

            if ($flush_operand) {
                if (trim($current_operand) != '') {
                    $tokens[] = array('type' => 'operand', 'value' => trim($current_operand));
                }

                $current_operand = '';
                $flush_operand   = false;
            }

            if ($flush_operator) {
                $tokens[]       = array('type' => 'operator', 'value' => $char);
                $flush_operator = false;
            }
        }

        return $tokens;
    }

    /**
     * Преобразование словесных значений координат.
     *
     * @param string  $coord  Числовое значение или проценты.
     * @param integer $dim    Размеры.
     * @param integer $secDim Координаты выравнивания.
     *
     * @return int Преобразованное значение.
     */
    private function evaluate($coord, $dim, $secDim = null)
    {
        $comp_regex = implode('|', $this->coord_align) . '|' . implode('|', $this->coord_numeric);

        if (preg_match('/^([+-])?({' . $comp_regex . '})$/', $coord, $matches)) {
            $sign = intval($matches[1] . '1');
            $val  = $matches[2];

            if (in_array($val, $this->coord_align)) {
                if ($secDim === null) {
                    switch ($val) {
                        case 'left':
                        case 'top':
                            return 0;

                            break;
                        case 'center':
                        case 'middle':
                            return $sign * intval($dim / 2);

                            break;
                        case 'right':
                        case 'bottom':
                            return $sign * $dim;

                            break;
                        default:
                            return null;
                    }
                } else {
                    switch ($val) {
                        case 'left':
                        case 'top':
                            return 0;

                            break;
                        case 'center':
                        case 'middle':
                            return $sign * intval($dim / 2 - $secDim / 2);

                            break;
                        case 'right':
                        case 'bottom':
                            return $sign * ($dim - $secDim);

                            break;
                        default:
                            return null;
                    }
                }
            } else {
                if (substr($val, -1) === '%') {
                    return intval(round($sign * $dim * floatval(str_replace('%', '', $val)) / 100));
                } else {
                    return $sign * intval(round($val));
                }
            }
        }

        return 0;
    }

    /**
     * Пропорциональные координаты при изменении размеров изображения.
     *
     * Если при изменении изображения ширина или высота не указаны, находим
     * их значения пропорционально указанному значению. Например, если указана
     * ширина и не указана высота, то значение высоты будет пропорционально
     * указанному значению ширины, и наоборот.
     *
     * @param ImageCommon $img    Изображение.
     * @param int         $width  Ширина.
     * @param int         $height Высота.
     *
     * @return array Ширина и высота.
     */
    private function coordForResize($img, $width, $height)
    {
        if ($width === null && $height === null) {
            return array($img->getWidth(), $img->getHeight());
        }

        if ($width !== null) {
            $width = $this->conversion($width, $img->getWidth());
        }

        if ($height !== null) {
            $height = $this->conversion($height, $img->getHeight());
        }

        if ($width === null) {
            $width = floor($img->getWidth() * $height / $img->getHeight());
        }

        if ($height === null) {
            $height = floor($img->getHeight() * $width / $img->getWidth());
        }

        return array($width, $height);
    }
}
