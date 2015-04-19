<?php

/**
 * Forces Value Assignment Even If A Differing Value Is Present
 */
class Mediotype_Core_Helper_Mschema_Validator_Forcevalue extends Mediotype_Core_Helper_Mschema_Validator_Abstract {

	/**
	 * Accepts Options Colon Delimited
	 *  :notempty - leaves empty value empty
	 *
	 * @param $validationObject
	 * @param $data
	 *
	 * @return Mediotype_Core_Model_Response
	 */
	public function Validate( $validationObject, &$data ) {

		$response = new Mediotype_Core_Model_Response( __METHOD__, null, Mediotype_Core_Model_Response::OK );
		if ( $this->CanRead( $validationObject ) ) {

			$assignment = $validationObject->{$this->getKeyword()};

			/*
			 * Check for config string after force value for
			 * ":notempty"
			 */
			if ( strpos( $assignment, ":" ) ) {
				$additionalConfig = explode( ":", $assignment );
				$assignment       = $additionalConfig[0];
				if ( $additionalConfig[1] == "notempy" ) {
					if ( is_null( $data ) || trim( $data ) == "" ) {
						$data = $assignment;
					} else {
						$response->disposition = Mediotype_Core_Model_Response::OK;
						$response->description = "Didn't re-assign present value, filled in empties :notempty";
						return $response;
					}

				}
			} else {
				$data = $assignment;
			}

			if ( ! $data = $assignment ) {
				$response->disposition = Mediotype_Core_Model_Response::FATAL;
				$response->description = "Failed to force value for variable to '{$validationObject->{$this->getKeyword()}}'";
			}

		}
		$response->data = $data;

		return $response;
	}

	/**
	 * @return string
	 */
	public function getKeyword() {
		return "force";
	}

}