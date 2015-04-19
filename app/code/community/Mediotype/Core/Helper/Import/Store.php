<?php
/**
 * Helper for interacting with one or more stores
 *
 * @author  Joel Hart   @mediotype
 */
class Mediotype_Core_Helper_Import_Store extends Mediotype_Core_Helper_Abstract{

    /**
     * Get All Store IDs in a Magento Build
     *
     * @return array
     */
    public function getAllStoreIds()
    {
        $storeIds = array();
        foreach (Mage::app()->getStores(true) as $store) {
            $storeIds[] = $store->getId();
        }
        return $storeIds;
    }

    /**
     * Get all enabled locale's associated with store ID in a Magento build
     *
     * @return array
     */
    public function getStoreLocaleCodes()
    {
        $storeCollection = Mage::getModel('core/store')->getCollection();
        $localeCodes = array();
        foreach ($storeCollection as $store) {
            $currentCode = Mage::getStoreConfig('general/locale/code', $store->getId());
            if (!isset($localeCodes[$currentCode])) {
                $localeCodes[$currentCode] = array();
            }
            array_push($localeCodes[$currentCode], $store->getId());
        }

        return $localeCodes;
    }

}