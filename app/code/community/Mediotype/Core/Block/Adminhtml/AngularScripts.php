<?php
class Mediotype_Core_Block_Adminhtml_AngularScripts extends  Mage_Core_Block_Template {
    protected $scripts = array();
    protected  function _construct(){
        $this->setTemplate('mediotype/core/foundation/angularscripts.phtml');
    }
}