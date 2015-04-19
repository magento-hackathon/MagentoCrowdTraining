<?php

/**
 *
 * @author      Joel Hart   @mediotype
 */
class Mediotype_Core_Helper_Mschema_Enumeration_Attribute extends Mediotype_Core_Helper_Mschema_Enumeration_Abstract {

	protected $attributeCode; //Requires attribute code string

	protected $debug = false;

	/**
	 * @return string
	 */
	public function getAttributeCode() {
		return $this->attributeCode;
	}

	//TODO also check value is in array
	public function evaluateEnumerable( $validationObject, &$data ) {


		if ( $this->getAttributeCode() == 'feature_filter_envelope_group' /* $this->debug */ ) {
			$reportVal = $data;
			if ( is_array( $reportVal ) ) {
				$reportVal = implode( " --- ", $reportVal );
			}
			/* Verbos values for attributes $message = sku,attribute_code,assigned_value */
			Checkerboard_DataAdapters_Helper_Report::auditAttributeValuesInImportNoSkusCSV(
				$this->getAttributeCode() . ',' . $reportVal,
				$this->getAttributeCode() . ".log"
			);
		}

		if ( $this->debug ) {
			Mage::log(
				array(
					"CLASS"    => __CLASS__,
					"LINE"     => __LINE__,
					"MESSAGE"  => "PROCESSING VALUE --",
					"VALUE"    => var_export( $data, true ),
					"ATT CODE" => $this->getAttributeCode(),
					"CLASS"    => get_class( $this )
				),
				null,
				'enumerable-attribute.log'
			); //Processing Value
		}

		$helper = Mage::helper( 'mediotype_core/magento_attribute' );
		/** @var Mediotype_Core_Helper_Magento_Attribute $helper */

		if ( is_array( $data ) ) {
			if ( $this->debug ) {
				Mage::log(
					array(
						"CLASS"   => __CLASS__,
						"LINE"    => __LINE__,
						"MESSAGE" => "DATA ASSIGNED IN IS ARRAY LOGIC"
					),
					null,
					'enumerable-attribute.log'
				);
			}

			foreach ( $data as $checkMe ) {

				if ( $this->checkEnumerableString( $checkMe ) == false ) {
					return false;
				}

				if ( $this->checkDbForAttributeValue( $checkMe ) == false ) {
					return false;
				}
			}

			return true;
		}

		if ( is_numeric( $data ) ) {
			if ( $this->debug ) {
				Mage::log(
					array(
						"CLASS"   => __CLASS__,
						"LINE"    => __LINE__,
						"MESSAGE" => "DATA IN IS NUMERIC LOGIC"
					),
					null,
					'enumerable-attribute.log'
				);
			}

			$labels = $helper->getOptionLabelsById( $data );
			if ( $this->debug ) {
				Mage::log( $labels->getData(), null, 'attributeid.labels.log' );
			}
			if ( $labels == false ) {
				return false;
			}
			$defaultValue = $labels->getDefaultValue();

			$data = $defaultValue;

			return true;
		}

		if ( is_string( $data ) ) {

			if ( property_exists( $this, 'useUcwords' ) && is_string( $data ) ) {
				$data = $this->preserveSpaceUcWords( $data );
			}

			if ( property_exists( $this, 'importDataMap' ) && array_key_exists( $data, $this->importDataMap ) ) {
				$data = $this->importDataMap[ $data ];
			}

			if ( $this->checkEnumerableString( $data ) == false ) {
				return false;
			}

			if ( $this->checkDbForAttributeValue( $data ) == false ) {
				return false;
			}

			return true;
		}

		return false;
	}

	protected function checkDbForAttributeValue( $string ) {
		$helper = Mage::helper( 'mediotype_core/magento_attribute' );
		/** @var Mediotype_Core_Helper_Magento_Attribute $helper */

		$valueId = $helper->getValueIdByCodeAndValue( $this->getAttributeCode(), $string );

		if ( empty( $valueId ) ) {
			Mage::log(
				array(
					"CLASS"   => __CLASS__,
					"LINE"    => __LINE__,
					"MESSAGE" => "PROVIDED VALUE NOT IN MAGENTO DB AS ATTRIBUTE OPTION",
					"VALUE"   => $string
				),
				null,
				'import-exception.log'
			); //Value not in Magento DB

			return false;
		}

		return true;
	}

	protected function checkEnumerableString( $string ) {

		if ( property_exists( $this, 'useUcwords' ) && is_string( $string ) ) {
			foreach ( $this->getEnumerable() as $enumVal ) {
				if ( strtolower( $string ) == strtolower( $enumVal ) ) {
					return true;
				}
			}

		} else {

			if ( array_search( $string, $this->getEnumerable() ) !== false ) {
				return true;
			}
		}

		if ( ! empty( $string ) ) {
			Mage::log(
				array(
					"CLASS"      => __CLASS__,
					"LINE"       => __LINE__,
					"MESSAGE"    => "FAILED TO IMPORT ATTRIBUTE VALUE",
					"CODE"       => $this->getAttributeCode(),
					"VALUE"      => $string,
					"ENUMERABLE" => $this->getEnumerable()
				),
				null,
				'import-exception.log'
			);
		}

		return false;
	}

	/**
	 * TRUNCATES all option values for an attribute, then creates new ones based on the enumerable array
	 *
	 * @param int $storeId
	 *
	 * @throws Mediotype_Core_Exception
	 * @throws Exception
	 */
	public function resetValuesToEnumerableOptions() {

		$helper = Mage::helper( 'mediotype_core/magento_attribute' );
		/** @var Mediotype_Core_Helper_Magento_Attribute $helper */

		$enumerable = $this->getEnumerable();
		if ( empty( $enumerable ) || ! is_array( $enumerable ) ) {
			throw new Mediotype_Core_Exception(
				"Enforcing Enumeration Attribute Value Options Failed, getEnumerable returned empty or non Array"
			);
		}

		if ( ! $helper->getAttributeByCode( $this->getAttributeCode() ) ) {
			throw new Mediotype_Core_Exception(
				"Enforcing Enumeration Attribute Value Options Failed To Load Attribute By Code!!!"
			);
		}

		try {
			$helper->truncateAllOptionsByCode( $this->getAttributeCode() );

			foreach ( $enumerable as $newOptionValue ) {
				if ( property_exists( $this, 'useUcwords' ) ) {
					$newOptionValue = $this->preserveSpaceUcWords( $newOptionValue );
				}
				$helper->addAttributeOptionValueForAttributeCode( $this->getAttributeCode(), $newOptionValue );
			}
		} catch ( Exception $e ) {
			Mediotype_Core_Helper_Debugger::log( "FAILED IN ENFORCE ENUMERABLE VALUE OPTIONS" );
			throw $e;
		}

	}

	public function preserveSpaceUcWords( $value ) {
		return trim( uc_words( $value, " " ) ); // Magento overrides this in functions.php
	}

	public function verifyAttributeOptionIntegrity() {

		$helper = Mage::helper( 'mediotype_core/magento_attribute' );
		/** @var Mediotype_Core_Helper_Magento_Attribute $helper */

		$enumerable = $this->getEnumerable();

		return true; //TODO Finish this function that matches values with enumerable set completely

	}

}