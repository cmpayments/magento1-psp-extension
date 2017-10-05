<?php

/**
 * Source model for all countries + int + eur
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Config_Types_Countries
{
    public function toOptionArray()
    {
        $helper = Mage::helper('cmpayments');

        return array(
                   array('value' => 'INT', 'label' => $helper->__('International')),
                   array('value' => 'EUR', 'label' => $helper->__('Europe')),
               ) + Mage::getModel('adminhtml/system_config_source_country')->toOptionArray();
    }
}