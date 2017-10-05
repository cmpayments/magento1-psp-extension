<?php

/**
 * Payment method class for Afterpay
 */
class Comaxx_CmPayments_Model_Method_Afterpay extends Comaxx_CmPayments_Model_Method_Abstract
{
    protected $_code = 'cmpayments_ap';
    protected $_formBlockType = 'cmpayments/form_afterpay';

    private $_vatCategories = array();

    const XML_CONFIG_PATH_VAT_CATEGORY_HIGH = 'cmpayments/cmpayments_ap/vat_category_high', XML_CONFIG_PATH_VAT_CATEGORY_LOW = 'cmpayments/cmpayments_ap/vat_category_low', XML_CONFIG_PATH_VAT_CATEGORY_MIDDLE = 'cmpayments/cmpayments_ap/vat_category_middle', XML_CONFIG_PATH_VAT_CATEGORY_ZERO = 'cmpayments/cmpayments_ap/vat_category_zero';

    const VAT_CATEGORY_HIGH = 1, VAT_CATEGORY_LOW = 2, VAT_CATEGORY_ZERO = 3, VAT_CATEGORY_NONE = 4, VAT_CATEGORY_MIDDLE = 5;

    public function __construct()
    {
        parent::__construct();

        if ($tax_id = Mage::getStoreConfig(self::XML_CONFIG_PATH_VAT_CATEGORY_HIGH)) {
            $this->_vatCategories[$tax_id] = self::VAT_CATEGORY_HIGH;
        }

        if ($tax_id = Mage::getStoreConfig(self::XML_CONFIG_PATH_VAT_CATEGORY_LOW)) {
            $this->_vatCategories[$tax_id] = self::VAT_CATEGORY_LOW;
        }

        if ($tax_id = Mage::getStoreConfig(self::XML_CONFIG_PATH_VAT_CATEGORY_MIDDLE)) {
            $this->_vatCategories[$tax_id] = self::VAT_CATEGORY_MIDDLE;
        }

        if ($tax_id = Mage::getStoreConfig(self::XML_CONFIG_PATH_VAT_CATEGORY_ZERO)) {
            $this->_vatCategories[$tax_id] = self::VAT_CATEGORY_ZERO;
        }
    }

    protected function getPaymentDetails(Mage_Sales_Model_Order $order)
    {
        /** @var Comaxx_CmPayments_Helper_Currency $currencyHelper */
        $currencyHelper = Mage::helper('cmpayments/currency');
        $currency       = $order->getOrderCurrencyCode();

        $config = Mage::getStoreConfig('cmpayments/cmpayments_ap');

        $data = array(
            'portfolio_id'        => $config['portfolio_id'],
            'password'            => $config['password'],
            'bank_account_number' => $config['bank_account_number'],
            'ip_address'          => Mage::helper('core/http')->getRemoteAddr(),
            'order_number'        => $order->getIncrementId(),
            'invoice_number'      => $order->getIncrementId(),
            'total_order_amount'  => $currencyHelper->toMinorUnits($order->getGrandTotal(), $currency),
            'bill_to_address'     => $this->getBillToAddress($order),
            'ship_to_address'     => $this->getShipToAddress($order),
            'order_line'          => $this->getOrderLines($order),
        );

        return $data;
    }

    /**
     * Get order lines for given order
     *
     * @param Mage_Sales_Model_Order $order The order to get the order line for
     *
     * @return array
     */
    protected function getOrderLines(Mage_Sales_Model_Order $order)
    {
        $items = $order->getAllVisibleItems();

        $lines = array();

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($items as $item) {
            $lines[] = array(
                'article_description' => $item->getName(),
                'vat_category'        => $this->_getVatCategory($item),
                'article_id'          => $item->getSku(),
                'quantity'            => $item->getQtyOrdered(),
                'unit_price'          => $item->getPriceInclTax(),
                'net_unit_price'      => $item->getPrice(),
            );
        }

        return $lines;
    }

    /**
     * Get the bill to address data from the order
     *
     * @param Mage_Sales_Model_Order $order The order to get the bill to address from
     *
     * @return array The Bill To Address data
     */
    protected function getBillToAddress(Mage_Sales_Model_Order $order)
    {
        $billingAddress        = $order->getBillingAddress();
        $additionalInformation = $this->_getAdditionalInformation($order);

        return array(
            'city'                  => $billingAddress->getCity(),
            'street_name'           => $additionalInformation->getBillingStreet(),
            'house_number'          => $additionalInformation->getBillingHousenumber(),
            'house_number_addition' => $additionalInformation->getBillingHousenumberAddition(),
            'iso_country_code'      => $billingAddress->getCountryId(),
            'postal_code'           => $billingAddress->getPostcode(),
            'region'                => $billingAddress->getRegion(),
            'reference_person'      => $this->getReferencePerson($order),
        );
    }

    /**
     * Get the bill to address data from the order
     *
     * @param Mage_Sales_Model_Order $order The order to get the bill to address from
     *
     * @return array The Bill To Address data
     */
    protected function getShipToAddress(Mage_Sales_Model_Order $order)
    {
        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress->getSameAsBilling()) {
            return $this->getBillToAddress($order);
        }

        $additionalInformation = $this->_getAdditionalInformation($order);

        return array(
            'city'                  => $shippingAddress->getCity(),
            'street_name'           => $additionalInformation->getShippingStreet(),
            'house_number'          => $additionalInformation->getShippingHousenumber(),
            'house_number_addition' => $additionalInformation->getShippingHousenumberAddition(),
            'iso_country_code'      => $shippingAddress->getCountryId(),
            'postal_code'           => $shippingAddress->getPostcode(),
            'region'                => $shippingAddress->getRegion(),
            'reference_person'      => $this->getReferencePerson($order),
        );
    }

    protected function getReferencePerson(Mage_Sales_Model_Order $order)
    {
        $additionalInformation = $this->_getAdditionalInformation($order);

        $dateOfBirth = implode(
            '-', array(
            $additionalInformation->getReferenceDobDay(),
            $additionalInformation->getReferenceDobMonth(),
            $additionalInformation->getReferenceDobYear(),
            )
        );

        return array(
            'last_name'      => $additionalInformation->getReferenceLastname(),
            'initials'       => $additionalInformation->getReferenceInitials(),
            'email_address'  => $additionalInformation->getReferenceEmail(),
            'phone_number_1' => $additionalInformation->getReferencePhonenumber1(),
            'phone_number_2' => $additionalInformation->getReferencePhonenumber2(),
            'gender'         => $additionalInformation->getReferenceGender() === '1' ? 'M' : 'V',
            'date_of_birth'  => date('d-m-Y', strtotime($dateOfBirth)),
            'iso_language'   => $additionalInformation->getReferenceLanguage(),
        );
    }

    /**
     * Get the VAT Category based on tax class with percentage as fallback
     *
     * 0      = VAT_CATEGORY_ZERO
     * 1 - 10 = VAT_CATEGORY_LOW
     * 10+    = VAT_CATEGORY_HIGH
     *
     * @param Mage_Sales_Model_Order_Item $item The Order item to get the VAT Category for
     *
     * @return int|mixed
     */
    private function _getVatCategory(Mage_Sales_Model_Order_Item $item)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $item->getProduct();

        if ($taxClassId = $product->getTaxClassId()) {
            if (array_key_exists($taxClassId, $this->_vatCategories)) {
                return $this->_vatCategories[$taxClassId];
            }

            /** @var Mage_Tax_Model_Calculation $taxCalculation */
            $taxCalculation = Mage::getModel('tax/calculation');
            $request        = $taxCalculation->getRateRequest();
            $percent        = $taxCalculation->getRate($request->setProductClassId($taxClassId));

            if ($percent === 0) {
                return self::VAT_CATEGORY_ZERO;
            }

            if ($percent > 0 && $percent <= 10) {
                return self::VAT_CATEGORY_LOW;
            }

            if ($percent > 10) {
                return self::VAT_CATEGORY_HIGH;
            }
        }

        return self::VAT_CATEGORY_NONE;
    }

    /**
     * Get the additional payment information from the order
     *
     * @param Mage_Sales_Model_Order $order The order to get the additional information from
     *
     * @return Varien_Object The additional information
     */
    private function _getAdditionalInformation(Mage_Sales_Model_Order $order)
    {
        return new Varien_Object($order->getPayment()->getAdditionalInformation());
    }
}