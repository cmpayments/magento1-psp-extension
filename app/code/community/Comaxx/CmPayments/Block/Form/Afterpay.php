<?php

/**
 * Afterpay block
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Block_Form_Afterpay extends Mage_Payment_Block_Form
{
    protected $quote;

    /**
     * @var string Matches possible house numbers + additions
     *
     * When applied to the following example:
     * Derpaderpastreet 1, 12, 123, 1a, 1bv, 23ab, 1-a, 1-bv, 23-ab Derpaderpastreet
     *
     * Will match the numbers + letter additions (with dash) while leaving out
     * the street. This should cover most international street formats, even when
     * house numbers may be placed in front of the street.
     */
    const NUMBER_PATTERN = "/\b(?P<numbers>[0-9]+[\\s-]*[a-z,0-9]{0,2}\\b)\b/i";

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('comaxx_cmpayments/form/afterpay.phtml');
        $this->quote = Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Return true if the checkout is done as a guest
     *
     * @return bool
     */
    public function isGuestCheckout()
    {
        return ! $this->quote->getCustomerId();
    }

    /**
     * Return true if the billing and shipping addresses are the same
     *
     * @return bool
     */
    public function isBillingShippingSame()
    {
        return $this->quote->getShippingAddress()->getSameAsBilling();
    }

    /**
     * Gets the possible street number matches excluding common stuff which might also
     * be present in a street name (1st, 2nd etc).
     *
     * @param string $streetFull Full street name
     *
     * @return array Array with matches in the given string
     */
    private function _getStreetNumberMatches($streetFull)
    {
        preg_match_all(self::NUMBER_PATTERN, $streetFull, $matches, PREG_SET_ORDER);

        $finalMatches = array();

        // Filter out 1st, 2nd, 3rd, 4th
        foreach ($matches as $numberEntry) {
            $number = $numberEntry['numbers'];
            $match  = substr(trim($number), -2);
            if ($match !== 'st' && $match !== 'nd' && $match !== 'rd' && $match !== 'th') {
                $finalMatches[] = $number;
            }
        }

        return $finalMatches;
    }

    /**
     * Gets the combined street name from an array
     *
     * @param mixed $streetFull Full street name
     *
     * @return string Street name if $street_full was an array, otherwise returns original parameter $street_full
     */
    private function _getFullStreet($streetFull)
    {
        if (is_array($streetFull)) {
            $streetCombined = array();

            //combine all adress lines
            foreach ($streetFull as $streetLine) {
                if (is_string($streetLine)) {
                    $streetCombined[] = trim($streetLine);
                }
            }

            if (empty($streetCombined)) {
                //empty array found, return empty
                return null;
            }

            $streetFull = implode(' ', $streetCombined);
        }

        return $streetFull;
    }

    /**
     * Gets just the name in the street+number magento stores
     *
     * @param string $streetFull Full street name
     *
     * @return string Street name
     */
    private function _getStreetName($streetFull)
    {
        $streetFull = $this->_getFullStreet($streetFull);

        if (! is_string($streetFull)) {
            return $streetFull;
        }

        $numbers = $this->_getStreetNumberMatches($streetFull);

        // From here we figure out what is just the street name using the matches
        foreach ($numbers as $number) {
            // Filter out only the numbers taking into account boundries, and dot/commas after it (which define a boundry)
            $streetFull = preg_replace('/\b' . $number . '\b[,\.]?/i', '', $streetFull);
        }

        return $streetFull;
    }

    /**
     * Tries to get the street number, if an invalid value is passed it will return false
     *
     * @param string $streetFull Full street name
     *
     * @return mixed false if no match could be made or the first viable number existing in the given string
     */
    private function _getStreetNumber($streetFull)
    {
        $streetFull = $this->_getFullStreet($streetFull);

        if (! is_string($streetFull)) {
            return $streetFull;
        }

        $matches = $this->_getStreetNumberMatches($streetFull);

        if (count($matches) > 0) {
            // First viable number is used, this may be wrong but it is impossible to make a better match
            return (int)$matches[0];
        } else {
            return false;
        }
    }

    /**
     * Tries to get an addition from the house number if it cannot make a match it will return false
     *
     * @param string $streetFull Full street name
     *
     * @return mixed false if no match could be made or the addition part from the first viable number in the given
     *               string
     */
    private function _getStreetNumberAddition($streetFull)
    {
        $streetFull = $this->_getFullStreet($streetFull);

        if (! is_string($streetFull)) {
            return $streetFull;
        }

        $matches = $this->_getStreetNumberMatches($streetFull);

        if (count($matches) > 0) {
            // First viable number is used, this may be wrong but it is impossible to make a better match
            return preg_replace('/^[0-9\\s-]+/', '', $matches[0]);
        } else {
            return false;
        }
    }

    public function getLanguages()
    {
        return array(
            "NL"    => $this->__('Dutch'),
            "DE"    => $this->__('German'),
            "NL-BE" => $this->__('Dutch (Belgium)'),
            "FR-BE" => $this->__('French (Belgium)'),
        );
    }

    /**
     * Returns the customers lastname from the billing (customer) street address
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->quote->getCustomer()->getEmail();
    }

    /**
     * Returns the customers lastname from the billing (customer) street address
     *
     * @return string
     */
    public function getCustomerLastname()
    {
        return $this->quote->getBillingAddress()->getLastname();
    }

    /**
     * Returns the customers phone number from the billing (customer) street address
     *
     * @return string
     */
    public function getCustomerPhone()
    {
        return $this->quote->getBillingAddress()->getTelephone();
    }

    /**
     * Get the street from the billing (customer) street address
     *
     * @return string Will always return a string, possibly without the house number which we try to filter out
     */
    public function getCustomerStreet()
    {
        return $this->_getStreetName($this->quote->getBillingAddress()->getStreet());
    }

    /**
     * Get the house number from the billing (customer) street address
     *
     * @return mixed false if no match can be made or a possible number
     */
    public function getCustomerHousenumber()
    {
        return $this->_getStreetNumber($this->quote->getBillingAddress()->getStreet());
    }

    /**
     * Get the addition to a house number if it exists from the billing (customer) address
     *
     * @return mixed false if no match can be made or the addition string
     */
    public function getCustomerHousenumberAddition()
    {
        return $this->_getStreetNumberAddition($this->quote->getBillingAddress()->getStreet());
    }

    /**
     * Get gender if it exists from the customer
     *
     * @return string|null Gender of the customer if available
     */
    public function getCustomerGender()
    {
        if ($this->quote->getCustomerId()) {
            $customer = $this->quote->getCustomer();

            return $customer->getGender();
        }

        return null;
    }

    /**
     * Get gender if it exists from the customer
     *
     * @return string|null Gender of the customer if available
     */
    public function getCustomerDob()
    {
        if ($this->quote->getCustomerId()) {
            $customer = $this->quote->getCustomer();

            return strtotime($customer->getDob());
        }

        return null;
    }

    /**
     * Get the street from the shipping (delivery) street address
     *
     * @return string Will always return a string, possibly without the house number which we try to filter out
     */
    public function getShippingStreet()
    {
        return $this->_getStreetName($this->quote->getShippingAddress()->getStreet());
    }

    /**
     * Get the house number from the shipping (delivery) street address
     *
     * @return mixed false if no match can be made or a possible number
     */
    public function getShippingHousenumber()
    {
        return $this->_getStreetNumber($this->quote->getShippingAddress()->getStreet());
    }

    /**
     * Get the addition to a house number if it exists from the shipping (delivery) street address
     *
     * @return mixed false if no match can be made or the addition string
     */
    public function getShippingHousenumberAddition()
    {
        return $this->_getStreetNumberAddition($this->quote->getShippingAddress()->getStreet());
    }

    /**
     * Returns the customers phone number from the shipping (delivery) street address
     *
     * @return string
     */
    public function getShippingPhone()
    {
        return $this->quote->getShippingAddress()->getTelephone();
    }
}