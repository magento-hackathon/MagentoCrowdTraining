<?php

class Mediotype_Tutorials_Block_Adminhtml_Links extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setData('area', 'frontend');
    }

    public function getBlockJson()
    {

        $data = array();
        $data['autoEditTutorial'] = Mage::helper('mediotype_tutorials')->getEditingSession();
        $data['pageRoute'] = Mage::helper('mediotype_tutorials')->getPageRoute();
        $data['pageTutorials'] = Mage::helper('mediotype_tutorials')->getPageTutorialsData();


        return htmlspecialchars(json_encode($data));
    }

    public function getPageRoute()
    {
        return Mage::helper('mediotype_tutorials')->getPageRoute();
    }

    public function getPageTutorialsCollection()
    {
        return Mage::helper('mediotype_tutorials')->getPageTutorialsCollection();
    }

    public function getAllTutorialsCollection()
    {
        /** @var Mediotype_Tutorials_Model_Resource_Tutorial_Collection $collection */
        $collection = Mage::getModel('mediotype_tutorials/tutorial')->getCollection();
        $collection->getSelect()
            ->join(array("ctpOne" => 'mediotype_tutorials_pages'), '`ctpOne`.`tutorial_id` = `main_table`.`id`')
            ->group('main_table.id')
            ->reset('columns')
            ->columns("main_table.*")
            ->columns("ctpOne.url_key")
            ->columns("ctpOne.id as pageId");
        return $collection->load();
    }

    public function getAllTutorialsArray()
    {
        $otherTutorials = array();
        foreach ($this->getAllTutorialsCollection() as $tutorialModel) {
            $currentCell = &$otherTutorials;
            $categoryPath = explode('/', $tutorialModel->getData('category'));
            foreach ($categoryPath as $index => $path) {
                if (!array_key_exists($path, $currentCell)) {
                    $currentCell[$path] = array();
                }
                // IF IT IS THE LAST INDEX IN THE PATH
                if ($index == count($categoryPath) - 1) {
                    $currentCell[$path][$tutorialModel->getId()] = $tutorialModel->getData();
                    $currentCell[$path][$tutorialModel->getId()]['url_key'] = $this->getUrl($tutorialModel->getData('url_key'), array('autoStartTutorial' => $tutorialModel->getId()));
                } else {
                    $currentCell = &$currentCell[$path];
                }
            }
        }

        return $otherTutorials;
    }

    public function getAllTutorialsHtml($data, $depth = 0, $parentPath = null)
    {
        $html = array();

        foreach ($data as $index => $nodeData) {
            if ($this->isTutorialData($nodeData)) {
                $html[] = '<li><a href="' . $nodeData['url_key'] . '"><i class="fi-play size-12" style="color:#008CBA;"></i>' . $nodeData['label'] . '</a></li>';
                continue;
            } else {
                $html[] = '<li class="has-submenu" ng-show="mode==\'list\'">';

                $fullPath = $index;
                if (!is_null($parentPath)) {
                    $fullPath .= $parentPath . ' / ' . $fullPath;
                }
                $html[] = '<a href="#"><i class="fi-folder" style="color:#368A55;"></i>' . $fullPath . '</a>';
                $html[] = '<ul class="right-submenu">';
                $html[] = '<li class="back"><a href="#">Back</a></li>';
                $html[] = '<li><label>' . $fullPath . '</label></li>';

                $html[] = $this->getAllTutorialsHtml($nodeData, $depth + 1);

                $html[] = '</ul>';
                $html[] = '</li>';
            }
        }

        return implode(PHP_EOL, $html);
    }

    protected function isTutorialData($data)
    {
        return array_key_exists('url_key', $data) && array_key_exists('label', $data);
    }

}