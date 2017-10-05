<?php

/**
 * Payment method class for iDEAL
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Method_Ideal extends Comaxx_CmPayments_Model_Method_Abstract
{
    protected $_code = 'cmpayments_idl';
    protected $_formBlockType = 'cmpayments/form_ideal';

    /**
     * Return the iDEAL issuer selected by the shopper
     *
     * return int IDEAL issuer ID
     */
    protected function getIssuer()
    {
        $data = $this->getInfoInstance()->getAdditionalInformation();

        return isset($data['issuer']) ? $data['issuer'] : null;
    }

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

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment             = $order->getPayment();
        $data['issuer_id']   = $payment->getAdditionalInformation('issuer');
        $data['description'] = Mage::helper('cmpayments')->__('Payment of order %s', $order->getRealOrderId());

        return $data;
    }
}