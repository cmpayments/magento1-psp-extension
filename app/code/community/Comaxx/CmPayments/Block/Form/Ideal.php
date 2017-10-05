<?php

/**
 * Ideal issuers block
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Block_Form_Ideal extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        //set template with issuers to be displayed
        $issuers = json_decode(Mage::helper('cmpayments/config')->getPaymentMethodItem('cmpayments_idl', 'issuers'));
        $this->setTemplate('comaxx_cmpayments/form/ideal.phtml')->setIssuers($issuers);
    }
}