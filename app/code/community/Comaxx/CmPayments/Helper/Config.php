<?php

/**
 * Helper class for reading config values
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Helper_Config extends Mage_Core_Helper_Abstract
{
    //define constants for config groups
    const GROUP_GENERAL = 'general', GROUP_MERCHANT = 'merchant';

    private $_general_cfg, $_merchant_cfg, $_payment;

    /**
     * Constructor used to load config groups
     *
     * @return Comaxx_CmPayments_Helper_Config object
     */
    public function __construct()
    {
        $config              = Mage::getStoreConfig('cmpayments');
        $this->_general_cfg  = $config['general'];
        $this->_merchant_cfg = $config['merchant_account'];
        $this->_payment      = Mage::getStoreConfig('payment');
    }

    /**
     * Checks if the plugin is enabled
     *
     * @return boolean true if plugin is enabled, otherwise false
     */
    public function isActive()
    {
        return ( ! empty($this->_general_cfg['active']) && ($this->_general_cfg['active'] === '1'));
    }

    /**
     * Retrieves the Merchant settings
     *
     * @param int $store_id contains store id if data should be extracted from store other then current store.
     *
     * @return array containing the configured username and password
     */
    public function getMerchant($store_id = null)
    {
        $merchant_config = $this->_merchant_cfg;
        //in case different store info is required, use other config data
        if ($store_id !== null) {
            $merchant_config = Mage::getStoreConfig('cmpayments/merchant_account', $store_id);
        }

        //get the settings belonging to correct module mode
        return array(
            'oauth_consumer_key' => empty($merchant_config['oauth_consumer_key']) ? null : $merchant_config['oauth_consumer_key'],
            'oauth_secret'       => empty($merchant_config['oauth_secret']) ? null : $merchant_config['oauth_secret'],
        );
    }

    /**
     * Retrieves an item in the specified group
     *
     * @param string $key   Configuration key for the desired item
     * @param string $group Configuration group to find item in
     *
     * @return object Returns object if found otherwise returns null
     */
    public function getItem($key, $group = null)
    {
        $result = null;
        switch ($group) {
            case Comaxx_CmPayments_Helper_Config::GROUP_GENERAL:
                $result = empty($this->_general_cfg[$key]) ? null : $this->_general_cfg[$key];
                break;
            case Comaxx_CmPayments_Helper_Config::GROUP_MERCHANT:
                $result = empty($this->_merchant_cfg[$key]) ? null : $this->_merchant_cfg[$key];
                break;
            case null:
                //no group specified, attempt to find in config using only key
                $result = Mage::getStoreConfig('cmpayments/' . $key);
                break;
        }

        return $result;
    }

    /**
     * Retrieves configuration for the requested payment method
     *
     * @param string $payment_method Key for the payment method
     * @param string $key            Optional key for an item within the payment method to be retrieved
     *                               (leave empty for all items related to the payment method)
     *
     * @return object In case $key is defined returns only the specific setting,
     * if $key is empty the payment method group is returned. In case no match is made returns null.
     */
    public function getPaymentMethodItem($payment_method, $key = null)
    {
        //build required path section
        $path = 'payment/' . $payment_method;

        //check if optional path section needs to be included
        if ( ! empty($key)) {
            $path .= '/' . $key;
        }

        return Mage::getStoreConfig($path);
    }

    /**
     * Get the CM Payment methods from config arranged by name
     *
     * @return array Payment methods from config arranged by name
     */
    public function getPaymentMethodsByName()
    {
        $methods = [];
        if ($this->_payment) {
            foreach ($this->_payment as $key => $method) {
                if (strpos($key, 'cmpayments') !== false && array_key_exists('cmname', $method)) {
                    $method['config_path']      = 'payment/' . $key;
                    $method['key']              = $key;
                    $methods[$method['cmname']] = $method;
                }
            }
        }

        return $methods;
    }

    /**
     * Checks plugin requirements, if not met the plugin will be disabled (no user notice if given in this method)
     *
     * @param array &$failedRequirements List of failed requirements (can be used by calling code to display error
     *                                   message)
     *
     * @return boolean True if plugin requirments are met, false if not
     */
    public function checkPluginRequirements(&$failedRequirements)
    {
        $requirementsMet    = true;
        $failedRequirements = array();
        $isActivePlugin     = $this->getItem('active', Comaxx_CmPayments_Helper_Config::GROUP_GENERAL);

        //only need to perform checks if plugin is active
        if ($isActivePlugin) {

            $versionRequirements = Mage::getConfig()->getNode('default/cmpayments/version/requirements')->asArray();
            $versionChecks       = Mage::getConfig()->getNode('default/cmpayments/version/checks')->asArray();

            foreach ($versionRequirements as $application => $requiredVersion) {

                $command        = $versionChecks[$application];
                $currentVersion = 'not found';

                if (is_array($command)) {
                    if ( ! empty($command) && isset($command['check']) && function_exists($command['check']) && isset($command['argument'])) {
                        $function       = call_user_func($command['check']);
                        $currentVersion = $function[$command['argument']];
                    }
                } else {
                    if (function_exists($command)) {
                        $currentVersion = call_user_func($command);
                    }
                }

                if (version_compare($currentVersion, $requiredVersion, '<')) {
                    $failedRequirements[$application] = $currentVersion;
                    $requirementsMet                  = false;
                }
            }

            //in case requirements are not met disable plugin
            if ( ! $requirementsMet) {
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('cmpayments/general/active', "0", 'default', 0);
            }
        }

        return $requirementsMet;
    }
}