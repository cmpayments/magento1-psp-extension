<?php

/**
 * Payment method class for Paypal
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Method_Paypal extends Comaxx_CmPayments_Model_Method_Abstract
{
    protected $_code = 'cmpayments_ppl';

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

        $data['description'] = Mage::helper('cmpayments')->__('Payment of order %s', $order->getRealOrderId());

        return $data;
    }
}