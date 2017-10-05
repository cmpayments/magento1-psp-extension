<?php

/**
 * Model for (CM) Refunds
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Api_Refund extends Comaxx_CmPayments_Model_Api_Abstract
{

    /**
     * Attempts to refund the provided payment id
     *
     * @param string $paymentId Payment id to refund
     * @param string $amount Amount to refund
     * @param string $currency Currency to refund in
     *
     * @throws Comaxx_CmPayments_Model_Exception on API error
     *
     * @return array Refund response data
     */
    public function refundPayment($paymentId, $amount, $currency)
    {
        $this->setMethod(self::METHOD_POST);
        $this->setAction('refunds');
        $this->setData(
            array(
            'amount'     => number_format($amount, 2),
            'currency'   => $currency,
            'payment_id' => $paymentId,
            )
        );

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