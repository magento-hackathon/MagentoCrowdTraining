<?php
/**
 * Created by PhpStorm.
 * User: stevenzurek
 * Date: 12/14/14
 * Time: 2:24 PM
 */ 
class Mediotype_Tutorials_Model_Page extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('mediotype_tutorials/page');
    }

    /**
     * @return Mediotype_Tutorials_Model_Resource_Step_Collection
     */
    public function getSteps(){
        $collection = Mage::getModel('mediotype_tutorials/step')->getCollection();
        $collection->addFieldToFilter('page_id', $this->getId());
        $collection->load();
        return $collection;
    }
}