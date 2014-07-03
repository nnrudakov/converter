<?php

/**
 * Управление общеязыковыми идентификаторами.
 *
 * @package    converter
 * @subpackage multilang
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
trait TMultilang {
    /**
     * Создание общеязыкового идентификатора.
     */
    protected function setMultilang()
    {
        /* @var DestinationModel $this */
        $const_entity = get_class($this) . '::ENTITY';

        if (!defined($const_entity) || !$this->module) {
            return false;
        }

        if (!$this->multilangId) {
            $multilang = new CoreMultilang();
            $multilang->module_id = $this->module->module_id;
            $multilang->entity    = constant($const_entity);
            $multilang->import_id = $this->importId;
            $multilang->save();
            $this->multilangId = (int) $multilang->id;
        }

        $multilang_link = new CoreMultilangLink();
        $multilang_link->multilang_id = $this->multilangId;
        $multilang_link->entity_id    = $this->getId();
        $multilang_link->lang_id      = $this->lang;
        $multilang_link->save();

        return true;
    }
}
