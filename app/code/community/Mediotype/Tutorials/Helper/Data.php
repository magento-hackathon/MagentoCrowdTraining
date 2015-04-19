<?php

/**
 * Created by PhpStorm.
 * User: stevenzurek
 * Date: 11/24/14
 * Time: 7:10 AM
 */
class Mediotype_Tutorials_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getCategories()
    {
        // $sql = "SELECT DISTINCT `category` FROM `mediotype_tutorials`";
        $collection = Mage::getModel('mediotype_tutorials/tutorial')->getCollection();
        $collection->getSelect()->distinct(true)->reset('columns')->columns('category');
        $collection->load();
        $results = array();
        foreach ($collection as $model) {
            $results[] = $model->getData('category');
        }
        return $results;
    }

    public function getPageRoute()
    {
        return implode("/", array(
//            $this->getFrontName(),
            $this->getRequest()->getRequestedRouteName(),
            $this->getRequest()->getRequestedControllerName(),
            $this->getRequest()->getRequestedActionName()
        ));
    }

    public function getRequest()
    {
        return Mage::app()->getRequest();
    }

    public function getFrontName()
    {
        $originalPathInfo = trim($this->getRequest()->getOriginalPathInfo(), "/");
        $exploded = explode("/", $originalPathInfo);
        return $exploded[0];
    }

    /**
     * Mage::helper('mediotype_tutorials')->getPageTutorialsCollection()->getSelectSql(true)
     * @return Varien_Data_Collection_Db
     */
    public function getPageTutorialsCollection()
    {
        //Mage::log($this->getPageRoute(), null, 'tut-page-route.log');
        /** @var Mediotype_Tutorials_Model_Resource_Tutorial_Collection $collection */
        $collection = Mage::getModel('mediotype_tutorials/tutorial')->getCollection();
        $collection->getSelect()
            ->join(array("ctpOne" => 'mediotype_tutorials_pages'), '`ctpOne`.`tutorial_id` = `main_table`.`id`')
            ->join(array("ctpTwo" => 'mediotype_tutorials_pages'), "`ctpTwo`.`tutorial_id` = `main_table`.`id` and `ctpTwo`.`url_key` = '" . $this->getPageRoute() . "'")
            ->group('main_table.id')
            ->reset('columns')
            ->columns("main_table.*")
            ->columns("ctpOne.url_key")
            ->columns("ctpOne.id as pageId")
            ->columns("ctpTwo.id as ctpTwoID")
            ->having('`pageId` = `ctpTwoID`');

        //Mage::log($collection->getSelectSql(true), null, 'tut-page-route.log');
        return $collection->load();
    }

    public function getPageTutorialsData($StepsBlockData = false)
    {
        // Get all Tutorials that start on the current page
        $data = array();
        foreach ($this->getPageTutorialsCollection() as $tutorialModel) {
            /** @var $tutorialModel Mediotype_Tutorials_Model_Tutorial */
            $data[$tutorialModel->getId()] = $tutorialModel->getProcessedData();
        }

        if ($editTutorialId = $this->getEditingSession()) {
            $editTutorialModel = Mage::getModel('mediotype_tutorials/tutorial')->load($editTutorialId);
            $data[$editTutorialModel->getId()] = $editTutorialModel->getProcessedData();
        }


//        if ($StepsBlockData) {
        if ($autoplayTutorialId = Mage::helper('mediotype_tutorials')->getTutorialSession()) {
            $autoplayTutorialModel = Mage::getModel('mediotype_tutorials/tutorial')->load($autoplayTutorialId);
            $data[$autoplayTutorialModel->getId()] = $autoplayTutorialModel->getProcessedData();
        }
//        }

        return $data;
    }

    public function startEditingSession()
    {
        Mage::getSingleton('adminhtml/session')->setData('tutorial-autoedit-id', $this->getRequest()->getParam('tutorial_id'));
    }

    public function stopEditingSession()
    {
        Mage::getSingleton('adminhtml/session')->unsetData('tutorial-autoedit-id');
    }

    public function getEditingSession()
    {
        return Mage::getSingleton('adminhtml/session')->getData('tutorial-autoedit-id');
    }


    public function getTutorialSession()
    {
        if ($tutId = Mage::getSingleton('adminhtml/session')->getData('as-tutorial-id')) {
            return $tutId;
        }
        return $this->getRequest()->getParam('autoStartTutorial');
    }

    public function startTutorialSession()
    {
        Mage::getSingleton('adminhtml/session')->setData('as-tutorial-id', $this->getRequest()->getParam('tutorial_id'));
    }

    public function stopTutorialSession()
    {
        Mage::getSingleton('adminhtml/session')->unsetData('as-tutorial-id');

    }


    public function canEdit()
    {
        return Mage::getSingleton('admin/session')->isAllowed('mediotype_tutorials/edit');
    }

    public function canDelete()
    {
        return Mage::getSingleton('admin/session')->isAllowed('mediotype_tutorials/delete');
    }
}