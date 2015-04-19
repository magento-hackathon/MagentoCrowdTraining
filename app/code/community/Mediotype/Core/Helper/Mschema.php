<?php

/**
 * mSchema Master class and default schemas
 *
 * @license     http://mediotype.com/LICENSE.txt
 * @author      Mediotype (SZ,JH) <diveinto@mediotype.com>
 */
class Mediotype_Core_Helper_Mschema {

	const EC_REQUIRED_NODE = 1001;

	const EC_STRUCTURE_MALFORMED = 1003;

	protected $validationObject;

	protected $currentValidationNode;

	protected $lastNode;

	protected $validators;

	private $debug;

	private $debugLevel = 1;

	const MSCHEMA_DEBUG_LEVEL_FULL = 3;
	const MSCHEMA_DEBUG_LEVEL_ERRORS = 2;
	const MSCHEMA_DEBUG_LEVEL_WARNINGS = 1;

	/**
	 * Sets debugger status and default validator classes
	 */
	public function __construct() {
		$this->debug = Mage::helper( 'mediotype_core/debugger' );
		$this->debug->setEnabled( false );

		// ************* LOAD CORE VALIDATORS *************
		$this->validators = array(
			"mediotype_core/mschema_validator_strip",
			"mediotype_core/mschema_validator_trim",
			"mediotype_core/mschema_validator_ucwords",
			"mediotype_core/mschema_validator_regex",
			"mediotype_core/mschema_validator_datatype",
			"mediotype_core/mschema_validator_cast",
			"mediotype_core/mschema_validator_forcevalue",
			"mediotype_core/mschema_validator_parseavp",
			"mediotype_core/mschema_validator_explode",
			"mediotype_core/mschema_validator_download",
			"mediotype_core/mschema_validator_enumeration",
			"mediotype_core/mschema_validator_Htmlentities",
			"mediotype_core/mschema_validator_magento_yesno"
		);

		$this->currentValidationNode = null;
	}

	/**
	 * @return array
	 */
	public function getValidators() {
		return $this->validators;
	}

	/**
	 * @param $class
	 *
	 * @return Mediotype_Core_Helper_Mschema
	 */
	public function addValidator( $class ) {
		if ( is_string( $class ) ) {
			$this->validators[ $class ] = $class;
		}

		if ( is_array( $class ) ) {
			foreach ( $class as $validatorClass ) {
				$this->_validators[ $validatorClass ] = $validatorClass;
			}
		}

		return $this;
	}

	/**
	 * @param $target
	 *
	 * @return bool
	 */
	public function removeValidator( $target ) {
		if ( array_key_exists( $target, $this->validators ) ) {
			unset( $this->validators[ $target ] );

			return true;
		}

		return false;
	}

	/**
	 * Loads a json schema file into the validator
	 *
	 * @param  $filePath String
	 *
	 * @return Mediotype_Core_Helper_Mschema
	 * @throws Mediotype_Core_Helper_Mschema_Exception
	 */
	public function LoadSchema( $filePath ) {

		if ( ! is_string( $filePath ) ) {
			throw new Mediotype_Core_Helper_Mschema_Exception(
				"mSchema LoadSchema method did not receive a valid String for filePath,", 1000
			);
		}

		if ( ! file_exists( $filePath ) ) {
			Mage::throwException( "Schema File --{$filePath}-- Does Not Exist," );
		}

		if ( $ioHandler = fopen( $filePath, "r" ) ) {
			$json = fread( $ioHandler, filesize( $filePath ) );
			fclose( $ioHandler );

			$this->validationObject = json_decode( $json );  //Evaluate validity of JSON

			if ( is_null( $this->validationObject ) ) {
				throw new Mediotype_Core_Helper_Mschema_Exception( "Failed to parse json file :: " . $filePath, 1000 );
			}

			$this->resetValidationNodes();

			return $this;
		}

		throw new Mediotype_Core_Helper_Mschema_Exception( "Failed to open file in LoadSchema method,", 1000 );
	}

	/**
	 * Resets current validation node
	 *
	 */
	public function resetValidationNodes() {
		$this->currentValidationNode = $this->validationObject;
	}


	/**
	 * @param  $data  Array | StdClass | Object
	 * @param  $depth int
	 *
	 * @return Mediotype_Core_Model_Response
	 */
	public function Validate( &$data, $depth = 0 ) {
		$returnObject = new Mediotype_Core_Model_Response(
			__METHOD__,
			array(),
			Mediotype_Core_Model_Response::OK
		);

		$depth += 1; /* TRACK DEPTH OF RECURSION */

		foreach ( $this->currentValidationNode as $index => $value ) {
			/* Process Schema Nesting */

			$this->debug->logRecursion( $depth, "NODE --{$index}-- **ENTERS LOOP**" );


			if ( ( ! property_exists( (object) $data, $index ) || ! isset( $data->$index ) )
			     && isset( $this->currentValidationNode->$index->default )
			) {
				/* Set Default Value If Defined In Schema*/
				if ( $this->debugLevel == 3 ) {

					Mage::log(
						array(
							"CLASS"   => __CLASS__,
							"LINE"    => __LINE__,
							"MESSAGE" => "SETTING DEFAULT for --{$index}--"
						),
						null,
						'parsing.log'
					);
				}
				$this->debug->logRecursion( $depth, "NODE --{$index}-- **SET DEFAULT AS VALUE**" );
				$data->$index = $this->currentValidationNode->$index->default;
			}

			if ( isset( $data->$index ) ) {
				/* If the index is found in the data */

				$this->debug->logRecursion( $depth, "NODE --{$index}-- **IS FOUND** IN SUPPLIED DATA" );

				if ( is_object( $this->currentValidationNode->$index ) ) {
					$this->processObject( $data, $depth, $index, $returnObject );

				}

				if ( is_array( $this->currentValidationNode->$index ) ) {

					$this->processArray( $data, $depth, $index, $returnObject );

				}

			} else {
//                $this->debug->logRecursion($depth, "NODE --{$index}-- *WAS NOT* FOUND IN SUPPLIED DATA");
				$isNodeRequired = false;
				if ( is_array( $this->currentValidationNode->$index ) ) {
					if ( isset( $this->currentValidationNode->$index[0]->required ) ) {
						$isNodeRequired = $this->currentValidationNode->$index[0]->required;
					} else {
						$isNodeRequired = false;
					}

				}
				if ( is_object( $this->currentValidationNode->$index ) ) {
					if ( isset( $this->currentValidationNode->$index->required ) ) {
						$isNodeRequired = $this->currentValidationNode->$index->required;
					} else {
						$isNodeRequired = false;
					}
				}

				if ( $isNodeRequired ) {
					/* Handle a missing required node as defined in the Json Schema */
					$this->debug->logRecursion( $depth, "NODE --{$index}--) **IS REQUIRED**" );
					$returnObject->disposition    = Mediotype_Core_Model_Response::FATAL;
					$returnObject->data[ $index ] = new Mediotype_Core_Model_Response(
						__METHOD__,
						$index,
						Mediotype_Core_Model_Response::FATAL,
						"MISSING REQUIRED NODE --{$index}--",
						Mediotype_Core_Helper_Mschema::EC_REQUIRED_NODE
					);
				}

			}

		}


		if ( $returnObject->disposition !== Mediotype_Core_Model_Response::OK ) {
			/* NOTE: THE ORDER IN WHICH THE RETURN OBJECT IS BUILT WILL BE THE ORDER IN WHICH IT IS RETURNED */
			$returnObject->description = "Failed mSchema Validation";
			if ( $this->debugLevel == 3 ) {

				Mage::log(
					array(
						"MESSAGE"  => "FAILED MSCHEMA PARSER",
						"RESPONSE" => $returnObject,
					),
					null,
					'import-exception.log'
				);
			}
		} else {
			$returnObject->description = "Passed mSchema Validation";
		}

		return $returnObject;
	}

	/**
	 * OBJECT VALIDATION PARSER
	 */
	protected function processObject( $data, $depth, $index, $returnObject ) {
		$this->debug->logRecursion( $depth, "NODE --{$index}-- **TYPE** OBJECT VALIDATION " );

		if ( $this->isEndNode( $this->currentValidationNode->$index ) == false ) {

			/* NOT END NODE */
			$this->debug->logRecursion( $depth, "NODE --{$index}-- **IS NOT** A END NODE" );

			$this->lastNode              = $this->cloneObject( $this->currentValidationNode );
			$this->currentValidationNode = $this->cloneObject( $this->currentValidationNode->$index );

			/* RECURSION */
			$this->debug->logRecursion( $depth, "NODE --{$index}--     +++ START Recursion on index " );
			$objectRecursionResponse     = $this->Validate( $data->$index, $depth ); //
			$this->currentValidationNode = $this->cloneObject( $this->lastNode );
			$this->debug->logRecursion( $depth, "NODE --{$index}--     --- END Recursion on index " );

			if ( $objectRecursionResponse->disposition !== Mediotype_Core_Model_Response::OK ) {
				$returnObject->data[ $index ] = $objectRecursionResponse;
				$returnObject->disposition    = Mediotype_Core_Model_Response::FATAL;
				$this->debug->logRecursion( $depth, "FAILURE IN CHILD NODE" );
			}

		}

		if ( $this->isEndNode( $this->currentValidationNode->$index ) ) {
			/* Detects End Node -- No Nested Schema Remaining -- */

			$this->debug->logRecursion( $depth, "NODE --{$index}-- **IS A** END NODE" );

			foreach ( $this->getValidators() as $className ) {
				/* Loop Through The Loaded Validator Classes For Matching Schema Declarations */

				$validator = null;

				if ( strstr( $className, "/" ) ) {
					/* Uses Magento 1.x Helper Short Syntax For Class Auto Loading */
					$validator = Mage::helper( $className );
				}

				if ( is_null( $validator ) ) {
					$this->debug->logRecursion(
						$depth,
						"***FAILED*** TO LOAD VALIDATOR --{$className}-- Continuing Loop"
					);
					continue;
				}

				if ( ! is_a( $validator, "Mediotype_Core_Helper_Mschema_Validator_Abstract" ) ) {
					/* Require Inheritance from the Validator Abstract, forcing canRead to exists */

					throw new Mediotype_Core_Exception(
						"Validation MUST Inherit From Mediotype_Core_Helper_Mschema_Validator_Abstract, " . get_class(
							$validator
						) . "  given"
					);

				}

				if ( $validator->canRead( $this->currentValidationNode->$index ) ) {
					$this->debug->logRecursion(
						$depth,
						" NODE --{$index}-- **VALIDATING** USING CLASS --{$className}--"
					);

					$response = $validator->Validate(
						$this->currentValidationNode->$index,
						$data->$index
					);

					$this->debug->logRecursion(
						$depth,
						"--{$className}-- **RESULTS** : " . print_r( $response, true )
					);


					if ( $response->disposition !== Mediotype_Core_Model_Response::OK ) {
						/*  CATCH ANY ERROR MSGS */
						$this->debug->logRecursion( $depth, "--{$index}-- **FAILED** VALIDATION" );
						$returnObject->data[ $index ] = $response;
						$returnObject->disposition    = Mediotype_Core_Model_Response::FATAL;
					}
				}

			}

			if ( $returnObject->disposition == Mediotype_Core_Model_Response::OK ) {
				$this->debug->logRecursion( $depth, "--{$index}-- **PASSED** VALIDATION" );
			}

		}
	}

	/**
	 * Possible TODO - Do we need to explicitly handle an array of an array
	 *
	 * ARRAY VALIDATION PARSER
	 */
	protected function processArray( $data, $depth, $index, $returnObject ) {
		$this->debug->logRecursion( $depth, "NODE --{$index}-- **TYPE** ARRAY VALIDATION " );

		$failedArrayResponseCollection = array();

		if ( is_array( $data->$index ) ) {
			/* CHECK IF SUPPLIED DATA IS AN ARRAY */

			$this->lastNode              = $this->cloneObject( $this->currentValidationNode );
			$this->currentValidationNode = $this->cloneObject(
				array_shift( $this->currentValidationNode->$index )
			);

			if ( isset( $this->currentValidationNode->$index ) && is_bool(
					( $this->currentValidationNode->$index )
				)
			) {
				/* TODO: REVIEW THIS */
				/* REMOVE KEYWORDS FROM DATA */

				unset( $this->currentValidationNode->$index );
			}

			foreach ( $data->$index as $key => $node ) {
				$this->debug->logRecursion( $depth, "NODE --{$index}--      +++ START ARRAY RECURSION" );
				$arrayRecursionResults = $this->Validate( $node, $depth );
				$this->debug->logRecursion( $depth, "NODE --{$index}--      --- END ARRAY RECURSION" );

				if ( $arrayRecursionResults->disposition !== Mediotype_Core_Model_Response::OK ) {
					$this->debug->logRecursion( $depth, "FAILURE IN CHILD NODE" );
					$failedArrayResponseCollection[ $key ] = $arrayRecursionResults;
				}
			}

			if ( count( $failedArrayResponseCollection ) > 0 ) {
				$returnObject->data[ $index ] = $failedArrayResponseCollection;
				$returnObject->disposition    = Mediotype_Core_Model_Response::FATAL;
			}

			$this->currentValidationNode = $this->cloneObject( $this->lastNode );

		} else {
			$this->debug->logRecursion( $depth, "NODE --{$index}-- **SHOULD BE** AN ARRAY IN SUPPLIED DATA" );

			$returnObject->disposition    = Mediotype_Core_Model_Response::FATAL;
			$returnObject->data[ $index ] = new Mediotype_Core_Model_Response(
				__METHOD__,
				$index,
				Mediotype_Core_Model_Response::FATAL,
				"NODE IN SUPPLIED DATA SHOULD BE AN ARRAY",
				Mediotype_Core_Helper_Mschema::EC_STRUCTURE_MALFORMED
			);
		}

	}

	/**
	 * Validate if the current json schema being processed was flagged 'required' somewhere
	 *
	 * @param $object
	 *
	 * @return bool
	 */
	protected function hasRequiredFields( $object ) {
		$requiredFieldsDetected = false;

		foreach ( $object as $index => $node ) {
			if ( is_object( $object->$index ) ) {
				/* IF KEY IS AN OBJECT THEN */
				if ( $this->isEndNode( $object->$index ) ) {
					/* If the key is a end node */
					if ( uc_words( $object->$index->required ) == true ) {
						$requiredFieldsDetected = true;
					}
				} else {
					/* IF NOT END NODE, RECURSE */
					$requiredFieldsDetected = $this->hasRequiredFields( $object->$index );
				}
			}
		}

		return $requiredFieldsDetected;
	}

	/**
	 * Lets the parser know if it has deeper nesting to process on the current json schema object
	 *
	 * @param $object
	 *
	 * @return bool
	 */
	protected function isEndNode( $object ) {
		foreach ( $object as $index => $node ) {
			if ( is_object( $node ) || is_array( $node ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * A lovely way to clone an Object
	 *
	 * @param object $obj
	 *
	 * @return mixed
	 */
	protected function cloneObject( $obj ) {
		return unserialize( serialize( $obj ) );
	}
}