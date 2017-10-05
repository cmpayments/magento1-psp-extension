<?php

/**
 * Model for (CM) charge
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Api_Charge extends Comaxx_CmPayments_Model_Api_Abstract
{
    /**
     * Creates a charge in CM using the provied order's details
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Exception On general error
     * @throws Comaxx_CmPayments_Model_Exception on API error
     *
     * @return string URL for payment method screen (if needed). Can be empty.
     * @throws \Mage_Core_Exception
     */
    public function createFromOrder(Mage_Sales_Model_Order $order)
    {
        /** @var Comaxx_CmPayments_Model_Method_Abstract $paymentModel */
        $paymentModel = Mage::helper('cmpayments')->getPaymentModelFromOrder($order);

        if (! $paymentModel) {
            Mage::throwException('Payment model could not be found/loaded');
        }

        $chargeData = $paymentModel->getChargeData($order);

        $this->setMethod(self::METHOD_POST);
        $this->setAction($paymentModel->getApiAction());
        $this->setData($chargeData);

        $response = $this->doRequest();

        if ($response->hasError()) {
            throw new Comaxx_CmPayments_Model_Exception(
                $response->getErrorMessage(),
                Comaxx_CmPayments_Model_Exception::EXECUTION_CALL
            );
        }

        //no error occured, handle response
        $responseData = $response->getResponseData();

        if (array_key_exists('charge_id', $responseData)) {
            $chargeId = $responseData['charge_id'];

            //set the charge id in Magento
            $order->setCmpaymentsChargeId($chargeId);
            $order->save();
        }

        if (array_key_exists('payments', $responseData) && count($responseData['payments'])) {
            $apiPayment = reset($responseData['payments']);
            $payment    = $order->getPayment();

            //set the payment id for created charge in Magento
            $payment->setCmpaymentsPaymentId($apiPayment['payment_id']);

            //get payment screen URL for payment method (if needed)
            $url = $paymentModel->getPaymentScreenUrl($payment, $apiPayment);

            //save order and payment for changes
            $payment->save();

            return $url;
        }

        if ($paymentModel instanceof Comaxx_CmPayments_Model_Method_Idealqr) {
            $payment = $order->getPayment();

            //get payment screen URL for payment method (if needed)
            $url = $paymentModel->getPaymentScreenUrl($payment, $responseData);

            //save order and payment for changes
            $payment->save();

            return $url;
        }
    }

    /**
     * Extracts the current order status in CM using the order id
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Exception On general error
     * @throws Comaxx_CmPayments_Model_Exception on API error
     *
     * @return array|null Order data in CM or null if order could not be found or is not a CM order
     */
    public function getCharge(Mage_Sales_Model_Order $order)
    {
        $chargeId = $order->getCmpaymentsChargeId();

        if (! $chargeId) {
            /** @var Comaxx_CmPayments_Model_Method_Abstract $paymentModel */
            $paymentModel = Mage::helper('cmpayments')->getPaymentModelFromOrder($order);

            if ($paymentModel instanceof Comaxx_CmPayments_Model_Method_Idealqr) {
                /** @var Comaxx_CmPayments_Helper_Api_IdealQR $helper */
                $helper = Mage::helper('cmpayments/api_idealQR');
                $helper->updatePaymentForOrder($order);

                $chargeId = $order->getCmpaymentsChargeId();
            }

            if (! $chargeId) {
                //no CM payments order found
                return null;
            }
        }

        $this->setMethod(self::METHOD_GET);
        $this->setAction('charges');
        $this->setKey($chargeId);

        $response = $this->doRequest();

        if ($response->hasError()) {
            throw new Comaxx_CmPayments_Model_Exception(
                $response->getErrorMessage(),
                Comaxx_CmPayments_Model_Exception::EXECUTION_CALL
            );
        }

        //no error occured, handle response
        return $response->getResponseData();
    }
}