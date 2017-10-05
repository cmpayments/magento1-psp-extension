<?php

/**
 * Abstract helper class for api helpers
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Helper_Api_Abstract extends Mage_Core_Helper_Abstract
{
    const RETURN_URL_SUCCESS = 'cmpayments/payment/success/', RETURN_URL_CANCELED = 'cmpayments/payment/cancel/', RETURN_URL_FAILED = 'cmpayments/payment/failed/', RETURN_URL_EXPIRED = 'cmpayments/payment/expired/', ORDER_STATUS_OPEN = 'Open', ORDER_STATUS_SUCCESS = 'Success', ORDER_STATUS_CANCELLED = 'Cancelled', ORDER_STATUS_FAILED = 'Failure', ORDER_STATUS_EXPIRED = 'Expired', REFUND_STATUS_SUCCESS = 'Succeeded', REFUND_STATUS_FAILED = 'Failed', REFUND_STATUS_PENDING = 'Pending';

    /**
     * Extracts payment method settings from CM
     *
     * @return array List payment methods with CM data
     */
    public function getPaymentMethods()
    {
        /** @var Comaxx_CmPayments_Model_Api_PaymentMethod $paymentMethod */
        $paymentMethod = Mage::getModel('cmpayments/api_PaymentMethod');

        /** @var Comaxx_CmPayments_Model_Api_Response $response */
        return $paymentMethod->getPaymentMethods();
    }

    /**
     * Extracts supported Ideal issuers
     *
     * @return array List of issuers with 'issuer Name' => 'issuer key'
     */
    public function getIdealIssuers()
    {
        /** @var Comaxx_CmPayments_Model_Api_PaymentMethod $paymentMethod */
        $paymentMethod = Mage::getModel('cmpayments/api_PaymentMethod');

        /** @var Comaxx_CmPayments_Model_Api_Response $response */
        return $paymentMethod->getIdealIssuers();
    }
}