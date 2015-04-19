<?php
class Mediotype_Core_Model_Abstract extends Mage_Core_Model_Abstract {
    public function __call($method, $args)
    {
        if(substr($method, 0, 9) == "increment"){
            $incrementValue = isset($args[0]) ? $args[0] : 1;
            $key = $this->_underscore(substr($method,9));
            $this->setData($key, $this->getData($key) + $incrementValue);
            $newValue = $this->getData($key);
            return $newValue;
        }
        return parent::__call($method, $args);
    }
}