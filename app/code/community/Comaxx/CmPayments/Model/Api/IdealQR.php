<?php

/**
 * Model class for iDEAL QR API calls
 *
 * Class Comaxx_CmPayments_Model_Api_IdealQR
 */
class Comaxx_CmPayments_Model_Api_IdealQR extends Comaxx_CmPayments_Model_Api_Abstract
{
    /**
     * Get payment data for given qr_id
     *
     * @param string $qr_id The id of the qr charge
     *
     * @return array The responseData from Api
     * @throws Comaxx_CmPayments_Model_Exception
     */
    public function getPayments($qr_id)
    {
        $this->setMethod(self::METHOD_GET);
        $this->setAction('qr');
        $this->setKey($qr_id);

        $response = $this->doRequest();

        if ($response->hasError()) {
            throw new Comaxx_CmPayments_Model_Exception(
                $response->getErrorMessage(),
                Comaxx_CmPayments_Model_Exception::EXECUTION_CALL
            );
        }

        $responseData = $response->getResponseData();

        if (array_key_exists('payments', $responseData)) {
            return $responseData['payments'];
        }

        return null;
    }
}