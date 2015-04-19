<?php
/**
 * Useful interactions with attributes and the eav system
 *
 *
 */
class Mediotype_Core_Helper_Import_Attribute extends Mediotype_Core_Helper_Abstract{


    /**
     * Get product attribute model or false
     *
     * @param string    $attributeCode
     *
     * @return bool|Mage_Eav_Model_Entity_Attribute
     */
    public function getAttributeByCode($attributeCode)
    {
        $attributeModel = Mage::getModel('eav/entity_attribute');
        $attributeId = $attributeModel->getIdByCode('catalog_product', $attributeCode);
        $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
        if ($attribute->getId()) {
            return $attribute;
        } else {
            return false;
        }
    }

    /**
     * Get An Attribute Set Model By Name & Entity Type String
     * Defaults to Default Attribute Set & Product Entity Type
     *
     * @param String    $attributeSetName
     *
     * @return bool| Mage_Eav_Model_Entity_Attribute_Set
     */
    public function getAttributeSetByName($attributeSetName = "Default", $entityTypeName = 'catalog_product')
    {
        if (!empty($attributeSetName)) {
            $entityTypeId = Mage::getModel('eav/entity')->setType($entityTypeName)->getTypeId(); //Product Entity Type ID

            $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter($entityTypeId)
                ->addFilter('attribute_set_name', $attributeSetName)->load();

            if (count($attributeSetCollection->getItems()) > 0) {
                $attributeSet = $attributeSetCollection->getFirstItem();
                return $attributeSet;
            }
        }

        return false;
    }

    /**
     * @param String    $groupName
     * @param String    $attributeSet
     *
     * @return bool | Mage_Eav_Model_Resource_Entity_Attribute_Group
     */
    public function getAttributeGroupByName($groupName, $attributeSet)
    {

        $attributeGroup = Mage::getModel('eav/entity_attribute_group');
        $attributeGroupCollection = $attributeGroup
            ->getCollection()
            ->addFieldToFilter('attribute_set_id', $attributeSet->getAttributeSetId())
            ->addFieldToFilter('attribute_group_name', $groupName);
        if ($attributeGroupCollection->count() > 0) {
            // GROUP EXISTS
            $attributeGroup = $attributeGroupCollection->getFirstItem();
            return $attributeGroup;
        }

        return false;
    }

    /**
     * Returns an array of arrays like ("label"=>"stuff", "value"=>"###")
     * Where the label is named value and the value is the option_id
     *
     * @param String    $attributeCode
     * @param int       $storeId
     * @param string    $entityTypeIdentifier
     *
     * @return array|bool
     */
    public function getAllAttributeOptionsByCode($attributeCode, $storeId = 0, $entityTypeIdentifier = 'catalog_product'){

        $attributeModel = Mage::getModel('eav/entity_attribute'); /** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
        $attributeId = $attributeModel->getIdByCode($entityTypeIdentifier, $attributeCode); //use model to find id
        $attribute = $attributeModel->load($attributeId); //load model's resource from db
        $attribute->setStoreId( $storeId ); // set baseline store id for all options return, default is admin a.k.a. global

        $attributeOptionsModel = Mage::getModel('eav/entity_attribute_source_table'); /** @var Mage_Eav_Model_Entity_Attribute_Source_Table $attributeOptionsModel */

        $attributeTable = $attributeOptionsModel->setAttribute($attribute); //Assigned intentionally though not used
        $options = $attributeOptionsModel->getAllOptions(false); //returns only options for the set store id, very complicated if set to true

        if(count($options) > 0){
            return $options;
        }

        return false;
    }

    /**
     * Removes all attribute option values for a given attribute code
     *
     * @param $attributeCode
     * @param int $storeId
     */
    public function truncateAllOptionsByCode($attributeCode, $storeId = 0){

        $attributeModel = Mage::getModel('eav/entity_attribute'); /** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
        $attributeId = $attributeModel->getIdByCode($entityTypeIdentifier, $attributeCode); //use model to find id
        $attribute = $attributeModel->load($attributeId); //load model's resource from db
        $attribute->setStoreId( $storeId ); // set baseline store id for all options return, default is admin a.k.a. global

        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->setAttributeFilter($attribute->getId())
            ->setStoreFilter($attribute->getStoreId())
            ->load();

        foreach($collection->getItems() as $item){
             $item->delete();
        }
    }



    /**
     * Takes an Attribute Code, and a Value, and returns the option_id, optionally takes a store ID (multistore/multilocale scenarios)
     * Default uses catalog_product entity type identifier and admin store id (0)
     *
     * @param String $attributeCode magento compliant attribute code
     * @param String $value What are we trying to match?
     * @param Int    $storeId  Default is 0 || admin a.k.a. Global
     * @param String $entityTypeIdentifier  from eav_entity_type table in DB
     *
     * @return Int | bool
     */
    public function getValueIdByCodeAndValue($attributeCode, $value, $storeId = 0, $entityTypeIdentifier = 'catalog_product')
    {
        $value = trim($value); //just to be safe

        $attributeModel = Mage::getModel('eav/entity_attribute'); /** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
        $attributeId = $attributeModel->getIdByCode($entityTypeIdentifier, $attributeCode); //use model to find id
        $attribute = $attributeModel->load($attributeId); //load model's resource from db
        $attribute->setStoreId( $storeId ); // set baseline store id for all options return, default is admin a.k.a. global

        $attributeOptionsModel = Mage::getModel('eav/entity_attribute_source_table'); /** @var Mage_Eav_Model_Entity_Attribute_Source_Table $attributeOptionsModel */

        $attributeTable = $attributeOptionsModel->setAttribute($attribute); //Assigned intentionally though not used
        $options = $attributeOptionsModel->getAllOptions(false); //returns only options for the set store id, very complicated if set to true

        foreach ($options as $option) {
            if (trim($option['label']) == $value) {
                return $option['value'];
            }
        }

        return false;
    }

    /**
     * Takes an attribute_code and new value, and adds the value to the attribute's options, optionally can take a store id and apply to specific store id
     *
     * @param String    $attributeCode
     * @param String    $newValue
     * @param Int       $storeId
     *
     * @return bool|Int If != false, returns new option ID for value
     * @throws Exception
     */
    public function addAttributeOptionValueForAttributeCode($attributeCode, $newValue, $storeId = 0)
    {
        $newValue = trim($newValue); //just to be safe

        $attributeModel = Mage::getModel('eav/entity_attribute'); /** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
        $attributeId = $attributeModel->getIdByCode('catalog_product', $attributeCode);

        if (empty($attributeId)) {
            throw new Mediotype_Core_Exception("Failed to load attribute to add options (attribute_code: $attributeCode )");
        }

        /** @var $option A uniquely formatted array to meet Magento's expectations */
        $option = array(
            "attribute_id" => $attributeId
        );

        $option['value'][0][$storeId] = $newValue; //Set the value for the provided or default store Id to the new value

        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($option);

        return $this->getValueIdByCodeAndValue($attributeCode, $newValue);
    }

    public function createNewAttributeSet(){

    }

    /**
     * Create a new attribute code following Magento standard given an attribute name
     * Basically performs string conversion to remove spaces
     *
     * @param String    $subject
     * @param string    $replacementChar
     * @param int       $maxLength
     *
     * @return mixed|string
     */
    protected function createCompliantAttributeCodeFromString($subject, $replacementChar = '_', $maxLength = 30)
    {
        $specialCharacters = ' !@#$%^&*()_+-={}|[]\\:";\'<>?,./~`'; //Validate special characters are removed from name
        for ($i = 0; $i < strlen($specialCharacters); $i++) {
            $subject = str_replace(substr($specialCharacters, $i, 1), $replacementChar, $subject);
        }
        $subject = str_replace($replacementChar . $replacementChar, $replacementChar, $subject);
        if (strlen($subject) > $maxLength) { // ATTRIBUTE CODE MUST BE LESS THEN 30 CHARACTERS
            $subject = substr($subject, 0, $maxLength);
        }
        $subject = strtolower($subject);
        return $subject;
    }

}