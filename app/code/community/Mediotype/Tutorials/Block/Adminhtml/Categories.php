<?php

/**
 * Created by PhpStorm.
 * User: stevenzurek
 * Date: 12/11/14
 * Time: 12:15 PM
 */
class Mediotype_Tutorials_Block_Adminhtml_Categories extends Mage_Adminhtml_Block_Page_Menu
{
    public function getMenuLevel($menu, $level = 0, $parent = null)
    {
        $html = array();

        // If its root level
        if (!$level) {
            $html[] = '<select id="categorySelect" name="tutorials[{{activeTutorial.id}}][category]" class="small-12 size-12" ng-model="activeTutorial.category">';
        }
        foreach ($menu as $item) {
            $itemStyle = '';

            if ($level != 0) {
                $ind = str_repeat('-', $level * 5) . '| ';
            }

            if ($level == 0) {
                $itemStyle = 'font-weight:bold;';
//                if (!empty($item['children'])) {
//                    $itemStyle .= 'font-style:italic;';
//                }
            }

            if ($level >= 2 && !($level & 1)) {
                $itemStyle = 'font-style:italic;';
            }
            $fontSize = 12 - $level;
            $itemStyle .= 'font-size:' . $fontSize . 'pt;';

            if (trim($item['label']) == '') {
                continue;
            }

            $optionValue = $item['label'];
            if (!is_null($parent)) {
                $optionValue = $parent . '/' . $optionValue;
            }

            $html[] = '<option value="' . $optionValue . '" style="' . $itemStyle . '">' . $item['label'] . '</option>';

            if (!empty($item['children'])) {
                $html[] = $this->getMenuLevel($item['children'], $level + 1, $item['label']);
            }
        }
        if (!$level) {
            $html[] = '</select>';
        }
        return implode(PHP_EOL, $html);
    }

    protected function _toHtml()
    {
        parent::_toHtml();
        return $this->getMenuLevel($this->getMenuArray());
    }


}