<?php
/**
 * Created by PhpStorm.
 * User: stevenzurek
 * Date: 11/24/14
 * Time: 9:32 AM
 */ 
class Mediotype_Tutorials_Model_Resource_Step extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('mediotype_tutorials/step', 'id');
    }

}