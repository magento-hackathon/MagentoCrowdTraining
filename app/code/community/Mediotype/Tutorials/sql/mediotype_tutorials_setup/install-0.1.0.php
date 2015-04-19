<?php
/** @var  $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$tutorialTable = $installer->getConnection()
    ->newTable($installer->getTable('mediotype_tutorials/tutorial'))
    ->addColumn('id',Varien_Db_Ddl_Table::TYPE_INTEGER,null,array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'ID')
    ->addColumn('label',Varien_Db_Ddl_Table::TYPE_VARCHAR,255,array(
        'nullable' => true,
        'default' => '',
    ),'Label')
    ->addColumn('category',Varien_Db_Ddl_Table::TYPE_VARCHAR,null,array(
        'nullable' => true,
        'default' => null,
    ),'Category')
    ->addColumn('description',Varien_Db_Ddl_Table::TYPE_BLOB,null,array(
        'nullable' => true,
        'default' => null,
    ),'Description')
    ->addColumn('keywords',Varien_Db_Ddl_Table::TYPE_VARCHAR,null,array(
        'nullable' => true,
        'default' => null,
    ),'Keywords');

$installer->getConnection()->createTable($tutorialTable);

$pagesTable = $installer->getConnection()
    ->newTable($installer->getTable('mediotype_tutorials/page'))
    ->addColumn('id',Varien_Db_Ddl_Table::TYPE_INTEGER,null,array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'ID')
    ->addColumn('tutorial_id',Varien_Db_Ddl_Table::TYPE_INTEGER,11,array(
        'nullable' => true,
        'default' => null,
    ),'Tutorial ID')
    ->addColumn('url_key',Varien_Db_Ddl_Table::TYPE_VARCHAR,510,array(
        'nullable' => true,
        'default' => null,
    ),'URL KEY')
    ->addColumn('use_expose',Varien_Db_Ddl_Table::TYPE_INTEGER,1,array(
        'nullable' => false,
        'default' => null,
    ),'USE EXPOSE')
    ->addColumn('params',Varien_Db_Ddl_Table::TYPE_VARCHAR,255,array(
        'nullable' => true,
        'default' => null,
    ),'Date Created')
	->addColumn('pre_page_callback',Varien_Db_Ddl_Table::TYPE_TEXT,null,array(
        'nullable' => true,
        'default' => null,
    ),'Pre Page CallBack')
	->addColumn('post_page_callback',Varien_Db_Ddl_Table::TYPE_TEXT,null,array(
        'nullable' => true,
        'default' => null,
    ),'Post Page CallBack')
    ->addForeignKey(
    $installer->getFkName(
        'mediotype_tutorials/tutorial',
        'id',
        'mediotype_tutorials/page',
        'id'
    ),
    'id',
    $installer->getTable('mediotype_tutorials/tutorial'),
    'id',
    Varien_Db_Ddl_Table::ACTION_RESTRICT,
    Varien_Db_Ddl_Table::ACTION_RESTRICT);

$installer->getConnection()->createTable($pagesTable);

$stepsTable = $installer->getConnection()
    ->newTable($installer->getTable('mediotype_tutorials/step'))
    ->addColumn('id',Varien_Db_Ddl_Table::TYPE_INTEGER,null,array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'ID')
    ->addColumn('page_id',Varien_Db_Ddl_Table::TYPE_INTEGER,11,array(
        'nullable' => true,
        'default' => null,
    ),'Page ID')
    ->addColumn('target_element_id',Varien_Db_Ddl_Table::TYPE_VARCHAR,255,array(
        'nullable' => true,
        'default' => null,
    ),'Target Element ID')
    ->addColumn('pre_step_callback',Varien_Db_Ddl_Table::TYPE_BLOB,null,array(
        'nullable' => true,
        'default' => null,
    ),'Pre Step Callback JS')
	->addColumn('post_step_callback',Varien_Db_Ddl_Table::TYPE_BLOB,null,array(
        'nullable' => true,
        'default' => null,
    ),'Post Step Callback JS')
    ->addColumn('step_title',Varien_Db_Ddl_Table::TYPE_VARCHAR,255,array(
        'nullable' => true,
        'default' => null,
    ),'Step Title')
	->addColumn('step_content',Varien_Db_Ddl_Table::TYPE_BLOB,null,array(
        'nullable' => true,
        'default' => null,
    ),'Step Content')
	->addColumn('tip_location',Varien_Db_Ddl_Table::TYPE_VARCHAR,255,array(
        'nullable' => true,
        'default' => 'bottom',
    ),'Tool Tip Location')
    ->addForeignKey(
    $installer->getFkName(
        'mediotype_tutorials/page',
        'id',
        'mediotype_tutorials/step',
        'id'
    ),
    'id',
    $installer->getTable('mediotype_tutorials/page'),
    'id',
    Varien_Db_Ddl_Table::ACTION_RESTRICT,
    Varien_Db_Ddl_Table::ACTION_RESTRICT);

$installer->getConnection()->createTable($pagesTable);

$installer->endSetup();