<?php

/**
 * Payment method class for Ideal QR
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Method_Idealqr extends Comaxx_CmPayments_Model_Method_Abstract
{

    protected $_code = 'cmpayments_idl_qr';

    /**
     * Get the API action for this payment method
     *
     * @return string The API action
     */
    public function getApiAction()
    {
        return 'qr';
    }

    /**
     * Get the charge data for Ideal QR
     *
     * @param Mage_Sales_Model_Order $order The order to get the charge data for
     *
     * @return array Charge data
     */
    public function getChargeData(Mage_Sales_Model_Order $order)
    {
        $amount      = round($order->getGrandTotal(), 2);
        $currency    = $order->getOrderCurrencyCode();
        $beneficiary = Mage::app()->getStore()->getFrontendName() ?: Mage::app()->getStore()->getName();

        // Get urls and purchase_id from default paymentdetails method
        $data = $this->getPaymentDetails($order);
        $data = array_merge(
            $data, array(
            'amount'            => $amount,
            'amount_changeable' => false,
            'currency'          => $currency,
            'description'       => Mage::helper('cmpayments')->__('Payment of order %s', $order->getRealOrderId()),
            'one_off'           => false,
            'expiration'        => date(DATE_RFC3339, strtotime('+1 day')),
            'beneficiary'       => $beneficiary,
            'size'              => 1000,
            )
        );

        return $data;
    }

    /**
     * Extracts payment screen URL from CM payment data
     *
     * @param Mage_Sales_Model_Order_Payment $payment     Payment object to place URL in
     * @param array                          $paymentData Payment data received from CM
     *
     * @return string URL if available, otherwise empty
     * @throws \Mage_Core_Exception
     */
    public function getPaymentScreenUrl($payment, $paymentData)
    {
        if (array_key_exists('qr_id', $paymentData) && array_key_exists('qr_code_url', $paymentData)) {
            $dataFields                   = $payment->getAdditionalInformation();
            $dataFields['cm_qr_id']       = $paymentData['qr_id'];
            $dataFields['cm_qr_code_url'] = $paymentData['qr_code_url'];
            $payment->setAdditionalInformation($dataFields);
        } else {
            Mage::throwException('Could not complete iDeal QR because of missing qr_id and qr_code_url');
        }

        return Mage::getUrl('cmpayments/idealQR/index');
    }
}