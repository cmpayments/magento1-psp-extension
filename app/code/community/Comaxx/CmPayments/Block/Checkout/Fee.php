<?php

/**
 * Block for displaying the additional fee in the checkout totals overview
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Block_Checkout_Fee extends Mage_Checkout_Block_Total_Default
{

    protected $_template = 'comaxx_cmpayments/checkout/fee.phtml';
    private $_tax_display;

    const INCL_EXCL_TAX = '3', INCL_TAX = '2', EXCL_TAX = '1';

    public function __construct()
    {
        $this->setTemplate($this->_template);
        $this->_store = Mage::app()->getStore();
    }

    private function getTaxDisplay()
    {
        if (! $this->_tax_display) {
            $payment = $this->getTotal()->getAddress()->getQuote()->getPayment();
            if ($payment) {
                $this->_tax_display = $this->helper('cmpayments/config')
                                           ->getPaymentMethodItem($payment->getMethod(), 'additional_fee_displaytax');
            }
        }

        return $this->_tax_display;
    }

    /**
     * Check if we need display afterpay costs include and exclude tax
     *
     * @return bool
     */
    public function displayBoth()
    {
        return $this->getTaxDisplay() === self::INCL_EXCL_TAX;
    }

    /**
     * Check if we need display shipping include tax
     *
     * @return bool
     */
    public function displayIncludeTax()
    {
        return $this->getTaxDisplay() === self::INCL_TAX;
    }

    /**
     * Check if we need display shipping exclude tax
     *
     * @return bool
     */
    public function displayExcludeTax()
    {
        return $this->getTaxDisplay() === self::EXCL_TAX;
    }

    /**
     * Get shipping amount include tax
     *
     * @return float
     */
    public function getPaymentsFeeIncludeTax()
    {
        $address = $this->getTotal()->getAddress();
        $quote   = $address->getQuote();

        return $quote->getCmpaymentsFeeAmount() + $quote->getCmpaymentsFeeTaxAmount();
    }

    /**
     * Get shipping amount exclude tax
     *
     * @return float
     */
    public function getPaymentsFeeExcludeTax()
    {
        $address = $this->getTotal()->getAddress();
        $quote   = $address->getQuote();

        return $quote->getCmpaymentsFeeAmount();
    }

    public function getIncludeTaxLabel()
    {
        $address      = $this->getTotal()->getAddress();
        $quote        = $address->getQuote();
        $payment      = $quote->getPayment();
        $payment_name = '';

        if ($payment->getMethod() !== null) {
            $method       = $payment->getMethodInstance();
            $payment_name = $method->getPmName() . ' ';
        }

        return $payment_name . ' ' . $this->helper('cmpayments')->__('payment fee (Incl.VAT)');
    }

    public function getExcludeTaxLabel()
    {
        $address      = $this->getTotal()->getAddress();
        $quote        = $address->getQuote();
        $payment      = $quote->getPayment();
        $payment_name = '';

        if ($payment->getMethod() !== null) {
            $method       = $payment->getMethodInstance();
            $payment_name = $method->getPmName() . ' ';
        }

        return $payment_name . ' ' . $this->helper('cmpayments')->__('payment fee (Excl.VAT)');
    }
}