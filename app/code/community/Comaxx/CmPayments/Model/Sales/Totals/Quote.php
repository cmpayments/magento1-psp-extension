<?php

/**
 * Model to calculate grand total using additional fees on frontend
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Sales_Totals_Quote extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    private $_pm_code;

    public function __construct()
    {
        //set code for this totals entry
        $this->setCode('cmpayments_payment_fee');
    }

    /**
     * Collect function called by Magento. Includes afterpay costs into quote
     *
     * @param Mage_Sales_Model_Quote_Address $address address used to determine quote costs
     *
     * @return Mage_Sales_Model_Quote_Address_Total_Tax instance of current class
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $quote = $address->getQuote();
        $store = $quote->getStore();

        // check if a method with additional fee is selected to add additional fee
        if (count($quote->getPaymentsCollection())) {
            $payment = $quote->getPayment();
            $method  = null;

            if ($payment->getMethod() !== null) {
                $method         = $payment->getMethodInstance();
                $this->_pm_code = $pm_code = $method->getCode();
            }

            if (! ($method instanceof Comaxx_CmPayments_Model_Method_Abstract)) {
                //set additional costs to default
                $quote->setCmpaymentsFeeAmount(0);
                $quote->setCmpaymentsFeeTaxAmount(0);

                return $this;
            }
        } else {
            return $this;
        }

        $config_helper = Mage::helper('cmpayments/config');

        // check if there are extra costs
        $additional_fee = (int)$config_helper->getPaymentMethodItem($pm_code, 'additional_fee');

        // calculating only for billing address!
        if ($address->getAddressType() == 'billing') {
            //determine fee and include into address/quote
            $this->_includeAdditionalFee($address, $quote, $additional_fee, $store, $config_helper);
        }

        return $this;
    }

    /**
     * Calculates the cost details and inserts additional fee costs/tax in grand total/tax of quote
     *
     * @param Mage_Sales_Model_Quote_Address  $address        address used to determine quote costs
     * @param Mage_Sales_Model_Quote          $quote          quote that is to be updated
     * @param string                          $additional_fee additional costs for this quote
     * @param Mage_Core_Model_Store           $store          store this quote is created on
     * @param Comaxx_CmPayments_Helper_Config $config_helper  helper for config values
     *
     * @return void
     */
    private function _includeAdditionalFee(
        Mage_Sales_Model_Quote_Address $address,
        Mage_Sales_Model_Quote $quote,
        $additional_fee,
        Mage_Core_Model_Store $store,
        Comaxx_CmPayments_Helper_Config $config_helper
    ) 
{ 
     
     
     
     
     
     
     
     
     
    
        $tax = 0;

        // initial tax calculation
        $tax_calc           = Mage::getSingleton('tax/calculation');
        $customer_tax_class = $quote->getCustomerTaxClassId();
        $payment_tax_class  = $config_helper->getPaymentMethodItem($this->_pm_code, 'additional_fee_taxclass');
        $tax_included       = $config_helper->getPaymentMethodItem($this->_pm_code, 'additional_fee_tax_included');
        $request            = $tax_calc->getRateRequest(
            $address, $quote->getBillingAddress(), $customer_tax_class,
            $store
        );

        if ($tax_included) {
            if ($payment_tax_class) {
                // tax is included in fee
                // calculate price excl tax (splitting actual fee and fee-tax)
                if ($rate = $tax_calc->getRate($request->setProductClassId($payment_tax_class))) {
                    $tax = $additional_fee - ($additional_fee / ($rate + 100) * 100);
                }
            }

            // deduct tax from base fee
            $additional_fee -= $tax;
        }

        $fee = $store->roundPrice($additional_fee);
        $tax = $store->roundPrice($tax);

        //convert currency of quote to store currency
        $fee = $store->convertPrice($fee, false);
        $tax = $store->convertPrice($tax, false);

        // Set new values for additional fee to Address
        $quote->setCmpaymentsFeeAmount($fee);
        $quote->setCmpaymentsFeeTaxAmount($tax);
    }

    /**
     * Used by Magento to extract a totals row for afterpay fee.
     *
     * @param Mage_Sales_Model_Quote_Address $address address used to determine quote costs
     *
     * @return
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_setAddress($address);
        $quote = $address->getQuote();

        $fee = $quote->getCmpaymentsFeeAmount();
        if ($fee > 0 && $address->getAddressType() === 'billing') {
            $method = $quote->getPayment()->getMethodInstance();
            if (($method instanceof Comaxx_CmPayments_Model_Method_Abstract)) {
                $address->addTotal(
                    array(
                    'code'  => $this->getCode(),
                    'title' => $method->getPmName() . ' ' . Mage::helper('cmpayments')->__('payment fee'),
                    'value' => $fee,
                    )
                );
            }
        }

        return $this;
    }
}