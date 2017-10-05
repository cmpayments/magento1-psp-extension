<?php

/**
 * Source model for CreditCard issuers
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Config_Types_CcIssuers
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'MasterCard', 'label' => 'MasterCard'),
            array('value' => 'VISA', 'label' => 'VISA'),
        );
    }
}