<?php
/**
 * Magento / Mediotype Module
 *
 * @desc        
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Model_Systems_Magento
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Model_Systems_Magento extends Mage_Core_Model_Abstract{

    /**
     *
     */
    const RESPONSE_DISPOSITION_OK = "OK";
    /**
     *
     */
    const RESPONSE_DISPOSITION_FATAL = "FATAL";

    /**
     * @var
     */
    protected $_endPoint;
    /**
     * @var
     */
    protected $_originDomain;
    /**
     * @var
     */
    protected $_originIp;
    /**
     * @var
     */
    protected $_requestMethod;
    /**
     * @var
     */
    protected $_request;
    /**
     * @var
     */
    protected $_response;
    /**
     * @var
     */
    protected $_activeAdmin;

    /**
     *
     */
    public function _construct(){
        $this->_requestMethod = Mage::getModel('mediotype_core/systems_api_json_request');
        $this->_originDomain = Mage::getBaseUrl();
        $this->_originIp = $_SERVER['REMOTE_ADDR'];
        $admin = Mage::getSingleton('admin/session');
        $this->_activeAdmin = $admin->getUser()->getName();
        parent::_construct();
    }

    /**
     * @param $uri
     * @return Mediotype_Core_Model_Systems_Magento
     */
    public function setEndPoint($uri){
        $this->_endPoint = $uri;
        return $this;
    }

    /**
     * @param $json
     * @return Mediotype_Core_Model_Systems_Magento
     */
    public function setJsonRequest($json){
        $this->_request = $json;
        return $this;
    }

    /**
     * @return String (most likely json string)
     */
    public function doRequest(){
        $params = array(
            "origin_domain" => $this->_originDomain,
            "origin_ip" => $this->_originIp,
            "admin_user" => $this->_activeAdmin,
            "request" => $this->_request
        );
        try{
            $this->_requestMethod->makeRequest( $this->_endPoint, $params );
            return $this->_requestMethod->responseBody;
        } catch (Exception $e){
            Mediotype_Core_Helper_Debugger::log(array("CAN NOT TALK TO MOTHERSHIP" , "PARAMS" => $params));
        }
    }

}