<?php

/**
 * Block to insert additional fees into order totals (Magento backend)
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Block_Adminhtml_Sales_Totals_Creditmemo extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();

        $order  = $this->getSource()->getOrder();
        $amount = $order->getCmpaymentsFeeAmount();
        $method = $order->getPayment()->getMethodInstance();

        $tax = $order->getCmpaymentsFeeTaxAmount();

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

            //update totals for creditmemo since Magento does not use order/invoice grand totals
            $creditmemo = $this->getCreditMemo();
            //set tax
            $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $tax);
            $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $tax);
            //set grand total
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $amount + $tax);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount + $tax);

            //set creditmemo values on total overview
            $grandTotal = $this->getTotal('grand_total');
            $grandTotal->setBaseValue($creditmemo->getBaseGrandTotal());
            $grandTotal->setValue($creditmemo->getGrandTotal());
        }

        return $this;
    }

}