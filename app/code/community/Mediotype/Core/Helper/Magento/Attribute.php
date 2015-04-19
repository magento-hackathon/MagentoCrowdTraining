<?php

/**
 * Useful interactions with attributes and the eav system
 *
 * @author  Joel Hart   @Mediotype
 */
class Mediotype_Core_Helper_Magento_Attribute extends Mediotype_Core_Helper_Abstract {

	/**
	 * Check to verify if a product attribute exists in the database, by attribute_code value
	 *
	 * @param string $attributeCode
	 *
	 * @return bool
	 */
	public function doesProductAttributeExistByCode( $attributeCode ) {

		$read                = $this->getCoreSetup();
		$productEntityTypeId = $read->fetchOne( "SELECT `entity_type_id`
												FROM `eav_entity_type`
												WHERE `entity_type_code`='" . Mage_Catalog_Model_Product::ENTITY . "'" );


		if ( $read->fetchOne( "SELECT `attribute_code`
							 FROM `eav_attribute`
							 WHERE `attribute_code`='$attributeCode'
							 AND `entity_type_id` = '$productEntityTypeId'" )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get product attribute model or false
	 *
	 * @param string $attributeCode
	 *
	 * @return bool|Mage_Eav_Model_Entity_Attribute
	 */
	public function getAttributeByCode( $attributeCode ) {
		$attributeModel = Mage::getModel( 'eav/entity_attribute' );
		$attributeId    = $attributeModel->getIdByCode( Mage_Catalog_Model_Product::ENTITY, $attributeCode );
		$attribute      = Mage::getModel( 'eav/entity_attribute' )->load( $attributeId );
		if ( $attribute->getId() ) {
			return $attribute;
		} else {
			return false;
		}
	}

	/**
	 * Get An Attribute Set Model By Name & Entity Type String
	 * Defaults to Default Attribute Set & Product Entity Type
	 *
	 * @param String $attributeSetName
	 *
	 * @return bool| Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function getAttributeSetByName(
		$attributeSetName = "Default",
		$entityTypeIdentifier = Mage_Catalog_Model_Product::ENTITY
	) {
		if ( ! empty( $attributeSetName ) ) {
			$entityTypeId = Mage::getModel( 'eav/entity' )->setType( $entityTypeIdentifier )->getTypeId(); //Product Entity Type ID

			$attributeSetCollection = Mage::getResourceModel( 'eav/entity_attribute_set_collection' )
			                              ->setEntityTypeFilter( $entityTypeId )
			                              ->addFilter( 'attribute_set_name', $attributeSetName )->load();

			if ( count( $attributeSetCollection->getItems() ) > 0 ) {
				$attributeSet = $attributeSetCollection->getFirstItem();

				/** @var Mage_Eav_Model_Entity_Attribute_Set $attributeSet */

				return $attributeSet;
			}
		}

		return false;
	}

	/**
	 * Adds an attribute to an attribute set in the default set group
	 *
	 * @param string | Mage_Eav_Model_Entity_Attribute $attribute
	 * @param string                                   $attributeSetName
	 * @param string                                   $entityTypeIdentifier
	 * @param string                                   $assignToGroup
	 */
	public function addProductAttributeToSetInDefaultGroup(
		$attribute,
		$attributeSetName,
		$entityTypeIdentifier = Mage_Catalog_Model_Product::ENTITY,
		$assignToGroup = "default"
	) {

		if ( is_string( $attribute ) ) {
			$attribute = $this->getAttributeByCode( $attribute );
			/** @var Mage_Eav_Model_Entity_Attribute $attribute */
		}

		$attributeSet = $this->getAttributeSetByName( $attributeSetName );
		/** @var Mage_Eav_Model_Entity_Attribute_Set $attributeSet */

		if ( $assignToGroup == "default" ) {
			$attributeGroupId = $attributeSet->getDefaultGroupId();
		} else {
			//TODO - build functionality for non default group, Magento's built in versions lack luster
		}

		$setup = Mage::getModel( 'eav/entity_setup' );
		/** @var Mage_Eav_Model_Entity_Setup $setup */

		$setup->addAttributeToGroup( $entityTypeIdentifier,
			$attributeSet->getId(),
			$attributeGroupId,
			$attribute->getId() );

	}

	/**
	 * @param String $groupName
	 * @param String $attributeSet
	 *
	 * @return bool | Mage_Eav_Model_Resource_Entity_Attribute_Group
	 */
	public function getAttributeGroupByName( $groupName, $attributeSet ) {

		$attributeGroup           = Mage::getModel( 'eav/entity_attribute_group' );
		$attributeGroupCollection = $attributeGroup
			->getCollection()
			->addFieldToFilter( 'attribute_set_id', $attributeSet->getAttributeSetId() )
			->addFieldToFilter( 'attribute_group_name', $groupName );
		if ( $attributeGroupCollection->count() > 0 ) {
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
	 * @param String $attributeCode
	 * @param int    $storeId
	 * @param string $entityTypeIdentifier
	 *
	 * @return array|bool
	 */
	public function getAllAttributeOptionsByCode(
		$attributeCode,
		$storeId = 0,
		$entityTypeIdentifier = Mage_Catalog_Model_Product::ENTITY
	) {

		$attributeCode = trim( $attributeCode ); //just to be safe

		$attributeModel = Mage::getModel( 'eav/entity_attribute' );
		/** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
		$attributeId = $attributeModel->getIdByCode( $entityTypeIdentifier, $attributeCode ); //use model to find id
		$attribute   = $attributeModel->load( $attributeId ); //load model's resource from db
		$attribute->setStoreId(
			$storeId
		); // set baseline store id for all options return, default is admin a.k.a. global

		$attributeOptionsModel = Mage::getModel( 'eav/entity_attribute_source_table' );
		/** @var Mage_Eav_Model_Entity_Attribute_Source_Table $attributeOptionsModel */

		$attributeTable = $attributeOptionsModel->setAttribute( $attribute ); //Assigned intentionally though not used
		$options        = $attributeOptionsModel->getAllOptions(
			false
		); //returns only options for the set store id, very complicated if set to true

		if ( count( $options ) > 0 ) {
			return $options;
		}

		return false;
	}

	/**
	 * Removes all attribute option values for a given attribute code
	 *
	 * @param     $attributeCode
	 * @param int $storeId
	 */
	public function truncateAllOptionsByCode(
		$attributeCode,
		$storeId = 0,
		$entityTypeIdentifier = Mage_Catalog_Model_Product::ENTITY
	) {

		$attributeModel = Mage::getModel( 'eav/entity_attribute' );
		/** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
		$attributeId = $attributeModel->getIdByCode( $entityTypeIdentifier, $attributeCode ); //use model to find id
		$attribute   = $attributeModel->load( $attributeId ); //load model's resource from db
		$attribute->setStoreId(
			$storeId
		); // set baseline store id for all options return, default is admin a.k.a. global

		$collection = Mage::getResourceModel( 'eav/entity_attribute_option_collection' )
		                  ->setPositionOrder( 'asc' )
		                  ->setAttributeFilter( $attribute->getId() )
		                  ->setStoreFilter( $attribute->getStoreId() )
		                  ->load();

		foreach ( $collection->getItems() as $item ) {
			$item->delete();
		}
	}

	/**
	 * Get an array of option values by option ID #
	 *
	 * @param        $optionId
	 * @param string $store
	 *
	 * @return Mage_Eav_Model_Entity_Attribute_Option | false
	 */
	public function getOptionLabelsById( $optionId, $storeId = 0 ) {

		$collection = Mage::getResourceModel( 'eav/entity_attribute_option_collection' )
		                  ->setPositionOrder( 'asc' )
		                  ->setIdFilter( $optionId )
		                  ->setStoreFilter( $storeId )
		                  ->load();

		if ( count( $collection->getItems() > 0 ) ) {
			return $collection->getFirstItem();
		}

		return false;
	}


	/**
	 * Takes an Attribute Code, and a Value, and returns the option_id, optionally takes a store ID (multistore/multilocale scenarios)
	 * Default uses catalog_product entity type identifier and admin store id (0)
	 *
	 * @param String $attributeCode        magento compliant attribute code
	 * @param String $value                What are we trying to match?
	 * @param Int    $storeId              Default is 0 || admin a.k.a. Global
	 * @param String $entityTypeIdentifier from eav_entity_type table in DB
	 *
	 * @return Int | bool
	 */
	public function getValueIdByCodeAndValue(
		$attributeCode,
		$value,
		$storeId = 0,
		$entityTypeIdentifier = Mage_Catalog_Model_Product::ENTITY
	) {
		$value = trim( $value ); //just to be safe

		$attributeModel = Mage::getModel( 'eav/entity_attribute' );
		/** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
		$attributeId = $attributeModel->getIdByCode( $entityTypeIdentifier, $attributeCode ); //use model to find id
		$attribute   = $attributeModel->load( $attributeId ); //load model's resource from db
		$attribute->setStoreId(
			$storeId
		); // set baseline store id for all options return, default is admin a.k.a. global

		$attributeOptionsModel = Mage::getModel( 'eav/entity_attribute_source_table' );
		/** @var Mage_Eav_Model_Entity_Attribute_Source_Table $attributeOptionsModel */

		$attributeTable = $attributeOptionsModel->setAttribute( $attribute ); //Assigned intentionally though not used
		$options        = $attributeOptionsModel->getAllOptions(
			false
		); //returns only options for the set store id, very complicated if set to true

		foreach ( $options as $option ) {
			if ( trim( $option['label'] ) == $value ) {
				return $option['value'];
			}
		}

		return false;
	}

	/**
	 * Takes an attribute_code and new value, and adds the value to the attribute's options, optionally can take a store id and apply to specific store id
	 *
	 * @param String $attributeCode
	 * @param String $newValue
	 * @param Int    $storeId
	 *
	 * @return bool|Int If != false, returns new option ID for value
	 * @throws Exception
	 */
	public function addAttributeOptionValueForAttributeCode( $attributeCode, $newValue, $storeId = 0 ) {
		$newValue = trim( $newValue ); //just to be safe

		$attributeModel = Mage::getModel( 'eav/entity_attribute' );
		/** @var Mage_Eav_Model_Entity_Attribute $attributeModel */
		$attributeId = $attributeModel->getIdByCode( 'catalog_product', $attributeCode );

		if ( empty( $attributeId ) ) {
			throw new Mediotype_Core_Exception(
				"Failed to load attribute to add options (attribute_code: $attributeCode )"
			);
		}

		/** @var $option A uniquely formatted array to meet Magento's expectations */
		$option = array(
			"attribute_id" => $attributeId
		);

		$option['value'][0][ $storeId ] = $newValue; //Set the value for the provided or default store Id to the new value

		$setup = new Mage_Eav_Model_Entity_Setup( 'core_setup' );
		$setup->addAttributeOption( $option );

		return $this->getValueIdByCodeAndValue( $attributeCode, $newValue );
	}

	public function createNewAttributeSet() {

	}

	/**
	 * Apply Attribute To Product Types | Default ALL | Or Array Of Type Strings
	 *
	 * @param       mixed         String | Mage_Catalog_Model_Entity_Attribute $attribute
	 * @param array $productTypes example: array('simple','bundle')
	 *
	 * @return bool
	 */
	public function applyAttributeToProductTypes( $attribute, $productTypes = array() ) {

		if ( is_string( $attribute ) ) {
			$attribute = $this->getAttributeByCode( $attribute );
		}

		if ( ! is_a( $attribute, 'Mage_Catalog_Model_Entity_Attribute' ) ) {
			return false;
		}

		$allTypes = Mage::getConfig()->getNode( 'global/catalog/product/type' )->asArray();
		if ( empty( $productTypes ) ) {
			$productTypes = $allTypes;
		} else {
			foreach ( $productTypes as $type ) {
				if ( ! in_array( $type, $allTypes ) ) {
					return false;
				}
				/** @var $attribute Mage_Catalog_Model_Entity_Attribute */
				$attribute->setApplyTo( $type );
			}
		}

		return true;
	}

	/**
	 * Remove Attribute Set From Magento
	 *
	 * @param        $attributeSetName
	 * @param string $entityTypeIdentifier
	 */
	public function deleteAttributeSetByName(
		$attributeSetName,
		$entityTypeIdentifier = Mage_Catalog_Model_Product::ENTITY
	) {
		if ( $attributeSet = $this->getAttributeSetByName( $attributeSetName, $entityTypeIdentifier ) ) {
			$attributeSet->delete();
		}
	}

	/**
	 * Removes EAV Attribute from Magento - defaults to catalog product entity type
	 *
	 * @param        $attributeCode
	 * @param string $entityTypeIdentifier
	 */
	public function deleteAttribute( $attributeCode, $entityTypeIdentifier = Mage_Catalog_Model_Product::ENTITY ) {

		$attributeCode = trim( $attributeCode );

		try {
			$write = $this->getCoreWrite();
			if ( $write->fetchOne( "SELECT `attribute_code`
								 FROM `eav_attribute`
								 WHERE `attribute_code`='$attributeCode'
								 AND `entity_type_id` = '$entityTypeIdentifier'" )
			) {
				$write->query( "DELETE
								 FROM `eav_attribute`
								 WHERE `attribute_code`='$attributeCode'
								 AND `entity_type_id` = '$entityTypeIdentifier'" );
			}
		} catch ( Exception $e ) {
			Mage::throwException( $e->getMessage() );
		}
	}

	/**
	 * Create a new attribute code following Magento standard given an attribute name
	 * Basically performs string conversion to remove spaces
	 *
	 * @param String $subject
	 * @param string $replacementChar
	 * @param int    $maxLength
	 *
	 * @return mixed|string
	 */
	protected function createCompliantAttributeCodeFromString( $subject, $replacementChar = '_', $maxLength = 30 ) {
		$specialCharacters = ' !@#$%^&*()_+-={}|[]\\:";\'<>?,./~`'; //Validate special characters are removed from name
		for ( $i = 0; $i < strlen( $specialCharacters ); $i ++ ) {
			$subject = str_replace( substr( $specialCharacters, $i, 1 ), $replacementChar, $subject );
		}
		$subject = str_replace( $replacementChar . $replacementChar, $replacementChar, $subject );
		if ( strlen( $subject ) > $maxLength ) { // ATTRIBUTE CODE MUST BE LESS THEN 30 CHARACTERS
			$subject = substr( $subject, 0, $maxLength );
		}
		$subject = strtolower( $subject );

		return $subject;
	}


}