<?php

/**
 * Controller for iDEAL QR payments
 *
 * Class Comaxx_CmPayments_IdealQRController
 */
class Comaxx_CmPayments_IdealQRController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        if ($order = $this->_getActiveOrder()) {
            /** @var Comaxx_CmPayments_Helper_Data $helper */
            $helper = Mage::helper('cmpayments');

            $payment               = $order->getPayment();
            $additionalInformation = $payment->getAdditionalInformation();
            if (! array_key_exists('cm_qr_code_url', $additionalInformation)) {
                $helper->log(
                    'Redirect Action error occurred during create of order ' . $order->getRealOrderId() . ': Missing QR Code Url',
                    Zend_Log::ERR
                );
                //Cancel order in Magento
                Mage::helper('cmpayments/order')
                    ->cancelOrder(
                        $this->__('We\'re sorry but an error occurred trying to create your order. We restored your shopping cart, and you may try again or come back later. We will keep your shopping cart saved if you\'re logged in.'),
                        $order->getRealOrderId()
                    );

                $this->_redirect('checkout/cart');
            }

            $this->loadLayout();
            $this->renderLayout();
        }
    }

    /**
     * Check payment status of current iDEAL QR payment
     */
    public function checkPaymentAction()
    {
        if (! $this->_validateFormKey() || ! $this->getRequest()->isAjax()) {
            return;
        }

        $order = $this->_getActiveOrder();

        /** @var Comaxx_CmPayments_Helper_Api_IdealQR $helper */
        $helper = Mage::helper('cmpayments/api_idealQR');
        $helper->updatePaymentForOrder($order);

        $result = array(
            'success' => false,
        );

        if ($order->getCmpaymentsChargeId()) {
            $result = $helper->getResultFromCharge($order);
        }

        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($result));
    }

    /**
     * Get the currently active order
     *
     * @return Mage_Sales_Model_Order|null The active order
     */
    private function _getActiveOrder()
    {
        $activeOrderId = Mage::helper('cmpayments/order')->getActiveOrderId($this->getRequest());

        // retrieve the order
        $order = Mage::getModel('sales/order')->loadByIncrementId($activeOrderId);

        if (! $activeOrderId || ($order && ! $order->getId())) {
            /** @var Comaxx_CmPayments_Helper_Data $helper */
            $helper = Mage::helper('cmpayments');

            $helper->log('Error occurred during load of iDEAL QR payment: No active order', Zend_Log::ERR);

            Mage::getSingleton('checkout/session')
                ->addError($this->__('We\'re sorry but an error occurred trying to load your payment. We restored your shopping cart, and you may try again or come back later. We will keep your shopping cart saved if you\'re logged in.'));

            $this->_redirect('checkout/cart');

            return;
        }

        return $order;
    }
}