<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Helper_Mschema_Validator_Parseavp
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Mschema_Validator_Parseavp extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
    /**
     * @param $validationObject
     * @param $data
     * @return bool|Mediotype_Core_Model_Response
     */
    public function Validate($validationObject, &$data)
    {
        $response = new Mediotype_Core_Model_Response(__METHOD__);
        if ($this->CanRead($validationObject)) {
            $originalData = $data;

            $data = explode(',', $data);
            print_r($data);
            foreach ($data as $key => $value) {
                $data[$key] = trim($value);
                if ($data[$key] == '') {
                    $data = $originalData;
                    if (!is_array($response->data)) {
                        $response->data = array();
                    }
                    $response->disposition = Mediotype_Core_Model_Response::FATAL;
                    $response->data[] = $data;
                    $response->description = "Malformed string";
                    return $response;
                }
            }

            if (count($data) == 0) {
                $data = null;
            } else {


                foreach ($data as $key => $value) {
                    $data[$key] = trim(trim($value), '`');
                    $data[$key] = explode(':', $data[$key]);
                }

                $tempArray = array();
                foreach ($data as $key => $value) {
                    if (count($data[$key]) != 2) {
                        $data = $originalData;
                        if (!is_array($response->data)) {
                            $response->data = array();
                        }
                        $response->disposition = Mediotype_Core_Model_Response::FATAL;
                        $response->data[] = $data;
                        $response->description = "Malformed string";
                        return $response;
                    }

                    $attribute_name = $data[$key][0];
                    $attribute_value = $data[$key][1];
                    $tempArray[$attribute_name] = $attribute_value;

                }
                $data = $tempArray;
            }
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return "parse_avp";
    }
}