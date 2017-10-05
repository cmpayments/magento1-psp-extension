<?php

/**
 * Helper class for order processing
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Helper_Order extends Mage_Core_Helper_Abstract
{
    /**
     * Get current order id provied by request or session
     *
     * @param mixed $request Request for current page
     *
     * @return null|string
     */
    public function getActiveOrderId($request = null)
    {
        $orderId = null;

        //first check id in request params
        if ($request !== null) {
            $orderId = $request->getParam('id');
        }

        //if there is no id provided then get it from session
        if (empty($orderId)) {
            $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        }

        return $orderId;
    }

    /**
     * Cancels the last order for the current user.
     *
     * @param string $cancelMsg Message to show the user after he has been redirected to the cart
     * @param string $orderId   IncrementId for order to cancel
     *
     * @return void
     */
    public function cancelOrder($cancelMsg, $orderId)
    {
        if ($orderId != null) {
            Mage::helper('cmpayments')->log('Cancellation of order ' . $orderId . ', message for user: ' . $cancelMsg);

            //Acquire lock. Not checking result: if locked failed still continue since customer is waiting on this action
            $lock = Comaxx_CmPayments_Model_Locking::getLock('order_' . $orderId);

            //get the latest version of the order after lock is acquired, force reload
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

            //remove the order from the session
            Mage::getSingleton('customer/session')->setLastOrderId(null);

            //cancel the order so it isn't open anymore
            $order->cancel()->save();

            //restore the last quote (cart)
            $this->restoreLastQoute();

            //set the given message to be shown on the next page load
            Mage::getSingleton('checkout/session')->addNotice($cancelMsg);

            //release lock for cleanup
            Comaxx_CmPayments_Model_Locking::releaseLock($lock);
        }
    }

    /**
     * Cancels the last order for the current user.
     *
     * @param Mage_Sales_Model_Order $order  Order to update
     * @param array                  $charge CM charge data belonging to the provided order
     *
     * @return Mage_Sales_Model_Order Updated order
     */
    public function updateOrder(Mage_Sales_Model_Order $order, $charge)
    {
        $orderId = $order->getRealOrderId();
        //first check if order belongs to charge
        if ($order->getCmpaymentsChargeId() !== $charge['charge_id']) {
            Mage::throwException("Update order mismatch for charge [{$charge['charge_id']}] and order [{$orderId}]");
        }

        /** @var Comaxx_CmPayments_Helper_Data $helper */
        $helper = Mage::helper('cmpayments');

        //do not change an order that is already set to complete state
        if ($order->getState() === Mage_Sales_Model_Order::STATE_COMPLETE) {
            $helper->log("CM Payments: order [$orderId] received an update that was ignored (order is already on state 'complete'). ");

            return $order;
        }

        switch ($charge['status']) {
            case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_CANCELLED:
            case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_FAILED:
            case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_EXPIRED:
                $userMsg = $this->getMessageByStatus($charge['status']);

                //also add message in order and cancel order
                $order->addStatusHistoryComment("CM Payments: cancelled order due to charge status '{$charge['status']}'.")
                      ->save();
                $this->cancelOrder($userMsg, $orderId);

                $this->verifyActiveOrder($order, $charge['status']);
                break;
            case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_SUCCESS:

                $order = $this->processSuccessCharge($charge, $orderId);

                break;
            case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_OPEN:
            default:
                //do nothing, only log that an update request was triggered
                $order->addStatusHistoryComment("CM Payments: update triggered without changes for charge status '{$charge['status']}'.")
                      ->save();
                break;
        }

        return $order;
    }

    /**
     * Process the charge data and update belonging order if needed
     *
     * @param array  $charge  Charge data belonging to order
     * @param string $orderId Id of the order to process charge data for
     *
     * @return Mage_Sales_Model_Order Updated order
     */
    protected function processSuccessCharge($charge, $orderId)
    {
        /** @var Comaxx_CmPayments_Helper_Data $helper */
        $helper = Mage::helper('cmpayments');

        //Acquire lock. Not checking result: if locked failed still continue since customer is waiting on this action
        $lock = Comaxx_CmPayments_Model_Locking::getLock('order_' . $orderId);

        //get the latest version of the order after lock is acquired, force reload
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        //first make sure order and charge currency are the same
        if (isset($charge['currency']) && $charge['currency'] !== $order->getBaseCurrencyCode()) {
            //release lock for cleanup
            Comaxx_CmPayments_Model_Locking::releaseLock($lock);
            Mage::throwException("Currency '{$order->getBaseCurrencyCode()}' for order {$order->getRealOrderId()} did not match currency '{$charge['currency']}' in charge.");

            return;
        }

        $order = $this->ensureActiveOrder($order);

        $captured = $charge['amount'];
        $due      = $order->getTotalDue();

        if ($due == 0) {
            $helper->log("Order [$orderId] due amount is already 0 ($due), thus it does not need to be registered.");
        } else {
            //capture amount if captured amount matches or is higher but not when due is already 0
            $order->addStatusHistoryComment("CM Payments: received success update for amount of [{$captured}]. attempting to capture");
            $order->getPayment()->registerCaptureNotification($captured);
            $order->save();
        }

        //release lock for cleanup
        Comaxx_CmPayments_Model_Locking::releaseLock($lock);

        $this->verifyActiveOrder(
            $order, Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_SUCCESS,
            'Your payment has been successfully processed, please return to the device where you have placed the order.'
        );

        return $order;
    }

    /**
     * Verify given order with active order
     *
     * @param Mage_Sales_Model_Order $order   The order to verify
     * @param string                 $status  The status of the charge
     * @param string                 $message The message to show according to the status
     */
    protected function verifyActiveOrder(Mage_Sales_Model_Order $order, $status, $message = null)
    {
        // When no active orderId is available or it's not the same as the given order id
        // We will check if the payment method is iDEAL QR and assume that the payment was done on another device
        if (! $this->getActiveOrderId() || $this->getActiveOrderId() !== $order->getRealOrderId()) {
            /** @var Comaxx_CmPayments_Model_Method_Abstract $paymentModel */
            $paymentModel = Mage::helper('cmpayments')->getPaymentModelFromOrder($order);

            if ($paymentModel instanceof Comaxx_CmPayments_Model_Method_Idealqr) {
                switch ($status) {
                    case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_SUCCESS:
                        Mage::getSingleton('checkout/session')->addSuccess($this->__($message));
                        break;

                    case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_CANCELLED:
                    case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_FAILED:
                    case Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_EXPIRED:
                        Mage::getSingleton('checkout/session')
                            ->addNotice($this->__('You have placed the order on another device, please return to that device to place the order again or continue shopping.'));
                        break;
                }
            }
        }
    }

    /**
     * Sends a new order mail unless it is already sent
     *
     * @param Mage_Sales_Model_Order $order Order to send mail for
     *
     * @return void
     */
    public function sendNewOrderMail(&$order)
    {
        //send email if not done yet
        if (! $order->getEmailSent()) {
            //allow code outside this module to catch the event and block sending mail if desired
            $editable_object           = new StdClass;
            $editable_object->sendmail = true;

            Mage::dispatchEvent(
                'cmpayments_send_new_order_mail', array(
                'action' => $editable_object,
                'order'  => $order,
                )
            );
            if ($editable_object->sendmail) {
                $order->sendNewOrderEmail();
                $order->save();
            }
        }
    }

    /**
     * Restores the qoute (cart) with the content of the last order
     *
     * @return void
     */
    public function restoreLastQoute()
    {
        /* @var $session Mage_Checkout_Model_Session */
        $session     = Mage::getSingleton('checkout/session');
        $lastQuoteId = $session->getLastQuoteId();
        $session->clear();
        $session->getQuote()->load($lastQuoteId)->setIsActive(1);

        /* @var $cart Mage_Checkout_Model_Cart */
        $cart = Mage::getSingleton('checkout/cart');
        if ($cart->getItemsCount()) {
            $cart->init();
            $cart->save();
        }
    }

    /**
     * Updates a canceled order so that it is active again
     *
     * @param Mage_Sales_Model_Order $order Canceled order to be made active again
     *
     * @return Mage_Sales_Model_Order Active order
     */
    public function ensureActiveOrder($order)
    {
        if ($order->getState() === Mage_Sales_Model_Order::STATE_CANCELED) {
            //keep track of product updates to update stock information for uncancel
            $productUpdates = array();
            //first reset order items canceled qty to 0
            $items           = $order->getItemsCollection();
            $canceledItemQty = array();
            foreach ($items as $item) {
                $parentItem                      = $item->getParentItem();
                $canceledItemQty[$item->getId()] = $item->getQtyCanceled();

                if ($parentItem && $parentItem->getProductType() == 'configurable') {
                    $parentId = $parentItem->getId();
                    if (isset($canceledItemQty[$parentId])) {
                        //parent has already been handled, use original canceled qty
                        $canceled = $canceledItemQty[$parentId];
                    } else {
                        $canceled = $parentItem->getQtyCanceled();
                    }
                } elseif ($parentItem && $parentItem->getProductType() == 'bundle') {
                    //simple products within bundle will not be tracked as canceled by magento, so simply use the original ordered qty
                    $canceled = $item->getQtyOrdered();
                } else {
                    $canceled = $item->getQtyCanceled();
                }

                if ($canceled > 0) {
                    $item->setQtyCanceled(0);

                    $productUpdates[$item->getProductId()] = array('qty' => $canceled);
                    if ($item->getProductType() == 'simple') {
                        $stockItem = Mage::getModel('cataloginventory/stock_item')
                                         ->loadByProduct($item->getProductId());
                        //set out of stock if product has no more items
                        if (((int)$stockItem->getQty() - (int)$canceled) <= (int)$stockItem->getMinQty()) {
                            $stockItem->setIsInStock(false)->setStockStatusChangedAutomaticallyFlag(true);
                        }

                        $stockItem->save();
                    }
                }

                $item->setTaxCanceled(0);
                $item->setHiddenTaxCanceled(0);
            }

            //trigger update of stock for affected products
            Mage::getSingleton('cataloginventory/stock')->registerProductsSale($productUpdates);
            $items->save();

            //update order itself by resetting all canceled amounts
            $order->setBaseDiscountCanceled(0)
                  ->setBaseShippingCanceled(0)
                  ->setBaseSubtotalCanceled(0)
                  ->setBaseTaxCanceled(0)
                  ->setBaseTotalCanceled(0)
                  ->setDiscountCanceled(0)
                  ->setShippingCanceled(0)
                  ->setSubtotalCanceled(0)
                  ->setTaxCanceled(0)
                  ->setTotalCanceled(0);
        }

        return $order;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     *
     * @return boolean True if refund succeeded otherwise false
     * @throws Exception In case payment refund requirements are not met
     */
    public function refundPayment($order)
    {
        $helper    = Mage::helper('cmpayments');
        $payment   = $order->getPayment();
        $paymentId = $payment->getCmpaymentsPaymentId();
        if (! $paymentId) {
            Mage::throwException('CM Payment id not found');
        }

        /** @var Comaxx_CmPayments_Model_Method_Abstract $paymentModel */
        $paymentModel = $helper->getPaymentModelFromOrder($order);
        if (! $paymentModel) {
            Mage::throwException('CM Payment model could not be found/loaded');
        }

        //make sure payment method supports refunds
        if (! $paymentModel->canRefund()) {
            Mage::throwException('CM Payment model does not support refunds');
        }

        try {
            $responseData = Mage::getModel('cmpayments/api_Refund')
                                ->refundPayment($paymentId, $payment->getAmountOrdered(), $order->getOrderCurrencyCode());

            if (array_key_exists('refund_id', $responseData) && array_key_exists('status', $responseData)) {
                //check if status is not failed:
                if ($responseData['status'] === Comaxx_CmPayments_Helper_Api_Abstract::REFUND_STATUS_FAILED) {
                    $helper->log("CM refund with id {$responseData['refund_id']} failed for order {$order->getRealOrderId()}");
                    Mage::throwException('The CM refund returned with status failed.');
                }

                //update payment with refund id and get status for result
                $payment->setCmpaymentsRefundId($responseData['refund_id']);
                $payment->save();

                return true;
            }
        } catch (Comaxx_CmPayments_Model_Exception $exception) {
            $helper->log('CM API Error during refund: ' . $exception->getMessage());
            Mage::throwException('An error occured during the execution of the CM refund call.');
        }

        return false;
    }

    /**
     * Gets the display message belonging to the CM order status.
     *
     * @param $status CM order status
     *
     * @return string Message to display
     */
    public function getMessageByStatus($status)
    {
        /** @var Comaxx_CmPayments_Helper_Data $helper */
        $helper = Mage::helper('cmpayments');

        $userMsg = $helper->__('Your payment has expired. We restored your shopping cart, and you may try again or come back later. We will keep your shopping cart saved if you\'re logged in.');

        if ($status === Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_CANCELLED) {
            $userMsg = $helper->__('Your payment was cancelled upon your request. You can still place your order again later.');
        } else if ($status === Comaxx_CmPayments_Helper_Api_Abstract::ORDER_STATUS_FAILED) {
            $userMsg = $helper->__('An error occurred during the payment process. We restored your shopping cart, and you may try again or come back later. We will keep your shopping cart saved if you\'re logged in.');
        }

        return $userMsg;
    }

}