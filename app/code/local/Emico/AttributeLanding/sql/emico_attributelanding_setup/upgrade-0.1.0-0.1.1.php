<?php
 /**
 * @author Frank Kruidhof <fkruidhof@emico.nl>
 * @copyright (c) Emico 2017
 */

/* @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$connection = $this->getConnection();
$tableName = $this->getTable('emico_attributelanding/page');
$columnName = 'header_image';

if ($connection->isTableExists($tableName) && !$connection->tableColumnExists($tableName, $columnName))
{
    $connection->addColumn($tableName, $columnName, array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'default' => null,
        'comment' => 'Image for header',
        'length'  => 255
        )
    );
}

$this->endSetup();