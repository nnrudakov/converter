<?php

/**
 * Сохранение файлов сущности
 *
 * @package    converter
 * @subpackage files
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
trait TFiles {
    /**
     * Сохранения файла сущности.
     *
     * @throws CException
     */
    protected function saveFile()
    {
        if (!$this->fileParams) {
            return false;
        }

        $const_file = get_class($this) . '::FILE';
        $const_field = get_class($this) . '::FILE_FIELD';
        $main = 1;
        $dir = Yii::app()->params['files_dir'];

        foreach ($this->fileParams as $params) {
            $name = isset($params['name']) ? $params['name'] : constant($const_file);
            $name = sprintf($name, $params['old_id']);
            $remote_file = $this->getFile($name);

            if (false === $remote_file) {
                //echo 'File "' . $name . '" not found.' . "\n";
                continue;
            }

            list($size, $content) = $remote_file;
            $field_id = isset($params['field_id']) ? $params['field_id'] : constant($const_field);

            $file = new Files();
            $file->ext        = substr($name, -3);
            $file->name       = $name;
            $file->size       = $size;
            $file->descr      = $params['descr'];
            $file->video_time = $params['video_time'];

            if (!$file->save()) {
                throw new CException('Files not created.' . "\n" . var_export($file->getErrors(), true) . "\n");
            }

            $link = new FilesLink();
            $link->file_id     = $file->file_id;
            $link->module_id   = $this->module->module_id;
            $link->category_id = $params['category_id'];
            $link->object_id   = $this->id;
            $link->field_id    = $field_id;
            $link->main        = $main;
            $link->sort        = $params['sort'];

            if (!$link->save()) {
                throw new CException('Link not created.' . "\n" . var_export($link->getErrors(), true) . "\n");
            }

            if ($main) {
                $main = 0;
            }

            $this->setFile($dir . $name, $file->ext, $content);
        }

        $this->fileParams = [];
    }

    /**
     * Проверка существования файла на внешнем сервере.
     *
     * @param string $filename
     *
     * @return array|bool Размер файла и содержимое.
     *
     * @throws CException
     */
    protected function getFile($filename)
    {
        $url = $this->filesUrl . $filename;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $file = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new CException('Curl error: ' . curl_error($ch) . '. File: ' . $url);
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] == 404) {
            return false;
        }

        return [$info['size_download'], $file];
    }

    /**
     * Сохранение файла.
     *
     * @param string $filename
     * @param string $ext
     * @param string $content
     *
     * @return bool
     */
    protected function setFile($filename, $ext, $content)
    {
        if (!$this->writeFiles) {
            return false;
        }

        $dirname = dirname($filename);

        if (!file_exists($dirname)) {
            mkdir($dirname, 0775, true);
        }

        file_put_contents($filename, $content);

        // пишем тумбочку для админки если файл не видео
        if ($ext != 'mp4') {
            $admin_name = str_replace('.' . $ext, '', $filename);
            $admin_name .= '_admin.' . $ext;
            file_put_contents($admin_name, $content);
        }

        return true;
    }
}
