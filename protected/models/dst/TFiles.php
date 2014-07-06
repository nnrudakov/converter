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
        /* @var DestinationModel $this */
        if (!$this->fileParams) {
            return false;
        }

        $const_file = get_class($this) . '::FILE';
        $const_field = get_class($this) . '::FILE_FIELD';
        $const_entity = get_class($this) . '::ENTITY';
        $const_entity = defined($const_entity) ? constant($const_entity) : '';
        $main = 1;
        $dir = Yii::app()->params['files_dir'];
        $path = $this->module->name . '/' . $const_entity . ($const_entity ? '/' : '');

        foreach ($this->fileParams as $params) {
            $filepath = $path . $this->getId() . '/';
            $name = isset($params['name']) ? $params['name'] : constant($const_file);
            $name = sprintf($name, $params['old_id']);
            $remote_file = $this->getFile($name);

            if (false === $remote_file) {
                continue;
            }

            $name = preg_replace('/.+?\//', '', $name);
            list($size, $content) = $remote_file;
            $attributes = [
                'ext'        => substr($name, -3),
                'path'       => $filepath,
                'name'       => $name,
                'size'       => $size,
                'descr'      => $params['descr'],
                'video_time' => $params['video_time']
            ];

            $file = new Files();
            $file->setAttributes($attributes, false);

            // Если файл такой есть, берем айдишник и создаем только новую связку
            if ($exist_file = Files::model()->findByAttributes($attributes)) {
                $file->file_id = $exist_file->file_id;
            } elseif (!$file->save()) {
                throw new CException('Files not created.' . "\n" . var_export($file->getErrors(), true) . "\n");
            }

            $field_id = isset($params['field_id']) ? $params['field_id'] : constant($const_field);
            $link = new FilesLink();
            $link->file_id     = $file->file_id;
            $link->module_id   = $this->module->module_id;
            $link->category_id = $params['category_id'];
            $link->object_id   = $this->getId();
            $link->field_id    = $field_id;
            $link->main        = $main;
            $link->sort        = $params['sort'];

            if (!$link->save()) {
                throw new CException('Link not created.' . "\n" . var_export($link->getErrors(), true) . "\n");
            }

            if ($main) {
                $main = 0;
            }

            if (!$exist_file) {
                $this->setFile($dir . $filepath . $name, $file->ext, $content);
            }
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

        if (!$this->writeFiles) {
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        $file = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new CException('Curl error: ' . curl_error($ch) . '. File: ' . $url);
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] == 404) {
            return false;
        }

        return [$info['download_content_length'], $file];
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
