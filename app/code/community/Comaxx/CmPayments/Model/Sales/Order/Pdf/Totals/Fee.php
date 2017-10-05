<?php

/**
 * Model to update the pdf invoice with additional fee information
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Sales_Order_Pdf_Totals_Fee extends Mage_Sales_Model_Order_Pdf_Total_Default
{
    const INCL_EXCL_TAX = '3', INCL_TAX = '2';

    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $pm_code = $pm_name = null;
        $order   = $this->getOrder();
        $totals  = array();

        //check if order is paid via afterpay, if not skip this display entry
        if (count($order->getPaymentsCollection())) {
            $payment = $order->getPayment();

            $method  = $payment->getMethodInstance();
            $pm_code = $method->getCode();
            $pm_name = $method->getPmName();

            if ( ! ($method instanceof Comaxx_CmPayments_Model_Method_Abstract)) {
                return $totals;
            }
        } else {
            return $totals;
        }
        $helper      = Mage::helper('cmpayments/config');
        $prefix      = $this->getAmountPrefix();
        $display_tax = $helper->getPaymentMethodItem($pm_code, 'additional_fee_displaytax');
        $fontSize    = $this->getFontSize();

        //set amounts to be displayed
        $amount          = $order->getCmpaymentsFeeAmount();
        $amount_incl_tax = $amount + $order->getCmpaymentsFeeTaxAmount();

        //format fields for correct price display
        $amount          = $this->getOrder()->formatPriceTxt($amount);
        $amount_incl_tax = $this->getOrder()->formatPriceTxt($amount_incl_tax);

        //display items (note fontsize is variable passed by calling class)
        if ($display_tax === self::INCL_EXCL_TAX) {
            $totals = array(
                array(
                    'amount'    => $prefix . $amount,
                    'label'     => $pm_name . ' ' . $helper->__('payment fee (Excl.VAT)') . ':',
                    'font_size' => $fontSize,
                ),
                array(
                    'amount'    => $prefix . $amount_incl_tax,
                    'label'     => $pm_name . ' ' . $helper->__('payment fee (Incl.VAT)') . ':',
                    'font_size' => $fontSize,
                ),
            );
        } elseif ($display_tax === self::INCL_TAX) {
            $totals = array(
                array(
                    'amount'    => $prefix . $amount_incl_tax,
                    'label'     => $pm_name . ' ' . $helper->__('payment fee') . ':',
                    'font_size' => $fontSize,
                ),
            );
        } else {
            $totals = array(
                array(
                    'amount'    => $prefix . $amount,
                    'label'     => $pm_name . ' ' . $helper->__('payment fee (Excl.VAT)') . ':',
                    'font_size' => $fontSize,
                ),
            );
        }

        return $totals;
    }
}
