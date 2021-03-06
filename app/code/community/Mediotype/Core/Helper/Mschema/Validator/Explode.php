<?php
class Mediotype_Core_Helper_Mschema_Validator_Explode extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
    /**
     * @param $validationObject
     * @param $data
     * @return bool
     */
    public function Validate($validationObject, &$data)
    {
        if (is_string($data))
            $data = explode($this->getVOData($validationObject), $data);

        return new Mediotype_Core_Model_Response(__METHOD__, NULL, Mediotype_Core_Model_Response::OK);

    }

    /**
     * @return string
     */
    protected function getKeyword()
    {
        return "explode";
    }

}