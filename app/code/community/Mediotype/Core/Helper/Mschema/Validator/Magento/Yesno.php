<?php

/**
 * @mediotype
 * Author   Steven Zurek & Joel Hart
 */
class Mediotype_Core_Helper_Mschema_Validator_Magento_Yesno extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
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

            if(is_bool($data)){
                $response->data = $data;
                return $response;
            }

            if ( is_numeric( $data ) ) {
                if((int)$data == 1){
                    $data = true;
                }
                if((int)$data == 0){
                    $data = false;
                }
            }

            if ( is_string( $data ) ) {
                if ( uc_words( trim( $data ),'' ) == "Yes" ) {
                    $data =  true;
                }
                if ( uc_words( trim( $data ),'' ) == "No" ) {
                    $data = false;
                }
            }
        }

        $response->data = $data;
        return $response;
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return "yesno";
    }
}