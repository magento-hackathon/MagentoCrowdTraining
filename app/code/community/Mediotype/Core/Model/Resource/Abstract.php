<?php
/**
 *
 * @author      Joel Hart
 */
class Mediotype_Core_Model_Resource_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
    }

    /**
     * Serialize serializable fields of the object
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _serializeFields(Mage_Core_Model_Abstract $object)
    {
        foreach ($this->_serializableFields as $field => $parameters) {
            if (count($parameters) >= 2) {
                $serializeDefault = $parameters[0];
                if (count($parameters) > 3) {
                    $unsetEmpty = (bool)$parameters[2];
                    $callback = $parameters[3];
                } else {
                    $unsetEmpty = isset($parameters[2]);
                    $callback = null;
                }
                $this->_serializeField($object, $field, $serializeDefault, $unsetEmpty, $callback);
            }
        }
    }

    /**
     * Serialize serializable fields of the object
     *
     * @param Mage_Core_Model_Abstract $object
     */
    public function serializeFields(Mage_Core_Model_Abstract $object)
    {
        $this->_serializeFields($object);
    }


    /**
     * Unserialize serializable object fields
     *
     * @param Mage_Core_Model_Abstract $object
     */
    public function unserializeFields(Mage_Core_Model_Abstract $object)
    {
        foreach ($this->_serializableFields as $field => $parameters) {
            if (count($parameters) >= 2) {
                $unserializeDefault = $parameters[1];
                if (count($parameters) > 4) {
                    $callback = $parameters[4];
                } else {
                    $callback = null;
                }
                $this->_unserializeField($object, $field, $unserializeDefault, $callback);
            }
        }
    }

    /**
     * Serialize specified field in an object
     *
     * @param Varien_Object $object
     * @param string $field
     * @param mixed $defaultValue
     * @param bool $unsetEmpty
     *
     * @return Mage_Core_Model_Resource_Abstract
     */
    /**
     * @param Varien_Object $object
     * @param string $field
     * @param null $defaultValue
     * @param bool $unsetEmpty
     * @param callable $callback
     *
     * @author Joel Hart
     * @return $this|Mage_Core_Model_Resource_Abstract
     */
    protected function _serializeField(
        Varien_Object $object,
        $field,
        $defaultValue = null,
        $unsetEmpty = false,
        $callback = null
    ) {
        if (!is_callable($callback)) {
            $callback = 'serialize';
        }

        $value = $object->getData($field);
        if (empty($value)) {
            if ($unsetEmpty) {
                $object->unsetData($field);
            } else {
                if (is_object($defaultValue) || is_array($defaultValue)) {
                    $defaultValue = $callback($defaultValue);
                }
                $object->setData($field, $defaultValue);
            }
        } elseif (is_array($value) || is_object($value)) {
            $object->setData($field, $callback($value));
        }

        return $this;
    }

    /**
     * Unserialize Varien_Object field in an object
     *
     * @param Varien_Object $object
     * @param string $field
     * @param mixed $defaultValue
     * @param callable $callback
     *
     * @author Joel Hart
     */
    protected function _unserializeField(Varien_Object $object, $field, $defaultValue = null, $callback = null)
    {
        if (!is_callable($callback)) {
            $callback = 'unserialize';
        }

        $value = $object->getData($field);
        if (empty($value)) {
            $object->setData($field, $defaultValue);
        } elseif (!is_array($value) && !is_object($value)) {
            $object->setData($field, call_user_func($callback, $value));
        }
    }


    /**
     * Retrieve select object for load object data
     * Extended to allow for array's to be passed to the load function
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $values = $value;
        if (is_array($value)) {
            $conditions = array();
            foreach ($values as $field => $value) {
                $field = $this->_getReadAdapter()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
                $conditions[] = $this->_getReadAdapter()->quoteInto($field . '=?', $value);
            }
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where(implode(' AND ', $conditions));
            return $select;
        } else {
            return parent::_getLoadSelect($field, $value, $object);
        }

    }
}
