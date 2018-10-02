<?php

$lib_path = Mage::getBaseDir('lib').'/checkout-php-library-master/vendor/autoload.php';
require_once($lib_path);
use  com\checkout;
use com\checkout\ApiServices;


class CustomerServices {
	
	public function getCustomerList($sk) {
		
		$apiClient = new checkout\ApiClient($sk);
		$customerService = $apiClient->customerService();
		try {
			/**  @var \com\checkout\ApiServices\SharedModels\OkResponse  $customerResponse **/
			$customerResponse = $customerService->getCustomerList();
			$res["status"] = (string)1;
			$res["customerList"] = $customerResponse; 
			$cnt = 0;
			$data = $customerResponse->getData();
			$temp = array();
			foreach($data as $customer) {
				$temp[$cnt]["id"] = $customer->getId();
				$temp[$cnt]["customerName"] = $customer->getName() ? $customer->getName() : "";
				$temp[$cnt]["email"] = $customer->getEmail();
				$temp[$cnt]["defaultCard"] = $customer->getDefaultCard() ? $customer->getDefaultCard() : "";
				$tempCard = array();
				$cards = $customer->getCards();
				$cardData = $cards->getData(); 
				$cnt1 = 0;
				foreach((array)$cardData as $card) {
					$tempCard[$cnt1]["id"] = $card->getId();
					$tempCard[$cnt1]["last4"] = $card->getLast4();
					$tempCard[$cnt1]["expiryMonth"] = $card->getExpiryMonth();
					$tempCard[$cnt1]["expiryYear"] = $card->getExpiryYear();
					$cnt1++;
				}
				$temp[$cnt]["customerCards"] = $tempCard;
				$cnt++;
			}
			$res["status"] = (string)1;
			$res["customerList"] = $temp;
		}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = $e->getErrorMessage();
			$res["errorCode"] = $e->getErrorCode();
		}
		return $res;
	}
	
	public function getCustomer($sk, $custEmail) {
		
		if($sk && $custEmail) {
			$apiClient = new checkout\ApiClient($sk);
			$customerService = $apiClient->customerService();
			
			try {
				/**  @var \com\checkout\ResponseModels\Customer  $customerResponse **/
				$customerResponse = $customerService->getCustomer($custEmail);
				$custData = array();
				$custData["id"] = $customerResponse->getId();
				$custData["customerName"] = $customerResponse->getName() ? $customerResponse->getName() : "";
				$custData["createdAt"] = $customerResponse->getCreated();
				$custData["email"] = $customerResponse->getEmail();
				$custData["defaultCard"] = $customerResponse->getDefaultCard() ? $customer->getDefaultCard() : "";
				$cards = $customerResponse->getCards();
				$cardData = $cards->getData(); 
				$tempCard = array();
				$cnt1 = 0;
				foreach((array)$cardData as $card) {
					$tempCard[$cnt1]["id"] = $card->getId();
					$tempCard[$cnt1]["last4"] = $card->getLast4();
					$tempCard[$cnt1]["expiryMonth"] = $card->getExpiryMonth();
					$tempCard[$cnt1]["expiryYear"] = $card->getExpiryYear();
					$cnt1++;
				}
				$custData["customerCards"] = $tempCard;
				$res["status"] = (string)1;
				$res["customer"] = $custData;
			}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
				$res["status"] = (string)0;
				$res["message"] = $e->getErrorMessage();
				$res["errorCode"] = $e->getErrorCode();
			}
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Security Key or email is not provided.";
		}
		return $res;
	}
	
	public function createCustomer($sk, $email, $name) {
		
		if($sk && $email) {
			$apiClient = new checkout\ApiClient($sk);
			$customerService = $apiClient->customerService();
			
			//initializing model to generate payload
			$customerCreate = new ApiServices\Customers\RequestModels\CustomerCreate();
			$customerCreate->setEmail($email);
			$customerCreate->setName($name);

			try {
				/** @var \com\checkout\ApiServices\Customers\ResponseModels\Customer $customerResponse **/
				$customerResponse = $customerService->createCustomer($customerCreate);
				$custData = array();
				$custData["id"] = $customerResponse->getId();
				$custData["customerName"] = $customerResponse->getName() ? $customerResponse->getName() : "";
				$custData["createdAt"] = $customerResponse->getCreated();
				$custData["email"] = $customerResponse->getEmail();
				$custData["description"] = $customerResponse->getDescription() ? $customerResponse->getDescription() : "";
				$cards = $customerResponse->getCards();
				$cardData = $cards->getData(); 
				$tempCard = array();
				$cnt1 = 0;
				foreach((array)$cardData as $card) {
					$tempCard[$cnt1]["id"] = $card->getId();
					$tempCard[$cnt1]["last4"] = $card->getLast4();
					$tempCard[$cnt1]["expiryMonth"] = $card->getExpiryMonth();
					$tempCard[$cnt1]["expiryYear"] = $card->getExpiryYear();
					$cnt1++;
				}
				$custData["customerCards"] = $tempCard;
				$res["status"] = (string)1;
				$res["customer"] = $custData;
			} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
				$res["status"] = (string)0;
				$res["message"] = $e->getErrorMessage();
				$res["errorCode"] = $e->getErrorCode();
			}
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Security key or email or name is missing";
		}
		return $res;
	}
	
	public function deleteCustomer($sk, $custEmail) {
		
		$temp1 = $this->checkCustomerExist($sk, $custEmail);
		
		if($temp1["isExist"] == 1) {
			$apiClient = new checkout\ApiClient($sk);
			$customerService = $apiClient->customerService();
			try {
				/**  @var \com\checkout\ApiServices\SharedModels\OkResponse  $customerResponse **/
				$custId = $temp1["id"];
				$customerResponse = $customerService->deleteCustomer($custId);
				$res["status"] = (string)1;
				$res["message"] = "Customer Deleted";
			}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
				$res["status"] = (string)0;
				$res["message"] = $e->getErrorMessage();
				$res["errorCode"] = $e->getErrorCode();
			}
		}elseif($temp1["isExist"] == 0) {
			$res["status"] = (string)0;
			$res["message"] = "Checkout.com Customer does not exist";
		}else {
			$res = $temp1["message"];
		}
		return $res;
	}
	
	public function checkCustomerExist($sk, $custEmail) {
		
		$temp = $this->getCustomer($sk, $custEmail);
		$temp1 = array();
		if($temp["status"] == 1) {
			$temp1["isExist"] = (string)1;
			$temp1["id"] = $temp["customer"]["id"];
		}elseif($temp["status"] == "0" && $temp["errorCode"] == "84130") {
			$temp1["isExist"] = (string)0;
			$temp1["message"] = "Checkout.com Customer Does not exist";
		}else {
			$temp1["isExist"] = (string)-1;
			$temp1["message"] = $temp;
		}
		return $temp1;
	}
	
	
	function getCustomerCardServices($sk, $custEmail, $custName) {
		
		if($sk && $custEmail) {
			
			$temp = $this->getCustomer($sk, $custEmail);
			if($temp["status"] == 1) {
				$res = $temp;
			}elseif($temp["status"] == "0" && $temp["errorCode"] == "84130") {
				$res = $this->createCustomer($sk, $custEmail, $custName);
			}else {
				$res = $temp;
			}
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Field Missing";
		}
		return $res;
	}
	
	
	public function updateCustomer($sk) {

		// Customer Id is must.
		$apiClient = new checkout\ApiClient($sk);
		$customerService = $apiClient->customerService();
		//initializing model to generate payload
		$updateCustomer = new ApiServices\Customers\RequestModels\CustomerUpdate();
		$phone = new ApiServices\SharedModels\Phone();

		$phone->setNumber("203 583 66 55");
		$phone->setCountryCode("55");

		$updateCustomer->setDescription('This is a description');
		$updateCustomer->setName('Sarah Mitchell');
		//$updateCustomer->setEmail('demo@checkout.com');
		$updateCustomer->setPhoneNumber($phone);
		$updateCustomer->setCustomerId('cust_A5071714-2F36-41B3-A753-9D76BA2730DA');
		try {
			/** @var \com\checkout\ApiServices\Customers\ResponseModels\Customer $customerResponse **/
			$customerResponse = $customerService->updateCustomer($updateCustomer);
		} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			$res["status"] = (string)0;
			$res["message"] = $e->getErrorMessage();
			$res["errorCode"] = $e->getErrorCode();
		}
		return $res;
	}
}
