<?php
/**
 *
 * @author      Joel Hart
 */
class Mediotype_Core_Model_Resource_Db_Collection_Abstract extends Mage_Core_Model_Resource_Db_Collection_Abstract{

    /**
     * Over-rides Mage_Core_Model_Resource_Db_Collection_Abstract->getAllIds() because using ajax for mass action on a grid
     * when the collection has joins from other tables is faulty in out of the box Magento
     *
     * @return array
     */
    public function getAllIds()
    {
        $this->_renderFilters();
        $idsSelect = "SELECT `core_query`.`".$this->getResource()->getIdFieldName()."` FROM (" . $this->getSelect() . ") as core_query";
        return $this->getConnection()->fetchCol($idsSelect);
    }

    /**
     * Over-rides Zend_Db_Select -> This original function fails often when using ->having on grids with resource models that use joins
     *
     * @return string|Varien_Db_Select
     */
    public function getSelectCountSql()
    {

        $this->_renderFilters();
        $countSelect = "SELECT COUNT(*) FROM (" . $this->getSelect() . ") as core_query";
        return $countSelect;

    }

    /**
     * @param array|string $field
     * @param null         $condition
     *
     * @return $this|Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addFieldToFilterHaving($field, $condition = null)
    {
        if (!is_array($field)) {
            $resultCondition = $this->_translateCondition($field, $condition);
        } else {
            $conditions = array();
            foreach ($field as $key => $currField) {
                $conditions[] = $this->_translateCondition(
                    $currField,
                    isset($condition[$key]) ? $condition[$key] : null
                );
            }

            $resultCondition = '(' . join(') ' . Zend_Db_Select::SQL_OR . ' (', $conditions) . ')';
        }

        $this->_select->having($resultCondition);

        return $this;
    }

    /**
     * Post-process collection items to run afterLoad on each
     *
     * @return $this
     * @author Joel Hart
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
            $item->afterLoad();
        }

        return parent::_afterLoad();
    }

    /**
     * Join table to collection select
     *
     * @param string $table
     * @param string $cond
     * @param string $cols
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function join($table, $cond, $cols = '*')
    {
        if (is_array($table)) {
            foreach ($table as $k => $v) {
                $alias = $k;
                $table = $v;
                break;
            }
        } else {
            $alias = $table;
        }

        if (!isset($this->_joinedTables[$alias])) {
            $this->getSelect()->join(
                array($alias => $this->getTable($table)),
                $cond,
                $cols
            );
            $this->_joinedTables[$alias] = true;
        }
        return $this;
    }

    /**
     * Join table to collection select
     *
     * @param string $table
     * @param string $cond
     * @param string $cols
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function joinLeft($table, $cond, $cols = '*')
    {
        if (is_array($table)) {
            foreach ($table as $k => $v) {
                $alias = $k;
                $table = $v;
                break;
            }
        } else {
            $alias = $table;
        }

        if (!isset($this->_joinedTables[$alias])) {
            $this->getSelect()->joinLeft(
                array($alias => $this->getTable($table)),
                $cond,
                $cols
            );
            $this->_joinedTables[$alias] = true;
        }
        return $this;
    }

}
