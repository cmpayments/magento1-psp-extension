<?php

/**
 * Payment method class for Sofortuberweisung
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Method_Sofortuberweisung extends Comaxx_CmPayments_Model_Method_Abstract
{
    protected $_code = 'cmpayments_sof';
    protected $_minimumQuoteAmount = 0.10;

    /**
     * Extracts payment method specific charge data to be used in the charge call
     *
     * @param Mage_Sales_Model_Order $order Order to extract charge data from
     *
     * @return array Payment details
     */
    protected function getPaymentDetails(Mage_Sales_Model_Order $order)
    {
        $data = parent::getPaymentDetails($order);

        $data['consumer_name'] = $order->getCustomerName();
        $data['description']   = Mage::helper('cmpayments')->__('Payment of order %s', $order->getRealOrderId());

        return $data;
    }
}