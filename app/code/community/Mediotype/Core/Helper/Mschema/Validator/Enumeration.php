<?php

/**
 * Schema validation for enumerated values
 * Enumeration values should inherit from Mediotype_Core_Helper_Mschema_Enumeration_Abstract or a child class
 *
 * @author  Joel Hart
 */
class Mediotype_Core_Helper_Mschema_Validator_Enumeration extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{

    protected $debugHelper;

    /** Mediotype_Core_Helper_Data $helper */

    public function __construct()
    {
        $this->debugHelper = Mage::helper('mediotype_core/debugger');
    }

    /**
     * @param $validationObject
     * @param $data
     * @return Mediotype_Core_Model_Response
     */
    public function Validate($validationObject, &$data)
    {
        $response = new Mediotype_Core_Model_Response(__METHOD__);
        $response->startTimer();

        try {
            $enumerationClass = $validationObject->enumeration;
            /** @var Mediotype_Core_Helper_Mschema_Enumeration_Abstract $enumerationObject */
            $enumerationObject = new $enumerationClass;
            $response->data        = array($data, $enumerationObject->getEnumerable());
        } catch (Exception $e) {

            $response->disposition = Mediotype_Core_Model_Response::FATAL;
            $response->description = "Could not autoload ENUMERATION CLASS -- " . $enumerationClass;
            if ($this->debugHelper->getEnabled() &&
                $this->debugHelper->getDebugLevel(
                ) >= Mediotype_Core_Model_System_Config_Source_Debug_Level::DEBUG_LOG_LEVEL_ERROR
            ) {
                Mediotype_Core_Helper_Debugger::log("FINAL RESPONSE", $response);
            }
            return $response;
        }

        if ($this->CanRead($validationObject)) {

            if ($enumerationObject->evaluateEnumerable($validationObject, $data)) {
                $response->disposition = Mediotype_Core_Model_Response::OK;
                $response->description = "Successfully found value |" . var_export($data,true) . "| in enumeration class |" . $enumerationClass . "|";
                $response->data        = $data;
                if ($this->debugHelper->getEnabled() &&
                    $this->debugHelper->getDebugLevel(
                    ) == Mediotype_Core_Model_System_Config_Source_Debug_Level::DEBUG_LOG_LEVEL_VERBOSE
                ) {
                    Mediotype_Core_Helper_Debugger::log($response);
                }
                return $response;
            }

            $response->data        = array($data, $enumerationObject->getEnumerable());
            $response->disposition = Mediotype_Core_Model_Response::FATAL;
            $response->description = "Could not find value in enumeration :: VALUE -- " . var_export($data,true) . "  ENUMERATION CLASS -- " . $enumerationClass;

            $response->stopTimer();

            if ($response->disposition != Mediotype_Core_Model_Response::OK) {
                Mediotype_Core_Helper_Debugger::log("FINAL RESPONSE", $response);
                return $response;
            }

        }

        $response->data        = array($data, $enumerationObject->getEnumerable());
        $response->disposition = Mediotype_Core_Model_Response::FATAL;
        $response->description = "FAILED TO PASS ENUMERABLE SCHEMA TYPE -- " . $enumerationClass;
        if ($this->debugHelper->getEnabled() &&
            $this->debugHelper->getDebugLevel(
            ) >= Mediotype_Core_Model_System_Config_Source_Debug_Level::DEBUG_LOG_LEVEL_ERROR
        ) {
            Mediotype_Core_Helper_Debugger::log("FINAL RESPONSE", $response);
        }
        return $response;
    }

    /**
     * @return string
     */
    protected function getKeyword()
    {
        return "enumeration";
    }
}