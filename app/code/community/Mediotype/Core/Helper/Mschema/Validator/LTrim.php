<?php
class Mediotype_Core_Helper_Mschema_Validator_Ltrim extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
    /**
     * @return string
     */
    protected function getKeyword()
    {
        return "ltrim";
    }

    /**
     * @param $validationObject
     * @param $data
     * @return bool
     */
    public function Validate($validationObject, &$data)
    {
        if (is_string($data))
            $data = ltrim($data, $this->getVOData($validationObject));

        return new Mediotype_Core_Model_Response(__METHOD__, NULL, Mediotype_Core_Model_Response::OK);

    }


}