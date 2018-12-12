<?php

class Mofluid_Mynotes_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const CUSTOMER_VALIDATION_ERROR_MSG = array('status'=>0, 'errorMessage'=>'Invalid Token.');
	const DEFAULT_RESPONSE = array('status'=>0, 'errorMessage'=>'Nothing is returned from the api.');
	const HTTP_SUCCESS_CODE = 200;
	const HTTP_UNAUTHORISED_CODE = 401;
	const HTTP_SERVER_ERROR = 500;

	/**** Function to get the json post data ****/
	/**** return type is array ****/

	public function getJsonPostData() {

		/**** getting the address with the content-type application/json ****/
		$filePointer = fopen('php://input', 'r');
		$rawData = stream_get_contents($filePointer);
		return json_decode($rawData, true);
	}

	/**** Function to check the field if it is set or not ****/
	
	public function checkField($data = null) {
	
		if(is_null($data) || !isset($data) || $data =="") 
			return false;
		return true;
	}	
}
