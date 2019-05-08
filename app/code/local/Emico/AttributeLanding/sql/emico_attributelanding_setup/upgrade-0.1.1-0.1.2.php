<?php
/**
 * @author Robert Fokken <rfokken@emico.nl>
 * @copyright (c) Emico 2018
 */

/* @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$connection = $this->getConnection();
$tableReference = 'emico_attributelanding/page_store';
$tableName = $this->getTable($tableReference);
if (!$connection->isTableExists($tableName)) {
    $tableDdl = $connection
        ->newTable($tableName)
        ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Attribute Page ID')
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ), 'Store ID')
        ->addIndex($this->getIdxName($tableReference, array('store_id')),
            array('store_id'))
        ->addForeignKey($this->getFkName($tableReference, 'page_id', 'emico_attributelanding_page', 'page_id'),
            'page_id', $this->getTable('emico_attributelanding_page'), 'page_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($this->getFkName($tableReference, 'store_id', 'core/store', 'store_id'),
            'store_id', $this->getTable('core/store'), 'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('Attribute page To Store Linkage Table');

    $connection->createTable($tableDdl);
}

$this->endSetup();