<?php

/**
 * Api class for response message reading
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 */
class Comaxx_CmPayments_Model_Api_Response
{

    private $_response;
    private $_error_messages;

    /**
     * Sets response in current handler
     *
     * @param string $response    Response of the API call
     * @param int    $http_status The http_status from the curl request
     *
     * @return void
     */
    public function setResponse($response, $http_status)
    {
        $_response = json_decode($response, true);
        if ($http_status !== 200) {
            if (array_key_exists('errors', $_response)) {
                foreach ($_response['errors'] as $error) {
                    $this->setErrorResponse($error['message'] . ' (' . $error['code'] . ')');
                }
            }
        }

        $this->_response = $_response;
    }

    /**
     * Returns the response
     *
     * @return array
     */
    public function getResponseData()
    {
        return $this->_response;
    }

    /**
     * Sets error response in current handler
     *
     * @param string $error_message Error message to use
     *
     * @return void
     */
    public function setErrorResponse($error_message)
    {
        $this->_error_messages[] = $error_message;
    }

    /**
     * Checks if current handler contains an error
     *
     * @return boolean True if handler has error, otherwise False
     */
    public function hasError()
    {
        $hasErrors = count($this->_error_messages) > 0;

        //check if alternative way to report errors is used
        if (! $hasErrors && ((isset($this->_response['errors']) && count($this->_response['errors'] > 0)) || (isset($this->_response['error']) && count($this->_response['error'] > 0)))) {
            $hasErrors = true;
        }

        return $hasErrors;
    }

    /**
     * Gets error message if any
     *
     * @return String Error message if any, otherwise null
     */
    public function getErrorMessage()
    {
        if ($this->hasError()) {
            return implode(', ', $this->_error_messages);
        }

        return null;
    }
}