<?php

/**
 * Helper class for iDEAL QR payments
 * Class Comaxx_CmPayments_Helper_Api_QR
 */
class Comaxx_CmPayments_Helper_Api_IdealQR extends Comaxx_CmPayments_Helper_Api_Abstract
{
    /**
     * Update the given order with a charge_id when a payment is started
     *
     * @param Mage_Sales_Model_Order $order The order to update
     *
     * @return void
     */
    public function updatePaymentForOrder(Mage_Sales_Model_Order $order)
    {
        if ($qr_id = $this->_getQRIdFromOrder($order)) {
            /** @var Comaxx_CmPayments_Model_Api_IdealQR $apiIdealQR */
            $apiIdealQR = Mage::getModel('cmpayments/api_IdealQR');
            $payments   = $apiIdealQR->getPayments($qr_id);

            if (count($payments)) {
                $order->setCmpaymentsChargeId(current(array_keys($payments)))->save();
            }
        }
    }

    /**
     * Get result from charge for given order
     *
     * @param Mage_Sales_Model_Order $order The order to get result from
     *
     * @return array The result with success and redirect url
     */
    public function getResultFromCharge(Mage_Sales_Model_Order $order)
    {
        /** @var Comaxx_CmPayments_Model_Api_Charge $chargeModel */
        $chargeModel = Mage::getModel('cmpayments/api_Charge');
        $charge      = $chargeModel->getCharge($order);

        $result = array(
            'success' => false,
        );

        if (array_key_exists('status', $charge)) {
            /** @var Comaxx_CmPayments_Helper_Order $helper */
            $helper = Mage::helper('cmpayments/order');

            switch ($charge['status']) {
                case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_FAILED:
                case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_EXPIRED:
                    $helper->restoreLastQoute();

                case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_CANCELLED:
                    $userMsg = $helper->getMessageByStatus($charge['status']);

                    Mage::getSingleton('checkout/session')->addNotice($userMsg);

                    $result = array(
                        'success'  => true,
                        'redirect' => Mage::getUrl('checkout/cart'),
                    );
                    break;

                case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_SUCCESS:
                    $result = array(
                        'success'  => true,
                        'redirect' => Mage::getUrl('checkout/onepage/success'),
                    );
                    break;
            }
        }

        return $result;
    }

    /**
     * Get the QRId from the given order
     *
     * @param Mage_Sales_Model_Order $order The order to get the QR ID from
     *
     * @return null|string The QR id
     */
    private function _getQRIdFromOrder(Mage_Sales_Model_Order $order)
    {
        $payment               = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        if (array_key_exists('cm_qr_id', $additionalInformation)) {
            return $additionalInformation['cm_qr_id'];
        }

        return null;
    }
}