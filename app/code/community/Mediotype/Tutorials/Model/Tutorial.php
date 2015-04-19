<?php

/**
 * Created by PhpStorm.
 * User: stevenzurek
 * Date: 11/24/14
 * Time: 7:51 AM
 */
class Mediotype_Tutorials_Model_Tutorial extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('mediotype_tutorials/tutorial');
    }


    /**
     * @return Mediotype_Tutorials_Model_Resource_Page_Collection
     */
    public function getPages()
    {
        $collection = Mage::getModel('mediotype_tutorials/page')->getCollection();
        $collection->addFieldToFilter('tutorial_id', $this->getId());
        return $collection->load();
    }

    /**
     * @return Mediotype_Tutorials_Model_Page
     */
    public function getFirstPage()
    {
        return $this->getPages()->getFirstItem();
    }

    public function getProcessedData()
    {
        /** @var $tutorialModel Mediotype_Tutorials_Model_Tutorial */
        $data = $this->getData();
        $data['pages'] = array();
        // Get All the pages for tutorial
        foreach ($this->getPages() as $pageModel) {
            /** @var $pageModel Mediotype_Tutorials_Model_Page */
            $tmpArray = $pageModel->getData();
            $tmpArray['full_url_key'] = Mage::helper('adminhtml')->getUrl($pageModel->getData('url_key'));
            $tmpArray['steps'] = array();
            // Get all steps for page
            foreach ($pageModel->getSteps() as $stepModel) {
                /** @var $stepModel Mediotype_Tutorials_Model_Step */
                $tmpArray['steps'][] = $stepModel->getData();
            }
            $data['pages'][] = $tmpArray;
        }

        return $data;
    }
}