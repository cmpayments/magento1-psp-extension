<?php
$this->startSetup();

$connection = $this->connection;

/*******************************************************************************************/
/************* Start with basic check to see if requirements are met ***********************/
/*******************************************************************************************/
$missingRequirements;
if (! Mage::helper('cmpayments/config')->checkPluginRequirements($missingRequirements)) {
    //requirements not met, display admin notification
    $errMsg = addslashes(
        'Failed to activate CM Payments module. The following libraries/mods are missing: \'' . implode(
            "','",
            $missingRequirements
        ) . '\''
    );

    $sql = "INSERT INTO `" . $this->adminNotifyTable . "` (`severity`, `title`, `description`) ";
    $sql .= "VALUES (1, 'CM Payments module: Requirements not met!', '" . $errMsg . "');";

    $this->run($sql);
}

/*******************************************************************************************/
/************* Begin Adding columns for payment method tracking ****************************/
/*******************************************************************************************/
//add required columns on order and payment table for CM id's
$connection->addColumn($this->orderTable, 'cmpayments_charge_id', 'varchar(64) NULL');
$connection->addColumn($this->orderPaymentTable, 'cmpayments_payment_id', 'varchar(64) NULL');
$connection->addColumn($this->orderPaymentTable, 'cmpayments_refund_id', 'varchar(64) NULL');
$connection->addColumn($this->orderGridTable, 'cmpayments_charge_id', 'varchar(64) NULL');

/*******************************************************************************************/
/************* Begin Adding columns for additional fee ****************************/
/*******************************************************************************************/
$tables = array(
    $this->orderTable,
    $this->quoteTable,
    $this->invoiceTable,
);
foreach ($tables as $table) {
    $connection->addColumn($table, 'cmpayments_fee_tax_amount', 'decimal(12,4) NULL');
    $connection->addColumn($table, 'cmpayments_fee_amount', 'decimal(12,4) NULL');
}

/*******************************************************************************************/
/************* Add locking table if not present ********************************************/
/*******************************************************************************************/
if (! $connection->isTableExists($this->lockTable)) {
    $table = $this->connection->newTable($this->lockTable);
    $table->addColumn(
        'lock_code', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => false,
        'primary'  => true,
        ), 'Lock Code'
    );
    $table->addColumn(
        'process_code', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable' => true,
        'default'  => null,
        ), 'Process Code'
    );
    $table->addIndex('process_code', array('process_code'));
    $table->addColumn(
        'lock_time', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        ), 'Lock Time'
    );
    $table->addIndex('lock_time', array('lock_time'));

    $table->setComment('For locking CM Payments transactions');
    $connection->createTable($table);
}

$this->endSetup();

Mage::helper('cmpayments')->log("CM Payments install 0.0.1 was completed. ");