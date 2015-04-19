<?php
class Mediotype_Core_Controller_Adminhtml_Action extends Mage_Adminhtml_Controller_Action
{
    public function isAllowed($action = null)
    {
        return Mage::helper('mediotype_core/acl')->isAllowed($this->getAclPrefix(), $this, $action);
    }

    protected function _isAllowed()
    {
        return $this->isAllowed();
    }

    public function getAclPrefix()
    {
        return null;
    }
}