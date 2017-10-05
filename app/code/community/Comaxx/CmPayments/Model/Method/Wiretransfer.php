<?php

/**
 * Payment method class for Wire Transfer
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Method_Wiretransfer extends Comaxx_CmPayments_Model_Method_Abstract
{
    protected $_code = 'cmpayments_wt';
    //currently WireTransfer does not have a refund option
    protected $_canRefund = false;

    /**
     * Extracts payment screen URL from CM payment data
     *
     * @param Mage_Sales_Model_Order_Payment $payment     Payment object to place URL in
     * @param array                          $paymentData Payment data received from CM
     *
     * @return string URL if available, otherwise empty
     */
    public function getPaymentScreenUrl($payment, $paymentData)
    {
        //no payment is made at this moment, this payment provides the customer the option to pay later
        return null;
    }

    /**
     * Return true if the payment method can be used in the checkout
     *
     * @param Mage_Sales_Model_Quote $quote Quote belonging to current checkout
     *
     * @see Mage_Payment_Model_Method_Abstract::isAvailable()
     *
     * @return boolean True if the payment method can be used in the checkout, otherwise false
     */
    public function isAvailable($quote = null)
    {
        //for now disable this method
        return false;

    }
}