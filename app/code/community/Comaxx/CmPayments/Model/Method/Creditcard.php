<?php

/**
 * Payment method class for Creditcard
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Method_Creditcard extends Comaxx_CmPayments_Model_Method_Abstract
{
    protected $_code = 'cmpayments_cc';

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

        /** @var Comaxx_CmPayments_Helper_Config $configHelper */
        $configHelper    = Mage::helper('cmpayments/config');
        $data['issuers'] = explode(',', $configHelper->getPaymentMethodItem($this->_code, 'issuers'));

        return $data;
    }
}