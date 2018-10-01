<?php

require_once(Mage::getBaseDir('lib').'/CheckoutPHP/vendor/autoload.php');
use  com\checkout;
use com\checkout\ApiServices;

class Card {
	
	function getCard($sk, $checkout_custid, $cardid) {
		
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();

		try {
			/**  @var ResponseModels\Card  $CardResponse **/
			$CardResponse = $cardService->getCard($checkout_custid, $cardid);
			$res["status"] = (string)1;
			$res["message"] = $CardResponse;
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = array('Caught exception Message: '.$e->getErrorMessage(), 'Caught exception Error Code: '.$e->getErrorCode(),'Caught exception Event id: '.$e->getEventId());
		}
		return $res;
	}

	
	function deleteCard($sk, $checkout_custid, $cardid) {
		
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();
		
		try {
			/**  @var \com\checkout\ApiServices\SharedModels\OkResponse  $CardResponse **/
			$CardResponse = $cardService->deleteCard($checkout_custid, $cardid);
			$res["status"] = (string)1;
			$res["message"] = $CardResponse;
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = array('Caught exception Message: '.$e->getErrorMessage(), 'Caught exception Error Code: '.$e->getErrorCode(),'Caught exception Event id: '.$e->getEventId());
		}
		return $res;
	}
	
	function getCustomerCardList($sk, $checkout_custid) {
		
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();
		
		try {
			/**  @var ResponseModels\Card  $CardResponse **/
			$CardResponse = $cardService->getCartList($checkout_custid);
			$res["status"] = (string)1;
			$res["message"] = $CardResponse;
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = array('Caught exception Message: '.$e->getErrorMessage(), 'Caught exception Error Code: '.$e->getErrorCode(),'Caught exception Event id: '.$e->getEventId());
		}
		return $res;
	}
	
	function createCard($sk, $checkout_custid) {
		
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();
		$cardsRequestModel = new ApiServices\Cards\RequestModels\CardCreate();
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
		$baseCardCreateObject->setExpiryYear('2019');
		$baseCardCreateObject->setCvv('100');
		$baseCardCreateObject->setBillingDetails($billingDetails);
		$cardsRequestModel->setBaseCardCreate($baseCardCreateObject);
		$cardsRequestModel->setCustomerId($checkout_custid);


		try {
			/** @var RequestModels\CardCreate $cardResponse **/
			$cardResponse = $cardService->createCard($cardsRequestModel);
			$res["status"] = (string)1;
			$res["card"] = $cardResponse;
		}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = array('Caught exception Message: '.$e->getErrorMessage(), 'Caught exception Error Code: '.$e->getErrorCode(),'Caught exception Event id: '.$e->getEventId());
		}
		return $res;
	}
	
	function updateCard($sk, $checkout_custid, $cardid) {
		
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();
		$cardsRequestModel = new ApiServices\Cards\RequestModels\CardUpdate();

		$baseCardCreateObject = new ApiServices\Cards\RequestModels\BaseCard();

		$billingDetails = new ApiServices\SharedModels\Address();
		$phone = new  ApiServices\SharedModels\Phone();

		$phone->setNumber("203 583 44 55");
		$phone->setCountryCode("44");

		$billingDetails->setAddressLine1('1 Glading Fields"');
		$billingDetails->setPostcode('N16 2BR');
		$billingDetails->setCountry('GB');
		$billingDetails->setCity('London');
		$billingDetails->setPhone($phone);

		$baseCardCreateObject->setName('Test Name');
		$baseCardCreateObject->setExpiryMonth('06');
		$baseCardCreateObject->setExpiryYear('2021');
		$baseCardCreateObject->setBillingDetails($billingDetails);
		$cardsRequestModel->setBaseCard($baseCardCreateObject);
		$cardsRequestModel->setCustomerId($checkout_custid);
		$cardsRequestModel->setCardId($cardid);


		try {
			/** @var SharedModels\OkResponse $cardResponse **/
			$cardResponse = $cardService->updateCard($cardsRequestModel);
			$res["status"] = (string)1;
			$res["card"] = $cardResponse;
		}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = array('Caught exception Message: '.$e->getErrorMessage(), 'Caught exception Error Code: '.$e->getErrorCode(),'Caught exception Event id: '.$e->getEventId());
		}
		return $res;
	}
}
