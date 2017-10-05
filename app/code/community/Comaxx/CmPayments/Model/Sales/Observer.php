<?php

/**
 * Controller used to handle CmPayments callbacks
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Sales_Observer
{

    /**
     * Sends refund command to CM Payments API if it's a CM Payment order
     *
     * @param Varien_Event_Observer $observer Event observer
     *
     * @return void
     *
     * @throws Exception In case payment cannot be refunded
     */
    public function refundPayment($observer)
    {
        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order      = $creditMemo->getOrder();
        $chargeId   = $order->getCmpaymentsChargeId();

        //only handle CM order refunds
        if ($chargeId) {
            $helper = Mage::helper('cmpayments');

            //can only do full refunds, make sure no modifications are made
            if ($creditMemo->getAdjustmentPositive() || $creditMemo->getAdjustmentNegative() || ($creditMemo->getShippingAmount() != $order->getShippingAmount())) {
                Mage::throwException($helper->__('A CM refund can only be for the full payment.'));
            }

            //attempt to refund full payment
            if (! Mage::helper('cmpayments/order')->refundPayment($order)) {
                $helper->log('Manual refund request of an order failed: ' . $order->getRealOrderId(), Zend_Log::WARN);
                Mage::throwException('The CM refund request could not be completed. ');
            } else {
                $helper->log('Manual refund request of an order succeeded: ' . $order->getRealOrderId());
            }
        }
    }

    /**
     * Provide extra information in the "Payment Information" block
     *
     * @param Varient_Event_Observer $observer
     *
     * @return void
     */
    public function specificInformation(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getPayment();
        if ($payment && 0 === stripos($payment->getMethod(), 'cmpayments')) {
            $order = $payment->getOrder();
            if ($order) {
                /** @var Varien_Object $transport */
                $transport = $observer->getTransport();
                if (Mage::app()->getStore()->isAdmin()) {
                    if ($order->getCmpaymentsChargeId()) {
                        $transport->setData('CmPayments Charge Id', $order->getCmpaymentsChargeId());
                    }
                } else {
                    // Here it's possible to show extra info in "My Orders" in the frontend.
                }
            }
        }
    }
}