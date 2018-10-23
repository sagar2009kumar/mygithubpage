<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Helper_Security extends Mage_Core_Helper_Abstract
{
    /**
     * Returns signed Checkout Form Fields as a string
     * @param array $params Checkout Form Fields
     * @param string $secretkey
     * @return string
     */
    public function sign ($params,$secretkey) {
		return $this->signData($this->buildDataToSign($params), $secretkey);
	}

    /**
     * Generates a keyed hash value using the HMAC method and returns the encoded string containing
     * the calculated message digest.
     * @param array $data
     * @param string $secretKey
     * @return string the encoded string containing the calculated message digest
     */
    public function signData($data, $secretKey) {
		return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
	}

    /**
     * Prepares the fields to be signed from Checkout Form Fields
     *
     * @param array $params Contains Checkout Form Fields
     * @return string
     */
    public function buildDataToSign($params) {
		$signedFieldNames = explode(",",$params["signed_field_names"]);
		foreach ($signedFieldNames as $field) {
			$dataToSign[] = $field . "=" . $params[$field];
		}
		return $this->commaSeparate($dataToSign);
	}

    /**
     * Converts an array into a comma separated string.
     * @param array $dataToSign The array of strings to implode
     * @return string
     */
    public function commaSeparate ($dataToSign) {
		return implode(",",$dataToSign);
	}

	/**
	 * Validate the response from Cybersource
	 *
	 * @param string $secretkey
	 * @param array $params
	 * @return boolean
	 */
	public function validateResponse($secretkey,$params)
	{

		if(empty($params['req_reference_number']))
		{
			return false;
		}

		if(empty($params['signature']))
		{
			return false;
		}

		if(strcmp($this->sign($params,$secretkey), $params['signature']) == 0)
		{
			return true;

		}else{

			return false;

		}

	}
}
