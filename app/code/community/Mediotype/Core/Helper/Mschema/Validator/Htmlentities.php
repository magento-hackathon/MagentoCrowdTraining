<?php

/**
 * Validator converts value to htmlentities
 *
 * Json node looks like: "htmlentities" : "UTF-8"
 * Where "key" = the keyword for the validator, and "value" = a valid Supported Charset of the htmlentities function
 * See valid charsets at http://php.net/manual/en/function.htmlentities.php
 *
 * @author  Joel Hart
 */
class Mediotype_Core_Helper_Mschema_Validator_Htmlentities extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{

    /**
     * Converts characters from a valid charset into html entities
     *
     * @param $validationObject
     * @param $data
     * @return bool|Mediotype_Core_Model_Response
     */
    public function Validate($validationObject, &$data)
    {

        if ($this->CanRead($validationObject)) {

            try {

                $characterSet = $this->getVOData($validationObject);
                if ( !empty($characterSet) && $characterSet === true) {
                    //Use Default of UTF-8
                    $characterSet = 'UTF-8';
                }
                Mage::log($data, NULL, 'card_size_raw.log');
                Checkerboard_DataAdapters_Helper_Report::auditAttributeValuesInImportNoSkusCSV('card_size,' . $data, 'card_size_raw.csv' );
                $htmlString = htmlentities($data, ENT_COMPAT, $characterSet);
                Mage::log($htmlString, NULL, 'card_size_html_entities.log');
                Checkerboard_DataAdapters_Helper_Report::auditAttributeValuesInImportNoSkusCSV('card_size,' . $data, 'card_size_html_entities.csv' );
                if (empty($htmlString)) {
                    return new Mediotype_Core_Model_Response(
                        __METHOD__,
                        null,
                        Mediotype_Core_Model_Response::FATAL,
                        "FAILED TO FIND VALID HTML ENTITY FOR PROVIDED VALUE"
                    );
                }

                $data = $htmlString;
                return new Mediotype_Core_Model_Response(__METHOD__, null, Mediotype_Core_Model_Response::OK);

            } catch (Exception $e) {
                return new Mediotype_Core_Model_Response(
                    __METHOD__,
                    null,
                    Mediotype_Core_Model_Response::FATAL,
                    "FAILED TO FIND VALID CHARACTERS COMPATIBLE WITH HTML ENTITIES FUNCTION"
                );
            }
        }

    }

    /**
     * @return string
     */
    protected function getKeyword()
    {
        return "htmlentities";
    }

}