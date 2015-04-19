<?php
/**
 * Magento / Mediotype Module
 * 
 *
 * @desc        Simple CURL protocol to pass parameters to Mothership
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Model_Systems_Api_Json_Request
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Model_Systems_Api_Json_Request extends Mage_HTTP_Client_Curl{

    /**
     * Stream resource
     * @var object
     */
    protected $_sock = null;

    /**
     * Request headers
     * @var array
     */
    protected $_headers = array();


    /**
     * Fields for POST method - hash
     * @var array
     */
    protected $_postFields = array();

    /**
     * Response headers
     * @var array
     */
    protected $_responseHeaders = array();

    /**
     * Response body
     * @var string
     */
    public $responseBody = '';

    /**
     * Response status
     * @var int
     */
    protected $_responseStatus = 0;


    /**
     * Request timeout
     * @var int
     */
    protected $_timeout = 300;

    /**
     * Curl
     * @var object
     */
    protected $_ch;


    /**
     * User ovverides options hash
     * Are applied before curl_exec
     *
     * @var array();
     */
    protected $_curlUserOptions = array();


    /**
     * Header count, used while parsing headers
     * in CURL callback function
     * @var int
     */
    protected $_headerCount = 0;

    /**
     * @param string $uri
     * @param array $params
     * @param string $method
     */
    public function makeRequest($uri, $params = array(), $method = "POST")
    {
        $this->_ch = curl_init();
        $this->curlOption(CURLOPT_URL, $uri);
        if($method == 'POST') {
            $this->curlOption(CURLOPT_POST, 1);
            $this->curlOption(CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif($method == "GET") {
            $this->curlOption(CURLOPT_HTTPGET, 1);
        } else {
            $this->curlOption(CURLOPT_CUSTOMREQUEST, $method);
        }

        if(count($this->_headers)) {
            $heads = array();
            foreach($this->_headers as $k=>$v) {
                $heads[] = $k.': '.$v;
            }
            $this->curlOption(CURLOPT_HTTPHEADER, $heads);
        }

        if($this->_timeout) {
            $this->curlOption(CURLOPT_TIMEOUT, $this->_timeout);
        }

        if($this->_port != 80) {
            $this->curlOption(CURLOPT_PORT, $this->_port);
        }

        //$this->curlOption(CURLOPT_HEADER, 1);
        $this->curlOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlOption(CURLOPT_HEADERFUNCTION, array($this,'parseHeaders'));

        if(count($this->_curlUserOptions)) {
            foreach($this->_curlUserOptions as $k=>$v) {
                $this->curlOption($k, $v);
            }
        }

        $this->_headerCount = 0;
        $this->_responseHeaders = array();
        $this->responseBody = curl_exec($this->_ch);
        $err = curl_errno($this->_ch);
        if($err) {
            $this->doError(curl_error($this->_ch));
        }
        curl_close($this->_ch);
    }

}