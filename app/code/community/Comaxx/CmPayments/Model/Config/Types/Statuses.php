<?php

/**
 * Source model for order statuses
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Config_Types_Statuses extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
    //override parent variable to allow all statusses to be seen/selected (not just Magento default)
    protected $_stateStatuses = null;
}