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
        $const_entity = get_class($this) . '::ENTITY';
        $const_lang   = get_class($this) . '::LANG';

        if (!defined($const_entity) || !defined($const_lang) || !$this->module) {
            return false;
        }

        $multilang = new CoreMultilang();
        $multilang->module_id = $this->module->module_id;
        $multilang->entity    = constant($const_entity);
        $multilang->save();

        $multilang_link = new CoreMultilangLink();
        $multilang_link->multilang_id = $multilang->id;
        $multilang_link->entity_id    = $this->getId();
        $multilang_link->lang_id      = constant($const_lang);
        $multilang_link->save();

        return true;
    }
}
