<?php

/**
 * Exception class for CM Payments
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Exception extends ErrorException
{
    const EXECUTION_CALL = 10;
    const VALIDATION_STREET = 23;
}