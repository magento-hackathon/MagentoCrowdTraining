<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Helper_Mschema_Validator_Trim
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Mschema_Validator_Trim extends Mediotype_Core_Helper_Mschema_Validator_Abstract
{
    /**
     * @param $validationObject
     * @param $data
     * @return Mediotype_Core_Model_Response
     */
    public function Validate($validationObject, &$data)
    {
        if (is_string($data))
            $data = trim($data, $this->getVOData($validationObject));

        return new Mediotype_Core_Model_Response(__METHOD__, NULL, Mediotype_Core_Model_Response::OK);
    }

    /**
     * @return string
     */
    protected function getKeyword()
    {
        return "trim";
    }

}