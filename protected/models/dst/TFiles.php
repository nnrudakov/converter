<?php

/**
 * Сохранение файлов сущности
 *
 * @package    converter
 * @subpackage files
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
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

        foreach ($this->fileParams as $params) {
            $name = isset($params['name']) ? $params['name'] : constant($const_file);
            $field_id = isset($params['field_id']) ? $params['field_id'] : constant($const_field);

            $file = new Files();
            $file->ext   = substr($name, -3);
            $file->name  = sprintf($name, $params['old_id']);
            $file->descr = $params['descr'];

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
        }

        $this->fileParams = [];
    }
}
