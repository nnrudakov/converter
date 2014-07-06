<?php

/**
 * Угадывание формата изображения.
 *
 * @package    SamoletCMS
 * @subpackage image
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2010-2014
 */
class Guess
{
    /**
     * Соответствие расширеший MIME типам.
     *
     * @var array
     */
    protected $mimeTable = array(
        'image/jpg'   => 'jpg',
        'image/jpeg'  => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/gif'   => 'gif',
        'image/png'   => 'png'
    );

    /**
     * Угадывание формата файла.
     *
     * @param string $file Имя файла или URL.
     *
     * @return mixed Объект угаданного изображения.
     *
     * @throws CException Выбрасывается в случае, если формат не поддерживается.
     */
    public function guessType($file)
    {
        $type = $this->determineType($file);

        return new TypeHandler($type);
    }

    private function determineType($file)
    {
        $type = strrpos($file, '.');
        $type = $type === false ? '' : substr($file, $type + 1);

        // есть ли mime-type
        if (preg_match('~[a-z]*/[a-z-]*~i', $type)) {
            if (isset($this->mimeTable[strtolower($type)])) {
                return $this->mimeTable[strtolower($type)];
            }
        }

        // убираем лишнее
        $type = strtolower(preg_replace('/[^a-z0-9_-]/i', '', $type));

        if ($type == 'jpeg') {
            $type = 'jpg';
        }

        return $type;
    }
}
