<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

$installer = $this;
$installer->startSetup();

$table = $installer->getTable('sales/order_payment');
$installer->getConnection()
    ->addColumn($table, 'cybersourcesop_auth_xid', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'comment' => 'adrenalin_tracking_id'));
$installer->getConnection()
    ->addColumn($table, 'cybersourcesop_proof_xml', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'comment' => 'cybersourcesop_proof_xml'));
$installer->getConnection()
    ->addColumn($table, 'cybersourcesop_eci', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'comment' => 'cybersourcesop_eci'));
$installer->getConnection()
    ->addColumn($table, 'cybersourcesop_cavv', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'comment' => 'cybersourcesop_cavv'));
$installer->getConnection()
    ->addColumn($table, 'cybersourcesop_save_token', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'comment' => 'Should Customer token be saved'));

$table = $installer->getTable('sales/quote_payment');
$installer->getConnection()
    ->addColumn($table, 'cybersourcesop_save_token', array('type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'comment' => 'Should Customer token be saved'));

$table = $installer->getTable('cybersourcesop/token');
/** @var $ddlTable Varien_Db_Ddl_Table */
$ddlTable = $installer->getConnection()->newTable($table);
$ddlTable->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'primary'  => true,
    'identity' => true,
    'unsigned' => true,
    'nullable' => false,
), 'Primary Key ID')
    ->addColumn('token_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false),'Token ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false),'Customer ID')
    ->addColumn('cc_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
        'nullable' => false,), 'Credit Card Type')
    ->addColumn('cc_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 30, array(
    'nullable' => false,), 'Credit Card Number')
    ->addColumn('cc_expiration', Varien_Db_Ddl_Table::TYPE_VARCHAR, 10, array(
        'nullable' => false,), 'Credit Card Expiration Date')
    ->addColumn('is_default', Varien_Db_Ddl_Table::TYPE_INTEGER, 255, array(
            'nullable' => true,), 'Is Default')
    ->addColumn('merchant_ref', Varien_Db_Ddl_Table::TYPE_INTEGER, 255, array(
            'nullable' => true,), 'Merchant Reference')
    ->setComment('CybersourceSOP Token Table');
$installer->getConnection()->createTable($ddlTable);

// Required tables to add new order status
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

// Check if status already exist
$rows = $this->getConnection()->fetchAll($this->getConnection()->select()->from($statusTable)->where('status=?','pending_decision_review'));

if(count($rows) === 0){
    // Insert order status and map to order state
    $installer->run("
        INSERT INTO `{$statusTable}` (`status`, `label`) VALUES ('pending_decision_review', 'Pending Decision Manager Review');
        INSERT INTO `{$statusStateTable}` (`status`, `state`, `is_default`) VALUES ('pending_decision_review', 'payment_review', '0');
    ");
} else {
    // Update order status and update map to order state
    $installer->run("
        UPDATE `{$statusTable}` SET label='Pending Decision Manager Review' WHERE status='pending_decision_review';
        UPDATE `{$statusStateTable}` SET state='payment_review' WHERE status='pending_decision_review'
    ");
}

$installer->endSetup();
