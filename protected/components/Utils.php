<?php

/**
 * Различные функции.
 *
 * @package    converter
 * @subpackage utils
 * @author     rudnik nnrudakov@gmail.com
 * @copyright  2014
 */
class Utils
{
    /**
     * Транслитерация русского текста в английский.
     *
     * ГОСТ 7.79-2000 (ИСО 9-95) - правила транслитерации кирилловского письма
     * латинским алфавитом.
     *
     * Реализована наиболее употребляемая система Б. Отсутпление от стандарта:
     * <ul>
     *  <li>только для русского языка;</li>
     *  <li>в полной мере не учитывается п. 6.3. Если следующая за транслитерируемой
     * буквой заглавная (прописная), то и текущее буквосочетание пишется заглавными
     * (прописными) буквами;</li>
     *  <li>в виду маловероятного использования, не реализован п. 6.4;</li>
     *  <li>нет анализа употребления буквы Ц (п. 6.6).</li>
     * </ul>
     *
     * @param string $text Текст.
     *
     * @return string $text Транслитерированный текст.
     */
    public static function rus2lat($text)
    {
        $tr = [
            'а' => 'a',  'б' => 'b',   'в' => 'v',  'г' => 'g',  'д' => 'd',
            'е' => 'e',  'ё' => 'yo',  'ж' => 'zh', 'з' => 'z',  'и' => 'i',
            'й' => 'j',  'к' => 'k',   'л' => 'l',  'м' => 'm',  'н' => 'n',
            'о' => 'o',  'п' => 'p',   'р' => 'r',  'с' => 's',  'т' => 't',
            'у' => 'u',  'ф' => 'f',   'х' => 'kh', 'ц' => 'c',  'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'shh', 'ъ' => '``', 'ы' => 'y`', 'ь' => '`',
            'э' => 'e`', 'ю' => 'yu',  'я' => 'ya',
            'А' => 'A',  'Б' => 'B',   'В' => 'V',  'Г' => 'G',  'Д' => 'D',
            'Е' => 'E',  'Ё' => 'Yo',  'Ж' => 'Zh', 'З' => 'Z',  'И' => 'I',
            'Й' => 'J',  'К' => 'K',   'Л' => 'L',  'М' => 'M',  'Н' => 'N',
            'О' => 'O',  'П' => 'P',   'Р' => 'R',  'С' => 'S',  'Т' => 'T',
            'У' => 'U',  'Ф' => 'F',   'Х' => 'Kh', 'Ц' => 'C',  'Ч' => 'Ch',
            'Ш' => 'Sh', 'Щ' => 'Shh', 'Ъ' => '``', 'Ы' => 'Y`', 'Ь' => '`',
            'Э' => 'E`', 'Ю' => 'Yu',  'Я' => 'Ya'
        ];
        $pattern = [
            '/Ж(?=[А-Я])/us', '/Х(?=[А-Я])/us', '/Ч(?=[А-Я])/us', '/Ш(?=[А-Я])/us',
            '/Щ(?=[А-Я])/us', '/Ю(?=[А-Я])/us', '/Я(?=[А-Я])/us'
        ];
        $replacement = [
            'ZH', 'KH', 'CH', 'SH', 'SHH', 'YU', 'YA'
        ];
        $text = preg_replace($pattern, $replacement, $text);

        return strtr($text, $tr);
    }

    /**
     * Приведение к нижнему регистру и удаление неалвафитных знаков.
     *
     * @param string $string   Строка.
     * @param bool   $translit Транслитерировать строку.
     *
     * @return string Строка.
     */
    public static function nameString($string, $translit = true)
    {
        if ($translit) {
            $string = self::rus2lat($string);
        }

        $string = preg_replace('/[^\w\d\s]*/', '', $string);
        $string = strtolower($string);
        $string = str_replace(' ', '-', $string);
        $string = substr($string, 0, 50);

        return $string;
    }

    /**
     * Очистка текста от лишних тегов.
     *
     * @param string $string
     *
     * @return string
     */
    public static function clearText($string)
    {
        $string = strip_tags($string, '<p><a><table><tbody><tr><th><td><ul><li>');
        $string = preg_replace('/<(p|table|th|tbody|tr|td|ul|li)\s.+?>/', '<$1>', $string);
        $string = str_replace(
            ['<td><p>', '</p></td>', '<(td)>-\s+(\d+)</\1>', '<p>&nbsp;</p>'],
            ['<td>', '</td>', '<$1>-$2</$1>', ''],
            $string
        );
        preg_replace('/<p>\s*<\/p>/', '', $string);

        return $string;
    }

    /**
     * Создаёт папку со всеми необходимыми родителями.
     *
     * @param string  $dir  Имя создаваемой папки.
     * @param integer $mode Права доступа к директории.
     *
     * @return bool
     */
    public static function makeDir($dir, $mode = 0775)
    {
        if (is_null($dir) || $dir === '') {
            return false;
        }

        if (is_dir($dir) || $dir === '/') {
            return true;
        }

        $oldumask = umask(0);
        $res = mkdir($dir, $mode, true);
        umask($oldumask);

        return $res;
    }
}
