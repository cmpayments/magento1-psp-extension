<?php

/**
 * Payment method class for Bancontact
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Method_Bancontact extends Comaxx_CmPayments_Model_Method_Abstract
{
    protected $_code = 'cmpayments_bc';
    protected $_minimumQuoteAmount = 0.02;
}