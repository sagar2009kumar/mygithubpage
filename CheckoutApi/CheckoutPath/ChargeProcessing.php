<?php

$lib_path = Mage::getBaseDir('lib').'/checkout-php-library-master/vendor/autoload.php';
require_once($lib_path);
use  com\checkout;
use com\checkout\ApiServices;

class ChargeCredit {
	
	function createChargeWithCardId($sk, $cardId, $value, $currency, $custId) {
	
		$apiClient = new checkout\ApiClient($sk);
		$charge = $apiClient->chargeService();
		
		// create an instance of CardIdChargeCreate Model
		$cardChargeIdPayload = new ApiServices\Charges\RequestModels\CardIdChargeCreate();
		
		if(!isset($trackId)) $trackId = "Demo-track-id";
		
		//initializing model to generate payload
		$cardChargeIdPayload->setCustomerId($custId);
		$cardChargeIdPayload->setAutoCapture("Y");
		$cardChargeIdPayload->setAutoCaptime("2");
		$cardChargeIdPayload->setValue($value);
		$cardChargeIdPayload->setCurrency($currency);
		$cardChargeIdPayload->setTrackId($trackId);
		$cardChargeIdPayload->setCardId($cardId);

		try {
			/**  @var ResponseModels\Charge $ChargeResponse  **/
			$ChargeResponse = $charge->chargeWithCardId($cardChargeIdPayload);
			$res["status"] = (string)1;
			$res["chargeId"] = $ChargeResponse->getId();
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = $e->getErrorMessage();
			$res["errorCode"] = $e->getErrorCode();
		}
		return $res;
	}
	
	public function captureCharge($sk, $chargeId, $value) {
		
		$apiClient = new checkout\ApiClient($sk);
		$charge = $apiClient->chargeService();
		$chargeCapturePayload = new ApiServices\Charges\RequestModels\ChargeCapture();
		$chargeCapturePayload->setChargeId($chargeId);
		$chargeCapturePayload->setValue($value);
		
		try {
			$ChargeResponse = $charge->CaptureCardCharge($chargeCapturePayload);
			$res["status"] = (string)1;
			$responseCode = $ChargeResponse->getResponseCode();
			if($responseCode == 10000 ) {
				$res["status"] = (string)1;
				$res["id"] = $ChargeResponse->getId();
				$res["created"] = $ChargeResponse->getCreated();
				$res["value"] = (string)$ChargeResponse->getValue();
				$res["currency"] = $ChargeResponse->getCurrency();
				$res["statusCheckoutDotcom"] = $ChargeResponse->getStatus();
				$res["responseCode"] = $ChargeResponse->getResponseCode();
				$res["message"] = $ChargeResponse->getResponseMessage();
			}else {
				$res["status"] = (string)0;
				$res["message"] = $ChargeResponse->getResponseMessage();
			}
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = $e->getErrorMessage();
			$res["errorCode"] = $e->getErrorCode();
		}
		return $res;
	}
	
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
	
	
}
