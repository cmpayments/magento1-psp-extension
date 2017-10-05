<?php

/**
 * Model for Observer
 *
 * @category Model
 * @package  Comaxx_CmPayments
 * @author   Comaxx <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Observer on configuration save of this module. Checks if requirements of this module are met, if not the module
     * is disabled.
     *
     * @return void
     */
    public function configSave($observer)
    {
        $config = $observer->getObject();
        if ($config->getSection() === 'cmpayments') {
            $groups              = $config->getGroups();
            $missingRequirements = null;
            if (! Mage::helper('cmpayments/config')->checkPluginRequirements($missingRequirements)) {
                //requirements not met, display error
                $missing = implode(
                    ', ', array_map(
                        function ($v, $k) {
                        return strtoupper(sprintf("%s (%s)", $k, $v));
                        }, $missingRequirements, array_keys($missingRequirements)
                    )
                );

                //disable module
                $groups['general']['fields']['active'] = 0;
                Mage::getSingleton('core/session')
                    ->addError('Failed to activate CM Payments module. The following libraries/mods are missing: \'' . $missing . '\' or higher');
            }

            /** @var Comaxx_CmPayments_Helper_Api_Abstract $helper */
            $helper = Mage::helper('cmpayments/api_Abstract');

            $this->setActivePaymentMethods($helper, $groups);

            //extract iDEAL issuers from API
            $groups['cmpayments_idl']['fields']['issuers']['value'] = json_encode($helper->getIdealIssuers());

            //update groups with possible modified config values
            $config->setGroups($groups);
        }
    }

    /**
     * Update config settings of payment methods according to merchant account
     *
     * @param Comaxx_CmPayments_Helper_Api_Abstract $helper       Helper class for API
     * @param Mixed                                 $configGroups List of configuration settings
     */
    protected function setActivePaymentMethods($helper, &$configGroups)
    {
        $paymentMethods = $helper->getPaymentMethods();

        /** @var Comaxx_CmPayments_Helper_Config $configHelper */
        $configHelper  = Mage::helper('cmpayments/config');
        $configMethods = $configHelper->getPaymentMethodsByName();

        $paymentMethodsByKey = array();

        foreach ($paymentMethods as $paymentMethod) {
            if (array_key_exists($paymentMethod->getName(), $configMethods)) {
                $paymentMethodsByKey[$configMethods[$paymentMethod->getName()]['key']] = $paymentMethod;
            }
        }

        foreach ($configGroups as $key => $configGroup) {
            if (strpos($key, 'cmpayments_') === 0) {
                if (array_key_exists(
                    $key,
                    $paymentMethodsByKey
                ) && ! empty($configGroup['fields']['active']['value'])
                ) {
                    $paymentMethod = $paymentMethodsByKey[$key];
                    if (! $paymentMethod->getApiAccess()) {
                        $configGroups[$key]['fields']['active']['value'] = 0;

                        Mage::getSingleton('adminhtml/session')
                            ->addError('Payment method "' . $paymentMethod->getName() . '" is not available in your account.');
                    }
                }
            }
        }
    }
}