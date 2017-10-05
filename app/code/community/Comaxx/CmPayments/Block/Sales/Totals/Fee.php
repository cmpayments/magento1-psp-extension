<?php

/**
 * Block to insert additional fees into order totals (Magento frontend) (used for order,invoice and creditmemo)
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Block_Sales_Totals_Fee extends Mage_Sales_Block_Order_Totals
{
    /**
     * Initialize totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();

        $order  = $this->getSource();
        $amount = $order->getCmpaymentsFeeAmount();
        $method = $order->getPayment()->getMethodInstance();

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