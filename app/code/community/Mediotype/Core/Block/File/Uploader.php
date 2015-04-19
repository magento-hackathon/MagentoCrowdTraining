<?php
/**
 * Class Mediotype_LandingPage_Block_Pdf_Uploader
 *
 * see http://stackoverflow.com/questions/5077755/images-in-magento-widgets
 * for explantion of why this is required for file uploads for widgets
 *
 * Helper block for use with widgets to allow for pdf uploading
 */
class Mediotype_LandingPage_Block_File_Uploader extends Mage_Adminhtml_Block_Template
{
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $config = $this->getConfig();
        $chooseButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable btn-chooser')
            ->setLabel($config['button']['open'])
            ->setOnclick('MediabrowserUtility.openDialog(\''.$this->getUrl('*/cms_wysiwyg_images/index', array('target_element_id' => $element->getName())).'\')')
            ->setDisabled($element->getReadonly());
        $text = new Varien_Data_Form_Element_Text();
        $text->setForm($element->getForm())
            ->setId($element->getName())
            ->setName($element->getName())
            ->setClass('widget-option input-text');
        if ($element->getRequired()) {
            $text->addClass('required-entry');
        }
        if ($element->getValue()) {
            $text->setValue($element->getValue());
        }
        $element->setData('after_element_html', $text->getElementHtml().$chooseButton->toHtml());
        return $element;
    }
}
