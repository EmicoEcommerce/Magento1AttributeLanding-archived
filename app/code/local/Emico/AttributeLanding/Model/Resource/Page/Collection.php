<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */ 
class Emico_AttributeLanding_Model_Resource_Page_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('emico_attributelanding/page');
    }

    /**
     * Filter pages active on current store
     *
     * @return $this
     * @throws Mage_Core_Model_Store_Exception
     */
    public function addCurrentStoreFilter()
    {
        $this->join(
            ['page_store' => 'emico_attributelanding/page_store'],
            'page_store.page_id = main_table.page_id',
            'store_id'
        );
        $this->addFieldToFilter('store_id', ['in' => [0, Mage::app()->getStore()->getId()]]);
        return $this;
    }
}