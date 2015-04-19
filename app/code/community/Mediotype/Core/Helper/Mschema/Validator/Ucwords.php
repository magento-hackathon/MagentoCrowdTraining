<?php
/**
 * Handles converting import or validation data to upper case words following php ucwords functionality http://php.net/manual/en/function.ucwords.php
 *
 * @author @Mediotype
 */
class Mediotype_Core_Helper_Mschema_Validator_Ucwords extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
    /**
     * @param $validationObject
     * @param $data
     * @return Mediotype_Core_Model_Response
     */
    public function Validate($validationObject, &$data)
    {
        $response = new Mediotype_Core_Model_Response(__METHOD__, NULL, Mediotype_Core_Model_Response::OK);

        if ($this->CanRead($validationObject)) {

            if(is_string($data)) {
                $data = uc_words( $data, " " );
            }

            if(is_array($data)){
                foreach($data as &$element){
                    $element = uc_words( $element, " " );
                }
            }
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getKeyword()
    {
        return "ucwords";
    }


}