<?php

/**
 * Created by PhpStorm.
 * User: stevenzurek
 * Date: 12/14/14
 * Time: 2:24 PM
 */
class Mediotype_Tutorials_Model_Resource_Page_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('mediotype_tutorials/page');
    }

}