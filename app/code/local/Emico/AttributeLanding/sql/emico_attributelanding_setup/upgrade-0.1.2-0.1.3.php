<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico 2018
 */

/* @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$connection = $this->getConnection();
$tableReference = 'emico_attributelanding/page';
$tableName = $this->getTable($tableReference);
if ($connection->isTableExists($tableName))
{
    if (!$connection->tableColumnExists($tableName, 'type')) {
        $connection
            ->addColumn($tableName, 'type', [
                'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'Page type',
                'length' => 255
            ]);
        $connection->addIndex($tableName, $this->getIdxName($tableReference, ['type']), ['type']);
    }

    $tableName = $this->getTable('emico_attributelanding_page_store');
    $this->run(" ALTER TABLE {$tableName} DROP PRIMARY KEY, ADD PRIMARY KEY (`page_id`, `store_id`)");
}

$this->endSetup();