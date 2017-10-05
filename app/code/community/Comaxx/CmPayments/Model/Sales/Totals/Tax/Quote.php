<?php

/**
 * Model to calculate grand total using additional tax fees on frontend
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Sales_Totals_Tax_Quote extends Mage_Sales_Model_Quote_Address_Total_Tax
{
    private $_pm_code;

    /**
     * Collect function called by Magento. Includes costs/tax to quote.
     *
     * @param Mage_Sales_Model_Quote_Address $address address used to determine quote costs
     *
     * @return Mage_Sales_Model_Quote_Address_Total_Tax instance of current class
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $pm_code = null;
        $quote   = $address->getQuote();
        $store   = $quote->getStore();

        // check if a method with additional fee is selected to add/remove additional fee
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

        //used to execute via shipping only (so it is only applied once and not on subtotal of order items)
        $items = $address->getAllItems();
        if (! count($items)) {
            return $this;
        }

        $config_helper = Mage::helper('cmpayments/config');

        // check if there is additional fee
        $additional_fee = (int)$config_helper->getPaymentMethodItem($pm_code, 'additional_fee');

        //determine fee and include into address/quote
        $this->_includeAdditionalFee($address, $quote, $additional_fee, $store, $config_helper);

        return $this;
    }

    /**
     * Calculates the cost details and inserts additional fee into quote (does not change Magento fields in quote)
     *
     * @param Mage_Sales_Model_Quote_Address  $address        address used to determine quote costs
     * @param Mage_Sales_Model_Quote          $quote          quote that is to be updated
     * @param string                          $additional_fee additional fee for this quote
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
        $rate               = 0;

        if ($tax_included && $payment_tax_class) {
            // tax is included in fee
            // calculate price excl tax (splitting actual fee and fee-tax)
            if ($rate = $tax_calc->getRate($request->setProductClassId($payment_tax_class))) {
                $tax = $additional_fee - ($additional_fee / ($rate + 100) * 100);
            }
        }

        //set amounts in address
        $address->setTaxAmount($address->getTaxAmount() + $tax);
        $address->setBaseTaxAmount($address->getBaseTaxAmount() + $tax);

        $address->setGrandTotal($address->getGrandTotal() + $additional_fee);
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $additional_fee);

        //save tax info that was applied
        $this->_saveAppliedTaxes($address, $tax_calc->getAppliedRates($request), $tax, $tax, $rate);
    }

    /**
     * Used by Magento to extract a totals row for additional fee.
     *
     * @param Mage_Sales_Model_Quote_Address $address address used to determine quote costs
     *
     * @return
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_setAddress($address);

        //no need to add seperate line for tax, it is included in the main tax entry.
        return $this;
    }
}