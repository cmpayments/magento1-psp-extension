<?php

/**
 * Controller used to handle CM Payment callbacks
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action executed to redirect the customer to the CM Payments - payment page
     *
     * @throws Mage_Payment_Model_Info_Exception
     *
     * @return void
     */
    public function redirectAction()
    {
        /** @var Comaxx_CmPayments_Helper_Data $helper */
        $helper = Mage::helper('cmpayments');

        // retrieve the order
        $order = Mage::getModel('sales/order')->loadByIncrementId(
            Mage::helper('cmpayments/order')
            ->getActiveOrderId(Mage::app()->getRequest())
        );

        if (! $order) {
            //log error and redirect customer since there is no order
            $helper->log('Redirect Action for the order could not proceed: no order found');
            Mage::getSingleton('checkout/session')
                ->addError($helper->__('Could not proceed with checkout since the order was not found.'));

            //redirect to the shopping cart
            $this->_redirect('checkout/cart');

            return;
        }

        $helper->log('Redirect Action for the order ' . $order->getRealOrderId());

        //make sure order still needs to be placed with CM Payments
        if ($order->getCmpaymentsChargeId() === null) {
            $internalErrorMsg = null;
            $redirectUrl      = null;
            try {
                // creation of the payment order
                /** @var Comaxx_CmPayments_Model_Api_Charge $charge */
                $charge      = Mage::getModel('cmpayments/api_Charge');
                $redirectUrl = $charge->createFromOrder($order);
            } catch (Comaxx_CmPayments_Model_Exception $exception) {
                $internalErrorMsg = 'CM Error: ' . $exception->getMessage();
            } catch (Exception $exception) {
                $internalErrorMsg = $exception->getMessage();
            }

            //handle possible error
            if ($internalErrorMsg) {
                $helper->log(
                    'Redirect Action error occurred during create of order ' . $order->getRealOrderId() . ':' . $internalErrorMsg,
                    Zend_Log::ERR
                );
                //Cancel order in Magento
                Mage::helper('cmpayments/order')
                    ->cancelOrder(
                        $this->__('We\'re sorry but an error occurred trying to create your order. We restored your shopping cart, and you may try again or come back later. We will keep your shopping cart saved if you\'re logged in.'),
                        $order->getRealOrderId()
                    );
                //redirect to the shopping cart
                $this->_redirect('checkout/cart');

                return;
            }

            $this->getResponse()->setBody(
                sprintf(
                    '<html><body><a href="%s">%s</a></body>', $redirectUrl,
                    $this->__('Redirecting to payment screen...')
                )
            );

            if (! $redirectUrl) {
                //payment method has no payment screen, set to checkout complete page and send new order mail if needed
                $redirectUrl = Mage::getUrl('checkout/onepage/success', array('_secure' => true));
                Mage::helper('cmpayments/order')->sendNewOrderMail($order);
            }

            $this->getResponse()->setRedirect($redirectUrl);
        } else {
            //redirect to the shopping cart
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Executed when a user returns from the payment screen after canceling the payment themselves
     *
     * @return void
     */
    public function cancelAction()
    {
        $this->updateOrder('Cancel Action');
    }

    /**
     * Action executed when payment fails
     *
     * @return void
     */
    public function failedAction()
    {
        $this->updateOrder('Expired Action', Zend_Log::ERR);
    }

    /**
     * Action executed when the payment process expires
     *
     * @return void
     */
    public function expiredAction()
    {
        $this->updateOrder('Expired Action');
    }

    /**
     * Action executed when a payment succeeds
     *
     * @return void
     */
    public function successAction()
    {
        $this->updateOrder('Success Action');
    }

    /**
     * Performs an update for the order extracted the latest CM data
     *
     * @param $logMsg      Message to log during execution of this update
     * @param $logSeverity Message severity for the update signaled by calling method
     *
     * @return void
     */
    protected function updateOrder($logMsg, $logSeverity = Zend_Log::INFO)
    {
        /** @var Comaxx_CmPayments_Helper_Data $helper */
        $helper = Mage::helper('cmpayments');

        //log message of the action, details are logged in cancelOrder function
        $helper->log($logMsg, $logSeverity);

        /** @var Comaxx_CmPayments_Helper_Order $orderHelper */
        $orderHelper = Mage::helper('cmpayments/order');
        //check if there is an order in request or session
        $orderId = $orderHelper->getActiveOrderId(Mage::app()->getRequest());

        if ($orderId) {
            $internalErrorMsg = null;

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

            try {
                $charge = Mage::getModel('cmpayments/api_Charge')->getCharge($order);
                if ($charge) {
                    //charge is successfully retrieved, use it to update the order
                    $order = $orderHelper->updateOrder($order, $charge);
                }
            } catch (Comaxx_CmPayments_Model_Exception $exception) {
                $internalErrorMsg = 'CM Error: ' . $exception->getMessage();
            } catch (Exception $exception) {
                $internalErrorMsg = $exception->getMessage();
            }

            if ($internalErrorMsg) {
                //an error occurred log internal message and place user notice
                $helper->log(
                    'An error occurred during update of order ' . $orderId . ':' . $internalErrorMsg,
                    Zend_Log::ERR
                );
                Mage::getSingleton('checkout/session')
                    ->addNotice($this->__('We\'re sorry but an error occurred trying to update your order.'));
            }


            //redirect to cart unless order has just been completed
            if (! $order->getTotalDue()) {
                //send new order mail if not done already
                $orderHelper->sendNewOrderMail($order);

                return $this->_redirect('checkout/onepage/success');
            }
        }

        $this->_redirect('checkout/cart');
    }
}