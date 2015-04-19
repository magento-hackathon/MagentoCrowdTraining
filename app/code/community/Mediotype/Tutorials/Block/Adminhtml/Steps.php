<?php

class Mediotype_Tutorials_Block_Adminhtml_Steps extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setData('area', 'frontend');
    }

    public function getBlockJson()
    {
        $data = array();
        $data['pageRoute'] = Mage::helper('mediotype_tutorials')->getPageRoute();
        $data['tutorials'] = Mage::helper('mediotype_tutorials')->getPageTutorialsData(true);
        $data['autoStartTutorial'] = Mage::helper('mediotype_tutorials')->getTutorialSession();
        $autoStartPage = $this->getRequest()->getParam('tutorialPageIndex');
        if (!$autoStartPage) {
            if ($data['autoStartTutorial']) {
                $pageCollection = Mage::getModel('mediotype_tutorials/page')->getCollection();
                $pageCollection->addFieldToFilter('tutorial_id', $data['autoStartTutorial']);
                $pageCollection->load();
                foreach($pageCollection as $index => $pageModel){
                    if($pageModel->getData('url_key') == $data['pageRoute']){
                        $autoStartPage = $index;
                    }
                }
            }
        }
        if(!$autoStartPage){
            $autoStartPage = 0;
        }
        $data['autoStartPage'] = $autoStartPage;

        return htmlspecialchars(json_encode($data));
    }

    public function getPageTutorialsCollection()
    {
        return Mage::helper('mediotype_tutorials')->getPageTutorialsCollection();
    }
}