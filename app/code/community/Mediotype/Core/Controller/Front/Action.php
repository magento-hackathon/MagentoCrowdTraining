<?php
/**
 *
 * @author      Joel Hart
 */
class Mediotype_Core_Controller_Front_Action extends Mage_Core_Controller_Front_Action{

    /**
     * Calls normal __construct THEN if Mediotype debugger is enabled provides additional developer output
     *
     * @param Zend_Controller_Request_Abstract  $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array                             $invokeArgs
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()){
        parent::__construct( $request,  $response,  $invokeArgs = array());

        if(Mediotype_Core_Helper_Debugger::getEnabled()){
            Mediotype_Core_Helper_Debugger::log(
                array(
                    "ACTION NAME"               => $this->getRequest()->getActionName(),
                    "FULL ACTION NAME"          => $this->getFullActionName(),
                    "ACTION METHOD EXISTS"      => method_exists($this, $this->getActionMethodName($this->getRequest()->getActionName())),
                    "REQUEST PARAMETERS"        => $this->getRequest()->getParams(),
                )
            );
        }

    }

}
