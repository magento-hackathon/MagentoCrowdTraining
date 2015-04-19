<?php

class Mediotype_Core_Block_Template extends Mage_Core_Block_Template
{

    protected $_postUrl;
    protected $_formPrefix;
    protected $_jsFormHandlerClass;


    public function __construct()
    {
        $this->_postUrl = "";
        $this->_formPrefix = "";
        $this->_jsFormHandlerClass = "";

        parent::__construct();
    }

    public function getPostUrl()
    {
        return $this->getUrl($this->_postUrl);
    }

    public function setPostUrl($value)
    {
        $this->_postUrl = $value;
    }

    public function getFormPrefix()
    {
        if(!$this->_formPrefix){
            $this->_formPrefix = Mage::helper('mediotype_core/forms')->getUniqueId();
        }
        return "f_" . $this->_formPrefix . "_";
    }

    public function setFormPrefix($value){
        $this->_formPrefix = $value;
        return $this;
    }

    public function setJsFormHandlerClass($value)
    {
        $this->_jsFormHandlerClass = $value;
    }

    public function getJsFormHandlerClass()
    {
        return $this->_jsFormHandlerClass;
    }


}