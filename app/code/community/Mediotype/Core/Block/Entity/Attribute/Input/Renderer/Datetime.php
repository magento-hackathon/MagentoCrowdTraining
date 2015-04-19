<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Block_Entity_Attribute_Input_Renderer_Datetime
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Block_Entity_Attribute_Input_Renderer_Datetime extends Varien_Data_Form_Element_Date
{
    /**
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setImage($this->getSkinUrl('images/grid-cal.gif'));
        $this->setFormat(Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        $this->setTime(true);
    }

    /**
     * Retrieve url of skins file
     *
     * @param   string $file path to file in skin
     * @param   array $params
     * @return  string
     */
    public function getSkinUrl($file = null, array $params = array())
    {
        return Mage::getDesign()->getSkinUrl($file, $params);
    }

}