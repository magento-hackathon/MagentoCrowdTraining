<?php
/**
 * Class Mediotype_LandingPage_Model_Widget_Instance
 * see http://stackoverflow.com/questions/5077755/images-in-magento-widgets
 * for explantion of why this is required for file uploads for widgets
 *
 * Looks for a cached url to a file and replaces it with a proper media url for frontend use
 */
class Mediotype_Core_Model_Widget_Instance extends Mage_Widget_Model_Widget_Instance
{
    protected function _beforeSave()
    {
        if (is_array($this->getData('widget_parameters'))) {
            $params = $this->getData('widget_parameters');
            foreach($params as $k => $v){
                if(strpos($v,'/cms_wysiwyg/directive/___directive/') !== false){
                    $parts = explode('/',parse_url($v, PHP_URL_PATH));
                    $key = array_search('___directive', $parts);
                    if($key !== false){
                        $directive = $parts[$key+1];
                        $src = Mage::getModel('core/email_template_filter')->filter(Mage::helper('core')->urlDecode($directive));
                        if(!empty($src)){
                            $params[$k] = parse_url($src, PHP_URL_PATH);
                        }
                    }
                }
            }
            $this->setData('widget_parameters', $params);
        }
        return parent::_beforeSave();
    }
}