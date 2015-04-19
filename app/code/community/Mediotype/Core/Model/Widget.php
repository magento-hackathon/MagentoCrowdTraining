<?php
/**
 * Class Mediotype_LandingPage_Model_Widget
 * see http://stackoverflow.com/questions/5077755/images-in-magento-widgets
 * for explantion of why this is required for file uploads for widgets
 *
 * Looks for a cached url to a file and replaces it with a proper media url for frontend use
 */
class Mediotype_Core_Model_Widget extends Mage_Widget_Model_Widget
{
    public function getWidgetDeclaration($type, $params = array(), $asIs = true)
    {
        foreach($params as $k => $v){
            if(strpos($v,'/admin/cms_wysiwyg/directive/___directive/') !== false){
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
        return parent::getWidgetDeclaration($type, $params, $asIs);
    }
}