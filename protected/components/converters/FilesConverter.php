<?php

/**
 * Конвертер файлов.
 *
 * @package    converter
 * @subpackage contracts
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FilesConverter implements IConverter
{
    /**
     * @va string
     */
    const SRC_NEWS_PHOTO_DIR = '/var/www/html/media.fckrasnodar.ru/www/res/galleries';

    /**
     * @va string
     */
    const SRC_PLAYERS_TEAMS_DIR = '/var/www/html/media.fckrasnodar.ru/krasnodar/www/app/mods/tsi/res/images';

    /**
     * @va string
     */
    const SRC_PERSONS_NEWS_DIR = '/var/www/html/media.fckrasnodar.ru/krasnodar/www/app/mods/info_store/res/images';

    /**
     * @va string
     */
    const DST_DIR = '/var/www/html/media.fckrasnodar.ru/';

    /**
     * @var string
     */
    const CRASH_SRC_FILE = '/var/www/html/media.fckrasnodar.ru/crash_src_file';

    /**
     * @var string
     */
    const CRASH_DST_FILE = '/var/www/html/media.fckrasnodar.ru/crash_dst_file';

    /**
     * @var string
     */
    const WATERMARK = '/var/www/html/media.fckrasnodar.ru/c222623cea46a660e0466dd9a47629cd.png';

    /**
     * @var Image
     */
    private $watermark = null;

    /**
     * @var array
     */
    private static $watermarkSettings = [
        'news' => [
            ['width' => 120, 'height' => 80,   'crop' => 'center'],
            ['width' => 180, 'height' => 120,  'crop' => 'center'],
            ['width' => 620, 'height' => 0,    'crop' => 'proportionally']
        ]
    ];

    /**
     * @var array
     */
    private static $quality = ['jpg' => 95 , 'png' => 8];

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rFiles: %d.";

    /**
     * @var integer
     */
    private $doneFiles = 0;

    /**
     * @var Files
     */
    private $files = null;

    /**
     * @var CoreModules[]
     */
    private $modules = null;

    /**
     * @var Image
     */
    private $image = null;

    /**
     * Инициализация.
     */
    public function __construct()
    {
        $this->files = Files::model();
        $this->image = new Image();
        $this->watermark = $this->image->load(self::WATERMARK);

        foreach (CoreModules::model()->findAll() as $module) {
            $this->modules[$module->module_id] = $module;
        }
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
        $this->chooseFiles(self::SRC_PERSONS_NEWS_DIR);
        $this->chooseFiles(self::SRC_PLAYERS_TEAMS_DIR);
        $this->chooseFiles(self::SRC_NEWS_PHOTO_DIR);
    }

    private function chooseFiles($dir)
    {
        if ($dh = opendir($dir)) {
            while (false !== ($dir_name = readdir($dh))) {
                if ('.' != $dir_name && '..' != $dir_name) {
                    $dir_name = $dir . DIRECTORY_SEPARATOR . $dir_name;
                    if (is_dir($dir_name)) {
                        $this->chooseFiles($dir_name);
                    } else {
                        $name = preg_replace('/.+?\//', '', $dir_name);
                        $criteria = new CDbCriteria();
                        $criteria->addSearchCondition('name', $name);
                        $file = $this->files->find($criteria);

                        if ($file && ($links = $file->links)) {
                            /* @var FilesLink $link */
                            $link = array_shift($links);
                            $this->moveFile($file, $link, $dir_name);
                        }
                    }
                }
            }

            closedir($dh);
        }
    }

    /**
     * @param Files     $file
     * @param FilesLink $link
     * @param string    $srcFile
     *
     * @return bool
     */
    private function moveFile($file, $link, $srcFile)
    {
        $name = preg_replace('/.+?\//', '', $file->name);
        $module = $this->modules[$link->module_id];
        $path = $module->name . '/';

        switch ($module->name) {
            case 'news':
                $path .= NewsObjects::ENTITY . '/';
                break;
            case 'fc':
                if (false !== strpos($name, 'players')) {
                    $path .= FcPerson::ENTITY . '/';
                } elseif (false !== strpos($name, 'teams')) {
                    $path .= FcTeams::ENTITY . '/';
                } else {
                    $path = '';
                }
                break;
            case 'persons':
                $path .= PersonsObjects::ENTITY . '/';

                break;
            default:
                $path = '';
                break;
        }

        if (!$path) {
            return false;
        }

        $path .= $link->object_id . '/';
        $dst_dir = self::DST_DIR . $path;
        $dst_file = $dst_dir . $name;
        Utils::makeDir($dst_dir);
        //file_put_contents(self::CRASH_SRC_FILE, $srcFile);

        if (!file_exists($dst_file) || filesize($srcFile) != filesize($dst_file)) {
            copy($srcFile, $dst_file);
        }

        $file->path = $path;
        $file->name = $name;
        $thumbs = $this->makeThumbs($file, $dst_dir, $module->name);
        $file->save(false);

        $this->doneFiles++;
        $this->progress();
    }

    /**
     * @param Files  $file
     * @param string $dirname
     * @param string $moduleName
     *
     * @return bool
     */
    private function makeThumbs($file, $dirname, $moduleName)
    {
        if ('mp4' == $file->ext) {
            return false;
        }
        //file_put_contents(self::CRASH_DST_FILE, $dirname . $file->name);
        $base_image = $this->image->load($dirname . $file->name);
        // превью для админки
        $admin_thumb = $dirname . substr($file->name, 0, -strlen('.' . $file->ext)) . '_admin.' . $file->ext;

        if (!file_exists($admin_thumb)) {
            $base_image->resize(100, 100)->save($admin_thumb);
        }

        if (isset(self::$watermarkSettings[$moduleName])) {
            $i = 1;
            foreach (self::$watermarkSettings[$moduleName] as $settings) {
                $thumb_name = substr($file->name, 0, -strlen('.' . $file->ext)) .
                    '_t' . $i . '.' . $file->ext;
                if (!file_exists($admin_thumb)) {
                    if ('proportionally' == $settings['crop']) {
                        if (!$settings['width']) {
                            $settings['width'] = null;
                        }
                        if (!$settings['height']) {
                            $settings['height'] = null;
                        }
                        $thumb = $base_image->resize($settings['width'], $settings['height'], 'height', 'down');
                    } else {
                        $iw = $base_image->getWidth();
                        $ih = $base_image->getHeight();
                        if (!$settings['width']) {
                            $settings['width'] = $iw;
                        }
                        if (!$settings['height']) {
                            $settings['height'] = $ih;
                        }
                        // ресайз до конечных размеров по ширине
                        $resized_image = $base_image->resize($settings['width'], null, 'exact');
                        /*
                         * если после ресайза какая-либо из сторон меньше той, что
                         * должна получится, меняем направление ресайза оригинала
                         */
                        if ($resized_image->getWidth() < $settings['width'] ||
                            $resized_image->getHeight() < $settings['height']) {
                            $resized_image = $base_image->resize(null, $settings['height'], 'exact');
                        }

                        if ('center' == $settings['crop']) {
                            $left = $top = 'center';
                        } else {
                            $left = 'center';
                            $top  = 'top';
                        }

                        $thumb = $resized_image->crop($left, $top, $settings['width'], $settings['height']);
                    }

                    // поставить водяной знак
                    if (isset($settings['watermark'])) {
                        $thumb = $thumb->watermark($this->watermark);
                    }

                    $thumb->save($dirname . $thumb_name, self::$quality[$file->ext]);
                }
                $file->{'thumb' . $i} = $thumb_name;
                $i++;
            }
        }
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneFiles);
    }
}