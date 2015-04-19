<?php

/**
 * @mediotype
 * Author   Steven Zurek & Joel Hart
 */
class Mediotype_Core_Helper_Mschema_Validator_Datatype extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
    /*
    "boolean"
    "integer"
    "double" (for historical reasons "double" is returned in case of a float, and not simply "float")
    "string"
    "array"
    "object"
    "resource"
    "NULL"
    "unknown type"
    "json"
     */

    protected $debug = true;

    /**
     * @param   $validationObject
     * @param   $data
     *
     * @return  bool | Mediotype_Core_Model_Response
     */
    public function Validate($validationObject, &$data)
    {
        $response = new Mediotype_Core_Model_Response(__METHOD__, null, Mediotype_Core_Model_Response::OK);
        if ($this->CanRead($validationObject)) {
            if ($validationObject->datatype == "json") {
                if ($this->debug) {
                    if (property_exists($validationObject, 'jsontype') && property_exists($validationObject, 'audit')) {
                        /* Verbos values for attributes $message = sku,attribute_code,assigned_value */
                        Checkerboard_DataAdapters_Helper_Report::auditJsonValuesCSV(
                            $validationObject->jsontype . ',' . $data,
                            $validationObject->audit . ".log"
                        );
                    }
                }
                if ($this->isJson($data) == null) {
                    $response->data = $data;
                    $response->disposition = Mediotype_Core_Model_Response::FATAL;
                    $response->description = "FAILED JSON STRING DATATYPE VALIDATION. PARAMETER PROVIDED IS A " . gettype(
                            $data
                        );
                }
            } else {
                if (gettype($data) != $validationObject->datatype) {
                    $response->disposition = Mediotype_Core_Model_Response::FATAL;
                    $response->description = "FAILED DATATYPE VALIDATION. PARAMETER PROVIDED IS A " . gettype(
                            $data
                        );
                }
            }
        }
        return $response;
    }

    /**
     * @param   $str  String
     * @return  bool
     */
    protected function isJson($str)
    {
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return "datatype";
    }
}