<?php
 /**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico 2017
 */

/* @var $this Mage_Catalog_Model_Resource_Setup */
$this->startSetup();

$connection = $this->getConnection();
$tableName = $this->getTable('emico_attributelanding/page');
if (!$connection->isTableExists($tableName)) {
    $tableDdl = $connection->newTable($tableName)
        ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
            'identity' => true,
        ], 'Page ID')
        ->addColumn('active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ], 'Whether the page is active')
        ->addColumn('url_path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [
            'nullable' => false,
            'default' => '',
        ], 'URL Path')
        ->addIndex('url_path', ['url_path'])
        ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
        ], 'Category ID')
        ->addForeignKey($this->getFkName('emico_attributelanding/page', 'category_id', 'catalog/category', 'entity_id'),
            'category_id', $this->getTable('catalog/category'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [
            'nullable' => true,
            'default' => null,
        ], 'Title')
        ->addColumn('meta_keywords', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
            'nullable'  => true,
        ], 'Page Meta Keywords')
        ->addColumn('meta_description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
            'nullable'  => true,
        ], 'Page Meta Description')
        ->addColumn('short_description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
            'nullable' => true,
        ], 'Short description')
        ->addColumn('long_description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [
            'nullable' => true,
        ], 'Long description')
        ->addColumn('search_attributes', Varien_Db_Ddl_Table::TYPE_TEXT, null, [
            'nullable' => true,
        ], 'Search attributes')
        ->addColumn('hide_selected_filter_group', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '1',
        ], 'Hide selected filters in the frontend')
        ->addColumn('tweakwise_template', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
            'unsigned'  => true,
            'nullable'  => true,
            'default'   => null,
        ], 'Tweakwise template ID')
        ->setComment('Custom landing pages with preselected attributes');

    $connection->createTable($tableDdl);
}

$this->endSetup();