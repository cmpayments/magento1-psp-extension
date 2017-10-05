<?php

/**
 * Block to insert additional fees into invoice totals (Magento backend)
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Block_Adminhtml_Sales_Totals_Invoice extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();

        $invoice = $this->getSource();
        $amount  = $invoice->getCmpaymentsFeeAmount();
        $method  = $invoice->getOrder()->getPayment()->getMethodInstance();

        if ($amount && $amount > 0) {
            $label = ($method instanceof Comaxx_CmPayments_Model_Method_Abstract) ? $method->getPmName() . ' ' . $this->helper('cmpayments')
                                                                                                                      ->__('payment fee') : 'Payment fee';

            $this->addTotalBefore(
                new Varien_Object(
                    array(
                    'code'       => 'cmpayments_payment_fee',
                    'value'      => $amount,
                    'base_value' => $amount,
                    'label'      => $label,
                    ), array('tax')
                )
            );
        }

        return $this;
    }

}