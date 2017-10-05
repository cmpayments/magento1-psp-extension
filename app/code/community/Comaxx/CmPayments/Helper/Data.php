<?php

/**
 * Generic helper class with function relevant to a wider scope of classes within the CM Payments plugin.
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Log message into CM Payments log
     *
     * @param mixed   $message  message to log
     * @param integer $severity Zend_Log severity level
     *
     * @return void
     */
    public function log($message, $severity = Zend_Log::INFO)
    {
        Mage::log($message, $severity, 'cmpayments.log');
    }

    /**
     * Extracts the payment method model belonging to the provided order.
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return mixed Model for payment method belonging to provided order
     */
    public function getPaymentModelFromOrder(Mage_Sales_Model_Order $order)
    {
        $payment = $order->getPayment();
        $method  = $payment->getMethodInstance()->getCode();
        //get model belonging to payment method and use it to update the
        $modelRef = Mage::helper('cmpayments/config')->getPaymentMethodItem($method, 'model');

        return ($modelRef !== null) ? Mage::getModel($modelRef) : null;
    }
}