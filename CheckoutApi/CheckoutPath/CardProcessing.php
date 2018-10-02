<?php

$lib_path = Mage::getBaseDir('lib').'/checkout-php-library-master/vendor/autoload.php';
require_once($lib_path);
include_once('CustomerProcessing.php');
use  com\checkout;
use com\checkout\ApiServices;

class Card {
	
	function getCustomerCardList($sk, $custEmail) {
		
		/**** This method is good because ultimately we have to fetch the customer id ****/
		$CustomerServices = new CustomerServices();
		$temp = $CustomerServices->getCustomer($sk, $custEmail);
		return $temp;
	}
	
	function getCustomerCardList1($sk, $custEmail) {
		
		/**** This method is not so good because we have to do two api calls ****/
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();
		$CustomerServices = new CustomerServices();
		$tempres = $CustomerServices->checkCustomerExist($sk, $custEmail);
		
		if($tempres["isExist"] == 1) {
			try {
				/**  @var ResponseModels\Card  $CardResponse **/
				$CardResponse = $cardService->getCartList($tempres["id"]);
				$cardData = $CardResponse->getData(); 
				$tempCard = array();
				$cnt1 = 0;
				foreach((array)$cardData as $card) {
					$tempCard[$cnt1]["id"] = $card->getId();
					$tempCard[$cnt1]["last4"] = $card->getLast4();
					$tempCard[$cnt1]["expiryMonth"] = $card->getExpiryMonth();
					$tempCard[$cnt1]["expiryYear"] = $card->getExpiryYear();
					$cnt1++;
				}
				$res["status"] = (string)1;
				$res["id"] = $tempres["id"];
				$res["email"] = $custEmail;
				$res["customerCards"] = $tempCard;
			} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
				$res["status"] = (string)0;
				$res["message"] = $e->getErrorMessage();
				$res["errorCode"] = $e->getErrorCode();
			}
		}else {
			$res = $tempres;
		}
		return $res;
	}
	
	function getCard($sk, $custId, $cardId) {
		
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();

		try {
			/**  @var ResponseModels\Card  $CardResponse **/
			$CardResponse = $cardService->getCard($custId, $cardId);
			$res["status"] = (string)1;
			$res["id"] = $custId;
			$card = $CardResponse; 
			$cnt1 = 0;
			$tempCard[$cnt1]["id"] = $card->getId();
			$tempCard[$cnt1]["last4"] = $card->getLast4();
			$tempCard[$cnt1]["expiryMonth"] = $card->getExpiryMonth();
			$tempCard[$cnt1]["expiryYear"] = $card->getExpiryYear();
			$res["customerCards"] = $tempCard;
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = $e->getErrorMessage();
			$res["errorCode"] = $e->getErrorCode();
		}
		return $res;
	}
	
	function createCard($sk, $custId, $cardInfo) {
		
		if($sk && $custId && $cardInfo) {
			$apiClient = new checkout\ApiClient($sk);
			$cardService = $apiClient->cardService();
			
			/**** to be processed later ****/
			//~ $cardInfo = explode("@",$cardInfo);
			//~ $cardInfo = $cardInfo[1];
			//~ $cardInfo = base64_decode($cardInfo);
			//~ $cardInfo = json_decode($cardInfo, true);
			//~ $accountNumber = $cardInfo["accountNumber"];
			//~ $expiryMonth = $cardInfo["expiryMonth"];
			//~ $expiryYear = $cardInfo["expiryYear"];
			//~ $cvv = $cardInfo["cvv"];
			
			$accountNumber = "4242424242424242";
			$expiryMonth = "12";
			$expiryYear = "2029";
			$cvv = "100";
			$baseCardCreateObject = new ApiServices\Cards\RequestModels\BaseCardCreate();
			$cardsRequestModel = new ApiServices\Cards\RequestModels\CardCreate();
			
			$baseCardCreateObject->setNumber($accountNumber);
			$baseCardCreateObject->setExpiryMonth($expiryMonth);
			$baseCardCreateObject->setExpiryYear($expiryYear);
			$baseCardCreateObject->setCvv($cvv);
			$cardsRequestModel->setBaseCardCreate($baseCardCreateObject);
			$cardsRequestModel->setCustomerId($custId);
			
			try {
				/** @var RequestModels\CardCreate $cardResponse **/
				$cardResponse = $cardService->createCard($cardsRequestModel);
				$res["status"] = (string)1;
				$res["id"] = $custId;
				$card = $cardResponse; 
				$tempCard["id"] = $card->getId();
				$tempCard["last4"] = $card->getLast4();
				$tempCard["expiryMonth"] = $card->getExpiryMonth();
				$tempCard["expiryYear"] = $card->getExpiryYear();
				$res["customerCards"] = $tempCard;
			}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
				$res["status"] = (string)0;
				$res["message"] = $e->getErrorMessage();
				$res["errorCode"] = $e->getErrorCode();
			}
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Field Missing";
		}
		return $res;
	}
	
	function createCardPrevious($sk, $email, $cardInfo) {
		
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();
		$CustomerServices = new CustomerServices();
		$tempres = $CustomerServices->checkCustomerExist($sk, $email);
		
		if($tempres["isExist"] == 1) {
			
			/**** to be processed later ****/
			//~ $cardInfo = explode("@",$cardInfo);
			//~ $cardInfo = $cardInfo[1];
			//~ $cardInfo = base64_decode($cardInfo);
			//~ $cardInfo = json_decode($cardInfo, true);
			//~ $accountNumber = $cardInfo["accountNumber"];
			//~ $expiryMonth = $cardInfo["expiryMonth"];
			//~ $expiryYear = $cardInfo["expiryYear"];
			//~ $cvv = $cardInfo["cvv"];
			
			$accountNumber = "4242424242424242";
			$expiryMonth = "12";
			$expiryYear = "2019";
			$cvv = "100";
			$baseCardCreateObject = new ApiServices\Cards\RequestModels\BaseCardCreate();
			$cardsRequestModel = new ApiServices\Cards\RequestModels\CardCreate();
			
			$baseCardCreateObject->setNumber($accountNumber);
			$baseCardCreateObject->setExpiryMonth($expiryMonth);
			$baseCardCreateObject->setExpiryYear($expiryYear);
			$baseCardCreateObject->setCvv($cvv);
			$cardsRequestModel->setBaseCardCreate($baseCardCreateObject);
			$cardsRequestModel->setCustomerId($tempres["id"]);
			
			try {
				/** @var RequestModels\CardCreate $cardResponse **/
				$cardResponse = $cardService->createCard($cardsRequestModel);
				$res["status"] = (string)1;
				$res["id"] = $tempres["id"];
				$card = $cardResponse; 
				$cnt1 = 0;
				$tempCard[$cnt1]["id"] = $card->getId();
				$tempCard[$cnt1]["last4"] = $card->getLast4();
				$tempCard[$cnt1]["expiryMonth"] = $card->getExpiryMonth();
				$tempCard[$cnt1]["expiryYear"] = $card->getExpiryYear();
				$res["customerCards"] = $tempCard;
			}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
				$res["status"] = (string)0;
				$res["message"] = $e->getErrorMessage();
				$res["errorCode"] = $e->getErrorCode();
			}
		}else {
			$res = $tempres;
		}
		return $res;
	}
	
	function deleteCard($sk, $custId, $cardId) {
		
		$apiClient = new checkout\ApiClient($sk);
		$cardService = $apiClient->cardService();
		
		try {
			/**  @var \com\checkout\ApiServices\SharedModels\OkResponse  $CardResponse **/
			$CardResponse = $cardService->deleteCard($custId, $cardId);
			$res["status"] = (string)1;
			$res["message"] = "Card Deleted.";
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = $e->getErrorMessage();
			$res["errorCode"] = $e->getErrorCode();
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
			$res["message"] = $e->getErrorMessage();
			$res["errorCode"] = $e->getErrorCode();
		}
		return $res;
	}
}
