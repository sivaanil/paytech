<?php

$installer = $this;
$installer->getConnection()->addColumn($this->getTable('bannerslider/bannerslider'), 'titlestatus', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length' => 1,
    'nullable' => false,
    'default' => 1,
    'comment' => 'Some comment here'
));
