<?php

require_once(Mage::getBaseDir('lib').'/CheckoutPHP/vendor/autoload.php');
use  com\checkout;
use com\checkout\ApiServices;

class ChargeCredit {
	
	function createChargeWithFullCardDetails($sk) {
		
		$apiClient = new checkout\ApiClient($sk);
		$charge = $apiClient->chargeService();

		//create an instance of a CardChargeCreate model
		$cardChargePayload = new ApiServices\Charges\RequestModels\CardChargeCreate();
		//initializing model to generate payload
		$baseCardCreateObject = new ApiServices\Cards\RequestModels\BaseCardCreate();

		$billingDetails = new ApiServices\SharedModels\Address();
		$phone = new  ApiServices\SharedModels\Phone();

		$phone->setNumber("203 583 44 55");
		$phone->setCountryCode("44");

		$billingDetails->setAddressLine1('1 Glading Fields"');
		$billingDetails->setPostcode('N16 2BR');
		$billingDetails->setCountry('GB');
		$billingDetails->setCity('London');
		$billingDetails->setPhone($phone);

		$baseCardCreateObject->setNumber('4242424242424242');
		$baseCardCreateObject->setName('Test Name');
		$baseCardCreateObject->setExpiryMonth('06');
		$baseCardCreateObject->setExpiryYear('2021');
		$baseCardCreateObject->setCvv('100');
		$baseCardCreateObject->setBillingDetails($billingDetails);

		$cardChargePayload->setEmail('sarah.mitchellramjaykar@checkout.com');
		$cardChargePayload->setAutoCapture('Y');
		$cardChargePayload->setAutoCaptime('0');
		$cardChargePayload->setValue('100');
		$cardChargePayload->setCurrency('USD');
		$cardChargePayload->setTrackId('Demo-0001');
		$cardChargePayload->setBaseCardCreate($baseCardCreateObject);

		try {
			/** @var ResponseModels\CardChargeCreate $ChargeResponse **/
			$ChargeResponse = $charge->chargeWithCard($cardChargePayload);
			$res["status"] = (string)1;
			$res["message"] = $ChargeResponse;
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = array('Caught exception Message: '.$e->getErrorMessage(), 'Caught exception Error Code: '.$e->getErrorCode(),'Caught exception Event id: '.$e->getEventId());
		}
		return $res;
	}
	
	function createChargeWithCardId($sk, $cardId, $value, $currency, $checkout_custid,  $trackId = null, $email = null) {
	
		$apiClient = new checkout\ApiClient($sk);
		$charge = $apiClient->chargeService();
		
		// create an instance of CardIdChargeCreate Model
		$cardChargeIdPayload = new ApiServices\Charges\RequestModels\CardIdChargeCreate();
		
		if(!isset($trackId)) $trackId = "Demo-track-id";
		if(!isset($email)) $email = 'demo@ebizon.com';
		
		//initializing model to generate payload
		$cardChargeIdPayload->setCustomerId($checkout_custid);
		//$cardChargeIdPayload->setEmail('sarah.mitchellramjaykar@checkout.com');
		$cardChargeIdPayload->setAutoCapture('Y');
		$cardChargeIdPayload->setAutoCaptime('2');
		$cardChargeIdPayload->setValue($value);
		$cardChargeIdPayload->setCurrency($currency);
		$cardChargeIdPayload->setTrackId($trackId);
		$cardChargeIdPayload->setCardId($cardId);

		try {
			/**  @var ResponseModels\Charge $ChargeResponse  **/
			$ChargeResponse = $charge->chargeWithCardId($cardChargeIdPayload);
			$res["status"] = (string)1;
			$res["message"] = $ChargeResponse;
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = array('Caught exception Message: '.$e->getErrorMessage(), 'Caught exception Error Code: '.$e->getErrorCode(),'Caught exception Event id: '.$e->getEventId());
		}
		return $res;
	}
	
	public function captureCharge($sk, $chargeId) {
		$apiClient = new checkout\ApiClient($sk);
		
		$charge = $apiClient->chargeService();
		$chargeCapturePayload = new ApiServices\Charges\RequestModels\ChargeCapture();
		$chargeCapturePayload->setChargeId($chargeId);
		$chargeCapturePayload->setValue('100');
		
		try {
			$ChargeResponse = $charge->CaptureCardCharge($chargeCapturePayload);
			$res["status"] = (string)1;
			$res["message"] = $ChargeResponse;
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = array('Caught exception Message: '.$e->getErrorMessage(), 'Caught exception Error Code: '.$e->getErrorCode(),'Caught exception Event id: '.$e->getEventId());
		}
		return $res;
	}
	
	
}
