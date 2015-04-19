<?php
class Mediotype_Core_Helper_Acl extends Mage_Core_Helper_Abstract
{

    public function isAllowed($aclPrefix = null, $controller = null, $action = null)
    {
        $aclPath = $this->getAclPath($aclPrefix, $controller, $action);
        return Mage::getSingleton('admin/session')
            ->isAllowed($aclPath);
    }

    protected function getAclPath($aclPrefix = null, $controller = null, $action = null)
    {
        /** @var $helper Mediotype_Core_Helper_Data */
        $helper = Mage::helper("mediotype_core");

        if (is_object($controller) && is_null($action)) {
            $action = $controller->getRequest()->getActionName();
        } elseif (is_array($action)) {
            $action = implode("/", $action);
        }

        $aclPath = "$aclPrefix/$action";

        return $aclPath;
    }
}