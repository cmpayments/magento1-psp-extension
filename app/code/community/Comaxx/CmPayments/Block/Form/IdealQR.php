<?php

/**
 * Class Comaxx_CmPayments_Block_Form_IdealQR
 */
class Comaxx_CmPayments_Block_Form_IdealQR extends Mage_Core_Block_Template
{

    /**
     * Get the image url for iDEAL QR
     *
     * @return null|string The url of the iDEAL QR Image
     */
    public function getImageUrl()
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId(
            Mage::helper('cmpayments/order')
            ->getActiveOrderId(Mage::app()->getRequest())
        );

        $payment               = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();

        if (array_key_exists('cm_qr_code_url', $additionalInformation)) {
            return $additionalInformation['cm_qr_code_url'];
        }

        return null;
    }
}