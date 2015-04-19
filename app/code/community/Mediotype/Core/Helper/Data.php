<?php
/**
 * Magento / Mediotype Module
 *
 *
 * @desc
 * @category    Mediotype
 * @package     Mediotype_Core
 * @class       Mediotype_Core_Helper_Data
 * @copyright   Copyright (c) 2013 Mediotype (http://www.mediotype.com)
 *              Copyright, 2013, Mediotype, LLC - US license
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * This method works like addFieldToFilter with a having call instead of a where call

     * Example of attaching to a column inside a grid, using filter condition callback (in the addColumn function:
        $this->addColumn(
            'lateBy',
            array(
                'header' => $this->__('Late By'),
                'index' => 'late_by',
                'type' => 'number',
                'filter_condition_callback' => "Mediotype_Core_Helper_Data::filterHaving"
            )
        );
     *
     *
     *
     * @param $collection
     * @param $column
     */
    public static function filterHaving($collection, $column)
    {
        /** @var $collection Checkerboard_Workorder_Model_Resource_Workorder_Collection */
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $cond = $column->getFilter()->getCondition();
        if ($field && isset($cond)) {
            $collection->addFieldToFilterHaving($field, $cond);
        }
    }

    /**
     * @param $collection
     * @param $mainTableForeignKey
     * @param $eavType
     * @param int $startIndex
     * @return mixed
     */
    public function joinEavTablesIntoCollection($collection, $mainTableForeignKey, $eavType, $startIndex = 1)
    {

        $entityType = Mage::getModel('eav/entity_type')->loadByCode($eavType);
        $attributes = $entityType->getAttributeCollection();
        $entityTable = $collection->getTable($entityType->getEntityTable());

        //Use an incremented index to make sure all of the aliases for the eav attribute tables are unique.
        $index = $startIndex;
        foreach ($attributes->getItems() as $attribute) {
            $alias = 'table' . $index;
            if ($attribute->getBackendType() != 'static') {
                $table = $entityTable . '_' . $attribute->getBackendType();
                $field = $alias . '.value';
                $collection->getSelect()
                    ->joinLeft(array($alias => $table),
                    'main_table.' . $mainTableForeignKey . ' = ' . $alias . '.entity_id and ' . $alias . '.attribute_id = ' . $attribute->getAttributeId(),
                    array($attribute->getAttributeCode() => $field)
                );
            }
            $index++;
        }
        //Join in all of the static attributes by joining the base entity table.
        $collection->getSelect()->joinLeft($entityTable, 'main_table.' . $mainTableForeignKey . ' = ' . $entityTable . '.entity_id');

        return $collection;

    }

    /**
     * @param string $string
     * @return array|null
     */
    public function explodeCamelCase($string)
    {
        $results = array();
        if (!is_string($string) || strlen($string) == 0) {
            return null;
        }
        $anchor_position = 0;
        for ($cur_position = 0; $cur_position < strlen($string); $cur_position++) {
            if (ctype_upper(substr($string, $cur_position, 1))) {
                $subject = strtolower(substr($string, $anchor_position, $cur_position - $anchor_position));
                $results[] = $subject;
                $anchor_position = $cur_position;
            }
        }

        if ($anchor_position == 0) {
            $results[] = $string;
        } else {
            $results[] = strtolower(substr($string, $anchor_position, strlen($string) - $anchor_position));
        }

        return $results;
    }

    public function interpolate($content, array $context = array(), $wrapperStart = null, $wrapperEnd = null)
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace[$wrapperStart . $key . $wrapperEnd] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($content, $replace);
    }
}
