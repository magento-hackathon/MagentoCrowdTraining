<?php
/**
 * @author  Joel Hart
 * @var $this Mage_Core_Model_Resource_Setup
 */

$installer = $this;

$installer->startSetup();
//
//$coreDefaultSym = (bool)Mage::getStoreConfig('mediotype_core/use_simlinks');
//
//if ($coreDefaultSym) {
//    try {
//        /** @var  $sysConfig Mage_Core_Model_Config_Data */
//        $sysConfig = Mage::getModel('core/config_data');
//        $sysConfig->load('dev/template/allow_symlink', 'path');
//        if ($sysConfig->getId()) {
//            $sysConfig->setValue('1');
//            $sysConfig->save();
//        } else {
//            $sysConfig->setPath('dev/template/allow_symlink');
//            $sysConfig->setValue('1');
//            $sysConfig->save();
//        }
//    } catch (Exception $e) {
//        Mediotype_Core_Helper_Debugger::log('failed to set system config use symlinks to true');
//        Mage::logException($e);
//    }
//}

$installer->endSetup();
