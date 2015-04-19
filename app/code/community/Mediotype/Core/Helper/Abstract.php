<?php
/**
 *
 * @author      Joel Hart
 */

class Mediotype_Core_Helper_Abstract extends Mage_Core_Helper_Abstract
{

    /**
     *
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getCoreWrite(){
        /** @var $write Varien_Db_Adapter_Pdo_Mysql */
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $write;
    }

    /**
     *
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getCoreRead(){
        /** @var $read Varien_Db_Adapter_Pdo_Mysql */
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $read;
    }

    /**
     *
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
     public function getCoreSetup(){
        /** @var $read Varien_Db_Adapter_Pdo_Mysql */
        $read = Mage::getSingleton('core/resource')->getConnection('core_setup');
        return $read;
     }

    /**
     * TODO -> Refactor this to follow isModuleOutPutEnabled naming convention style for Magento-isms
     *
     * @param $xmlNode STRING path to config group
     *
     * @return array system config values ($key => $value)
     */
    public function getExtensionSystemConfig($xmlNode)
    {
        if (Mage::helper('mediotype_core/debugger')->getEnabled()) {
            Mediotype_Core_Helper_Debugger::log(array("xmlnode" => $xmlNode)); // Can't we just pass this responsibility to the debugger for basic calls?
        }
        return Mage::getStoreConfig($xmlNode);
    }

    /**
     * Check is module is enabled
     *
     * Follows naming convention of ALL Magento modules for system config path
     *
     * modules/MODULE_NAME/active
     *
     * @return bool
     */
    public function getIsEnabled(){
        return $this->isModuleOutputEnabled();
    }

}
