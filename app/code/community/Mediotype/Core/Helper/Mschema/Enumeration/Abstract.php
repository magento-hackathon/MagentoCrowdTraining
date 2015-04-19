<?php

/**
 * Enumerable Class type abstract for enumerable objects parsed by the enumeration validator
 *
 * //Todo add logic to 'create' attribute value
 *
 * @authr   Joel Hart   @mediotype
 */
class Mediotype_Core_Helper_Mschema_Enumeration_Abstract
{

    protected $enumerable; //Requires array of enumerable values, simple indexed array, do not use key pairs until functionality is implemented to handle that

    /**
     * @return array
     */
    public function getEnumerable()
    {
        return $this->enumerable;
    }

    /**
     * Enforces Data Check Of Enumerable Property
     *
     * @throws Mediotype_Core_Exception
     */
    public function _construct()
    {
        if (empty($this->enumerable) || !is_array($this->enumerable)) {
            throw new Mediotype_Core_Exception("getEnumerable Did Not Return an Array in an Enumerable Instantiation");
        }
    }

    /**
     * This function is basic, but can be extended
     *
     * @return bool
     */
    public function evaluateEnumerable($validationObject, &$data)
    {
        if (array_search($data, $this->getEnumerable())) {
            return true;
        }
        return false;
    }



}