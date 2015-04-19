<?php
/**
 * Magento / Mediotype Module
 *
 * @desc        Drives Instructions Blocks Content For Modules
 *              Allows override of instructions from module shipped html to
 *              updated Mediotype html
 * @usage       extend this class, add a configuration node to reference the mothership URI
 *              Assign endpoint in the _construct(). Use Layout.xml to declare blocktype of
 *              your instruction block and embed in an adminhtml view.
 *              TO EXECUTE OFFLINE HTML
 *              add a function named
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Block_Instructions
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ, JH) <diveinto@mediotype.com>
 */
abstract class Mediotype_Core_Block_Instructions extends Mage_Core_Block_Template {

    protected $_endPoint; //Required by module instruction to talk home
    protected $_jsonRequest; //Required to send communication (can be an empty key:val)

    protected $_requestMethod;
    protected $_remoteHtml;

    abstract protected function _offlineHtml(); // required to feed offline instructions

    public function _construct(){
        $this->_requestMethod = Mage::getModel('mediotype_core/systems_magento');
        parent::_construct();
    }

    protected function _afterToHtml($html){

        if( $remoteHtml = $this->_hasRemoteInstructions() ){
            $html .= $remoteHtml;
        } else {
            $html .= $this->_offlineHtml();
        }

        return $html;
    }

    protected function _hasRemoteInstructions(){

        $remoteMessage =
            $this->_requestMethod
              ->setEndPoint($this->_endPoint)
              ->setJsonRequest($this->_jsonRequest)
              ->doRequest();

        $responseObj = json_decode($remoteMessage);

        $html = "";

        if( $responseObj->disposition !== NULL
            && $responseObj->disposition == Mediotype_Core_Model_Systems_Magento::RESPONSE_DISPOSITION_OK){

        }

        if( strlen( $responseObj->alertHtml ) > 0){
            $html .= $responseObj->alertHtml;
        }

        if( strlen($responseObj->html) > 0 ){

            $html .= "<div class='mediotype-notification'>";
            $html .= "<span class='mediotype-notification-span'>";
            $html .= $remoteMessage;
            $html .= "</span></div>";
            return $html;
        }

        return false;
    }

}