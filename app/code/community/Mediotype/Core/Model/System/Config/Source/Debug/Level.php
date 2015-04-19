<?php
/**
 * Used in creating options for On|Off config value selection
 *
 */
class Mediotype_Core_Model_System_Config_Source_Debug_Level
{

    const DEBUG_LOG_LEVEL_OFF       = 0; //Log Nothing
    const DEBUG_LOG_LEVEL_ERROR     = 1; //Only log errors
    const DEBUG_LOG_LEVEL_WARNING   = 2; //Log errors & warnings
    const DEBUG_LOG_LEVEL_VERBOSE   = 3; //Log verbose steps and report everything

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::DEBUG_LOG_LEVEL_OFF, 'label'=>Mage::helper('adminhtml')->__('NONE')),
            array('value' => self::DEBUG_LOG_LEVEL_ERROR, 'label'=>Mage::helper('adminhtml')->__('ONLY ERRORS')),
            array('value' => self::DEBUG_LOG_LEVEL_WARNING, 'label'=>Mage::helper('adminhtml')->__('WARNINGS & ERRORS')),
            array('value' => self::DEBUG_LOG_LEVEL_VERBOSE, 'label'=>Mage::helper('adminhtml')->__('VERBOSE REPORTING (ALL)')),
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
            self::DEBUG_LOG_LEVEL_OFF => Mage::helper('adminhtml')->__('NONE'),
            self::DEBUG_LOG_LEVEL_ERROR => Mage::helper('adminhtml')->__('ONLY ERRORS'),
            self::DEBUG_LOG_LEVEL_WARNING => Mage::helper('adminhtml')->__('WARNINGS & ERRORS'),
            self::DEBUG_LOG_LEVEL_VERBOSE => Mage::helper('adminhtml')->__('VERBOSE REPORTING (ALL)'),
        );
    }

}
