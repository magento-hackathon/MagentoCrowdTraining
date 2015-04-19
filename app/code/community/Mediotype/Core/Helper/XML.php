<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Helper_Xml
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Xml extends Mage_Core_Helper_Abstract {
    /**
     * @param $XML
     * @return array
     */
    public function XMLToArray($XML) {
        $returnData = array();
        $XML = simplexml_load_string($XML);

        foreach ($XML as $node) {
            if ($node->count() > 0) {
                $returnData[$node->getName()] = $this->XMLToArray($node->asXML());
            } else {
                $returnData[$node->getName()] = urldecode((string) $node);
            }
        }
        return $returnData;
    }
}

