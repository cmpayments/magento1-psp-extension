<?php

/**
 * Setup class for the resources to assist in the updates
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Resource_Setup extends Mage_Sales_Model_Mysql4_Setup
{
    /**
     * Varien_Db_Adapter_Pdo_Mysql connection to the database
     */
    protected $connection;
    /**
     * List of tables to use in installation
     */
    protected $orderTable;
    protected $orderPaymentTable;
    protected $quoteTable;
    protected $orderGridTable;
    protected $invoiceTable;
    protected $quoteAddressTable;
    protected $orderAddressTable;
    protected $adminNotifyTable;
    protected $lockTable;

    /**
     * Creates Comaxx_CmPayments_Resource_Setup object
     *
     * @param string $resourceName the setup resource name
     *
     * @return Comaxx_CmPayments_Resource_Setup
     */
    public function __construct($resourceName)
    {
        parent::__construct($resourceName);

        $this->connection        = $this->getConnection();
        $this->adminNotifyTable  = $this->getTable('adminnotification/inbox');
        $this->orderTable        = $this->getTable('sales/order');
        $this->orderPaymentTable = $this->getTable('sales/order_payment');
        $this->quoteTable        = $this->getTable('sales/quote');
        $this->invoiceTable      = $this->getTable('sales/invoice');
        $this->orderGridTable    = $this->getTable('sales/order_grid');
        $this->quoteAddressTable = $this->getTable('sales/quote_address');
        $this->orderAddressTable = $this->getTable('sales/order_address');
        $this->lockTable         = $this->getTable('cmpayments_lock');
    }
}