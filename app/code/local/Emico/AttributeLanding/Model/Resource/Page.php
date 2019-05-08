<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */ 
class Emico_AttributeLanding_Model_Resource_Page extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('emico_attributelanding/page', 'page_id');
    }

    /**
     * @param Emico_AttributeLanding_Model_Page $object
     * @param string $urlKey
     * @return $this
     */
    public function loadByUrl(Emico_AttributeLanding_Model_Page $object, $urlKey)
    {
        $this->load($object, $urlKey, 'url_path');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _unserializeField(Varien_Object $object, $field, $defaultValue = null)
    {
        if ($field == 'search_attributes') {
            return json_decode($defaultValue, true);
        }

        parent::_unserializeField($object, $field, $defaultValue);
    }

    /**
     * {@inheritdoc}
     */
    protected function _serializeField(Varien_Object $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        if ($field === 'search_attributes') {
            return;
        }
        return parent::_prepareTableValueForSave($field, $defaultValue);
    }

    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Emico_AttributeLanding_Model_Resource_Page
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());

            $object->setData('store_id', $stores);

        }

        return parent::_afterLoad($object);
    }

    /**
     * Assign page to store views
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Emico_AttributeLanding_Model_Resource_Page
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('emico_attributelanding/page_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array(
                'page_id = ?'     => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = [
                    'page_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                ];
            }

            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        //Mark layout cache as invalidated
        Mage::app()->getCacheInstance()->invalidateType('layout');

        return parent::_afterSave($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($pageId)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('emico_attributelanding/page_store'), 'store_id')
            ->where('page_id = ?',(int)$pageId);

        return $adapter->fetchCol($select);
    }
}