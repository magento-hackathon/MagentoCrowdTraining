<?php

class Mediotype_Tutorials_Adminhtml_PostController extends Mage_Adminhtml_Controller_Action
{
    public function saveAction()
    {
        $tutorialPostData = $this->getRequest()->getParam('tutorials', array());
        foreach ($tutorialPostData as $tutorialId => $tutorialData) {
            /** @var Mediotype_Tutorials_Model_Tutorial $tutorialModel */
            $tutorialModel = Mage::getModel('mediotype_tutorials/tutorial');
            if ($tutorialId != 'new') {
                $tutorialModel->load($tutorialId);
            }

            $tutorialModel->addData(
                array(
                    "label" => $tutorialData['label'],
                    "description" => $tutorialData['description'],
                    "keywords" => $tutorialData['keywords'],
                    "category" => $tutorialData['category']
                )
            );

            $tutorialModel->save();

            foreach ($tutorialModel->getPages() as $pageModel) {
                /** @var $pageModel Mediotype_Tutorials_Model_Page */
                foreach ($pageModel->getSteps() as $stepModel) {
                    /** @var $stepModel  Mediotype_Tutorials_Model_Step */
                    $stepModel->delete();
                }
                $pageModel->delete();


            }

            foreach ($tutorialData['pages'] as $pIndex => $pageData) {
                $pageModel = Mage::getModel('mediotype_tutorials/page');
                $pageModel->setData(
                    array(
                        "tutorial_id" => $tutorialModel->getId(),
                        "url_key" => $pageData['url_key'],
                        "use_expose" => ($pageData['use_expose'] == 'on' ? true : null),
                        "params" => $pageData['params'],
                        "pre_page_callback" => $pageData['pre_page_callback'],
                        "post_page_callback" => $pageData['post_page_callback']
                    )
                );
                $pageModel->save();

                foreach ($pageData['steps'] as $sIndex => $stepData) {
                    $stepModel = Mage::getModel('mediotype_tutorials/step');
                    $stepData = array(
                        "page_id" => $pageModel->getId(),
                        "step_title" => $stepData['title'],
                        "step_content" => $stepData['content'],
                        "target_element_id" => $stepData['target_element_id'],
                        "tip_location" => $stepData['tip_location'],
                        "pre_step_callback" => $stepData['pre_step_callback'],
                        "post_step_callback" => $stepData['post_step_callback']
                    );
                    $stepModel->setData($stepData);
                    $stepModel->save();
                }
            }

        }
        $this->_redirectReferer();
    }

    public function startTutorialAction()
    {
        Mage::helper('mediotype_tutorials')->startTutorialSession();
    }

    public function stopTutorialAction()
    {
        Mage::helper('mediotype_tutorials')->stopTutorialSession();
    }

    public function startEditingAction()
    {
        Mage::helper('mediotype_tutorials')->startEditingSession();
    }

    public function stopEditingAction()
    {
        Mage::helper('mediotype_tutorials')->stopEditingSession();
    }

    public function deleteAction()
    {
        $tutorialId = $this->getRequest()->getParam('id');
        $tutorialModel = Mage::getModel('mediotype_tutorials/tutorial')->load($tutorialId);
        $tutorialModel->delete();
        $this->_redirectReferer();
    }

    protected function _validateFormKey()
    {
        return true;
    }

    protected function _validateSecretKey()
    {
        return true;
    }
}