<?php

/**
 * Model to calculate grand total using additional fees on frontend
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Sales_Totals_Invoice extends Mage_Sales_Model_Order_Invoice_Total_Subtotal
{

    /**
     * Collect invoice subtotal
     *
     * @param   Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return  Mage_Sales_Model_Order_Invoice_Total_Subtotal
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        //  check if additional fee is selected
        if (count($order->getPaymentsCollection())) {
            if ($invoice->getCmpaymentsFeeAmount() == 0 || $invoice->getCmpaymentsFeeAmount() == null) {
                return $this;
            }
        } else {
            return $this;
        }

        //additional fee is used in this order
        //note: values are already on invoice object, just not included in total

        //update grand total with additional fee
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getCmpaymentsFeeAmount());
        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getCmpaymentsFeeAmount());

        //subtotal(incl tax) is incl additional fee tax at the moment, remove it for proper totals overview
        $invoice->setSubtotalInclTax($invoice->getSubtotalInclTax() - $invoice->getCmpaymentsFeeTaxAmount());
        $invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax() - $invoice->getCmpaymentsFeeTaxAmount());

        return $this;
    }
}