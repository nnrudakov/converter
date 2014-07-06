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
     * Инициализация.
     */
    public function __construct()
    {
        $this->files = Files::model();

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
        //echo "$name\n";
        $module = $this->modules[$link->module_id];
        //print_r($link->getAttributes());
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
        Utils::makeDir($dst_dir);
        copy($srcFile, $dst_dir . $name);

        $file->path = $path;
        $file->name = $name;
        //$file->save(false);

        $this->doneFiles++;
        $this->progress();
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneFiles);
    }
}
