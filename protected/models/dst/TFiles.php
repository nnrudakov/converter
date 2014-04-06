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
        $name = isset($this->fileParams['name']) ? $this->fileParams['name'] : constant($const_file);
        $field_id = isset($this->fileParams['field_id']) ? $this->fileParams['field_id'] : constant($const_field);

        $file = new Files();
        $file->ext       = substr($name, -3);
        $file->name      = sprintf($name, $this->fileParams['old_id']);
        $file->load_date = date('Y-m-d H:i:s');

        if (!$file->save()) {
            throw new CException('Files not created.' . "\n" . var_export($file->getErrors(), true) . "\n");
        }

        $link = new FilesLink();
        $link->file_id     = $file->file_id;
        $link->module_id   = $this->module->module_id;;
        $link->category_id = 0;
        $link->object_id   = $this->id;
        $link->field_id    = $field_id;
        $link->main        = 1;
        $link->sort        = 1;

        if (!$link->save()) {
            throw new CException('Link not created.' . "\n" . var_export($link->getErrors(), true) . "\n");
        }
    }
}
