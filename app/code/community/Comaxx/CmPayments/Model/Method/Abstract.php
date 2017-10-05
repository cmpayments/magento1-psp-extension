<?php

/**
 * Abstract class used as a base for payment methods
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
abstract class Comaxx_CmPayments_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
    /* Payment code which is the unique identifier for the payment method */
    protected $_code;
    /* Minimum quote amount which allows using current payment method */
    protected $_minimumQuoteAmount = 0.01;

    /**
     * Availability options
     * Set Magento availability in order to use their respective functionality
     */
    protected $_isGateway = true;
    protected $_canAuthorize = false;  // Set true, if you have authorization step.
    protected $_canCapture = false; // Set true, if your payment method allows to perform capture transaction (usally only credit cards methods)
    protected $_canCapturePartial = false;
    protected $_canRefund = true;  // Set true, if online refunds are available
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = true;  // Set true, if you can cancel authorization via API online
    protected $_canUseInternal = true;  // Enables use of method internally (backend)
    protected $_canUseCheckout = true;  // Enables use of method for customers (frontend)
    protected $_canUseForMultishipping = false; // Set true, if method can be used for shipping to several addresses
    protected $_isInitializeNeeded = true; // call initialize on method instead of authorize on creation or order

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
        // Is the module active?
        $module = Mage::helper('cmpayments/config')->isActive();

        if (! $module) {
            return false;
        }

        $grandTotal = (float)$quote->getGrandTotal();
        if ($grandTotal < $this->_minimumQuoteAmount) {
            return false;
        }

        // Does the parent agree that the method is usable?
        return parent::isAvailable($quote);
    }

    /**
     * Check if this payment method can be used for a specific country
     *
     * @param string $country Country of the billing address
     *
     * @see Mage_Payment_Model_Method_Abstract::canUseForCountry()
     *
     * @return boolean True if the payment method can be used for the specified country, otherwise false
     */
    public function canUseForCountry($country)
    {
        //extract countries available for current payment method
        $config_helper       = Mage::helper('cmpayments/config');
        $countries_setting   = $config_helper->getPaymentMethodItem($this->_code, 'regions');
        $available_countries = explode(',', $countries_setting);
        $european_countries  = explode(',', $config_helper->getItem('european_countries'));

        //check if country is supported by payment method
        //in case 'EUR' is in payment method countries the european countries are supported
        //in case 'INT' is in payment method countries it is internationally supported
        return in_array('INT', $available_countries, true) || in_array(
            $country, $available_countries,
            true
        ) || (in_array('EUR', $available_countries, true) && in_array($country, $european_countries, true));
    }

    /**
     * Returns configured payment method name
     *
     * @return string Returns the name
     */
    public function getPmName()
    {
        return $this->getConfigData('title');
    }

    /**
     * Returns method name for the CM API
     *
     * @return string Returns the CM name of the payment method
     */
    public function getCmMethodName()
    {
        return $this->getConfigData('cmname');
    }

    /**
     * Return the url to redirect which will initiate the order at CmPayments
     * Called via Mage_Sales_Model_Quote_Payment::getOrderPlaceRedirectUrl()
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        //always redirect to the paymentcontroller to create the charge at CM and redirect user to the proper payment page
        return Mage::getUrl('cmpayments/payment/redirect');
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @see Mage_Payment_Model_Method_Abstract::getConfigPaymentAction()
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return self::ACTION_AUTHORIZE;
    }

    /**
     * Assign form data as additional information
     * @see Mage_Payment_Model_Method_Abstract::assignData()
     */
    public function assignData($data)
    {
        if (! ($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $dataFields = array_map('trim', $data->getData());

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation($dataFields);

        return $this;
    }

    /**
     * Get the name of the action that should be called in the API
     *
     * @return string The name of the action
     */
    public function getApiAction()
    {
        return 'charges';
    }

    /**
     * Extracts payment method specific charge data to be used in the charge call
     *
     * @param Mage_Sales_Model_Order $order Order to extract charge data from
     *
     * @return array Charge data
     */
    public function getChargeData(Mage_Sales_Model_Order $order)
    {
        //add default charge fields
        $amount     = round($order->getGrandTotal(), 2);
        $currency   = $order->getOrderCurrencyCode();
        $chargeData = array(
            'amount'   => $amount,
            'currency' => $currency,
            'payments' => array(
                array(
                    'amount'          => $amount,
                    'currency'        => $currency,
                    'payment_method'  => $this->getCmMethodName(),
                    'payment_details' => $this->getPaymentDetails($order),
                ),
            ),
        );

        return $chargeData;
    }

    /**
     * Get payment details for given order
     *
     * @param Mage_Sales_Model_Order $order The order to get the payment details from
     *
     * @return array The payment details for given order
     */
    protected function getPaymentDetails(Mage_Sales_Model_Order $order)
    {
        //add postfix in order to process the URL at a later time
        $urlPostfix = 'id/' . $order->getRealOrderId();

        return array(
            'success_url'   => Mage::getUrl(
                Comaxx_CmPayments_Helper_Api_Abstract::RETURN_URL_SUCCESS . $urlPostfix,
                array('_secure' => true)
            ),
            'failed_url'    => Mage::getUrl(
                Comaxx_CmPayments_Helper_Api_Abstract::RETURN_URL_FAILED . $urlPostfix,
                array('_secure' => true)
            ),
            'cancelled_url' => Mage::getUrl(
                Comaxx_CmPayments_Helper_Api_Abstract::RETURN_URL_CANCELED . $urlPostfix,
                array('_secure' => true)
            ),
            'expired_url'   => Mage::getUrl(
                Comaxx_CmPayments_Helper_Api_Abstract::RETURN_URL_EXPIRED . $urlPostfix,
                array('_secure' => true)
            ),
            'purchase_id'   => $order->getRealOrderId(),
        );
    }

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
        $paymentDetails = isset($paymentData['payment_details']) ? $paymentData['payment_details'] : array();

        $url = '';
        if (isset($paymentDetails['authentication_url'])) {
            $url = $paymentDetails['authentication_url'];

            //save url for future use
            $dataFields                          = $payment->getAdditionalInformation();
            $dataFields['cm_authentication_url'] = $url;
            $payment->setAdditionalInformation($dataFields);
        }

        return $url;
    }
}