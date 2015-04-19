<?php
/**
 * Created by PhpStorm.
 * User: szurek
 * Date: 9/4/14
 * Time: 9:46 PM
 */
class Mediotype_Core_Block_Adminhtml_Form_Element_Configurable extends Varien_Data_Form_Element_Abstract
{
    protected $_htmlTag = 'div';

    public function __construct($attributes = array())
    {
        if (array_key_exists('htmlTag', $attributes)) {
            $this->setHtmlTag($attributes['htmlTag']);
        }

        parent::__construct($attributes);
    }

    public function getElementHtml()
    {
        $html = '<' . $this->getHtmlTag() . ' id="' . $this->getHtmlId() . '" name="' . $this->getName()
            . '" ' . $this->serialize($this->getHtmlAttributes()) . '>' . $this->getValue(
                null
            ) . '</' . $this->getHtmlTag() . '>' . "\n";
        $html .= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @param string $htmlTag
     */
    public function setHtmlTag($htmlTag)
    {
        $this->_htmlTag = $htmlTag;
    }

    /**
     * @return string
     */
    public function getHtmlTag()
    {
        return $this->_htmlTag;
    }


}