<?php

class Comaxx_CmPayments_Model_Api_PaymentMethod extends Comaxx_CmPayments_Model_Api_Abstract
{
    /**
     * Extracts supported and enabled payment methods for active merchant
     *
     * @return array List payment methods with CM data
     */
    public function getPaymentMethods()
    {
        $this->setMethod(self::METHOD_GET);
        $this->setAction('payment_methods');

        $response = $this->doRequest();

        if (! $response->hasError()) {
            $methods = $response->getResponseData();

            $paymentMethods = array();
            foreach ($methods as $name => $properties) {
                $_paymentMethod = new Varien_Object();
                $_paymentMethod->addData($properties);
                $_paymentMethod->setName($name);
                $paymentMethods[] = $_paymentMethod;
            }

            return $paymentMethods;
        }

        return array();
    }

    /**
     * Extracts supported Ideal issuers from CM Payments API
     *
     * @return array List of issuers with 'issuer Name' => 'issuer key'
     */
    public function getIdealIssuers()
    {
        $this->setMethod(self::METHOD_GET);
        $this->setAction('issuers');
        $this->setKey('ideal');

        $response = $this->doRequest();

        if (! $response->hasError()) {
            return $response->getResponseData();
        }
    }
}