<?php
/**
 * Used in creating options for On|Off config value selection
 *
 */
class Mediotype_Core_Model_System_Config_Source_Onoff
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('On')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Off')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('adminhtml')->__('Off'),
            1 => Mage::helper('adminhtml')->__('On'),
        );
    }

}
