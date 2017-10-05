<?php

/**
 * Abstract class for api models
 *
 * @category Comaxx
 * @package  Comaxx_CmPayments
 * @author   Development <development@comaxx.nl>
 *
 */
abstract class Comaxx_CmPayments_Model_Api_Abstract
{
    const SEVERITY_DEBUG = 'debug', SEVERITY_INFO = 'information', SEVERITY_WARN = 'warning', SEVERITY_ERROR = 'error';

    const API_URL = 'https://api.cmpayments.com/{action}/v1/{key}';
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    const OAUTH_SIGNATURE_METHOD = 'HMAC-SHA256';
    const OAUTH_VERSION = '1.0';

    protected $method;
    protected $data;
    protected $action;
    protected $key;

    /**
     * Excute the request
     *
     * @return Comaxx_CmPayments_Model_Api_Response
     * @throws \Mage_Core_Exception
     */
    public function doRequest()
    {
        $helper = Mage::helper('cmpayments');
        $action = $this->getAction();
        $data   = $this->getData();
        $method = $this->getMethod() ?: self::METHOD_POST;
        $key    = $this->getKey() ?: '';

        if (! $action) {
            Mage::throwException('Please define an action');
        }

        //replace action and key
        $url = str_replace('{action}', $action, self::API_URL);
        $url = str_replace('{key}', $key, $url);
        $helper->log('call for using url: ' . $url);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeader($url, $data, $method));

        if ($method === self::METHOD_POST) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            $helper->log($data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response    = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $helper->log('response status: ' . $http_status);
        $helper->log($response);

        curl_close($curl);

        /** @var Comaxx_CmPayments_Model_Api_Response $apiResponse */
        $apiResponse = Mage::getModel('cmpayments/api_Response');
        $apiResponse->setResponse($response, $http_status);

        return $apiResponse;
    }

    /**
     * Build headers for the curl request having signed OAuth headers
     *
     * @param string $url    The action url of the api call
     * @param array  $data   The data for the call
     * @param string $method The request method (GET, POST)
     *
     * @return array The headers containing signed OAuth headers
     */
    protected function getHeader($url, $data, $method)
    {
        $nonce     = md5(uniqid(microtime(true), true));
        $timestamp = time();

        $header = array();

        if ($data) {
            $header[] = json_encode($data);
        }

        $oauth_consumer_key = Mage::getStoreConfig('cmpayments/merchant_account/oauth_consumer_key');
        $oauth_secret       = Mage::getStoreConfig('cmpayments/merchant_account/oauth_secret');

        $header = array_merge(
            $header, array(
            "oauth_consumer_key={$oauth_consumer_key}",
            "oauth_nonce={$nonce}",
            'oauth_signature_method=' . self::OAUTH_SIGNATURE_METHOD,
            "oauth_timestamp={$timestamp}",
            'oauth_version=' . self::OAUTH_VERSION,
            )
        );

        $payload = $method . '&' . rawurlencode($url) . '&' . implode(
            rawurlencode('&'),
            array_map('rawurlencode', $header)
        );

        $signkey = implode('&', array(rawurlencode($oauth_consumer_key), rawurlencode($oauth_secret)));
        $hash    = rawurlencode(base64_encode(hash_hmac('sha256', $payload, $signkey)));

        $oauth_header = array(
            "oauth_consumer_key=\"{$oauth_consumer_key}\"",
            "oauth_nonce=\"{$nonce}\"",
            "oauth_signature=\"{$hash}\"",
            'oauth_signature_method="' . self::OAUTH_SIGNATURE_METHOD . '"',
            "oauth_timestamp=\"{$timestamp}\"",
            'oauth_version="' . self::OAUTH_VERSION . '"',
        );

        $header = array(
            'Content-type: application/json',
            'Authorization: OAuth ' . implode(', ', $oauth_header),
        );

        return $header;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}