<?php
/**
 * @author Robert Fokken <rfokken@emico.nl>
 * @copyright (c) Emico 2018
 */

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$connection = $installer->getConnection();
$table = $installer->getTable('emico_attributelanding/page');
if ($connection->isTableExists($table)) {

    if (!$connection->tableColumnExists($table, 'custom_layout_handle')) {
        $connection->addColumn($table, 'custom_layout_handle', [
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Custom layout handle',
        ]);
    }
}

$installer->endSetup();