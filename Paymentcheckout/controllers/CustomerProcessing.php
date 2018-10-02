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
			// preprocessing is remaining 
			$res["customer_list"] = $customerResponse; 
			$cnt = 0;
			$data = $customerResponse->getData();
			$temp = array();
			foreach($data as $customer) {
				$temp[$cnt]["id"] = $customer->getId();
				$temp[$cnt]["customerName"] = $customer->getName() ? $customer->getName() : "";
				$temp[$cnt]["email"] = $customer->getEmail();
				$tempCard = array();
				$cards = $customer->getCards();
				$cards = $cards->getData();
				$cnt1 = 0;
				foreach($cards as $card) {
					$tempCard[$cnt1]["id"] = $card->getId();
					$tempCard[$cnt1]["last4"] = $card->getLast4();
					$tempCard[$cnt1]["expiryMonth"] = $card->getExpiryMonth();
					$tempCard[$cnt1]["expiryYear"] = $card->getExpiryYear();
					$tempBillingAddress = $card->getBillingDetails();
					$dummyBillingAddress = array();
					$dummyBillingAddress["addressLine1"] = $tempBillingAddress->getAddressLine1() ? $tempBillingAddress->getAddressLine1() : "";
					$dummyBillingAddress["addressLine2"] = $tempBillingAddress->getAddressLine2() ? $tempBillingAddress->getAddressLine1() : "";
					$dummyBillingAddress["postCode"] = $tempBillingAddress->getPostcode() ? $tempBillingAddress->getPostcode() : "";
					$dummyBillingAddress["country"] = $tempBillingAddress->getCountry() ? $tempBillingAddress->getCountry() : "";
					$dummyBillingAddress["city"] = $tempBillingAddress->getCity() ? $tempBillingAddress->getCity() : "";
					$dummyBillingAddress["state"] = $tempBillingAddress->getState() ? $tempBillingAddress->getState() : "";
					$tempCard[$cnt1]["billingDetails"] = $dummyBillingAddress;
					$cnt1++;
				}
				$temp[$cnt]["customer_cards"] = $tempCard;
				$cnt++;
			}
			$res["status"] = (string)1;
			$res["customer_list"] = $temp;
		}catch (Exception $e) {
			$res["status"] = (string)0;
			$res["message"] = 'Caught exception: '.$e->getErrorMessage()."\n";
		}
		return $res;
	}
	
	public function getCustomer($sk, $cust_email) {
		
		if($sk && $cust_email) {
		$apiClient = new checkout\ApiClient($sk);
			$customerService = $apiClient->customerService();
			try {
				/**  @var \com\checkout\ResponseModels\Customer  $customerResponse **/
				$customerResponse = $customerService->getCustomer($cust_email);
				// Preprocessing is remaining
				$res["status"] = (string)1;
				$res["customer"] = $customerResponse;
			}catch (Exception $e) {
				$res["status"] = (string)0;
				$res["message"] = 'Caught exception: '.$e->getErrorMessage()."\n";
			}
		}else {
			$res["status"] = (string)0;
			$res["message"] = 'Security Key or email is not provided.';
		}
		return $res;
	}
	
	public function createCustomer($sk, $email, $name, $cardinfo) {
		
		if($sk && $email && $name) {
			$apiClient = new checkout\ApiClient($sk);

			$customerService = $apiClient->customerService();

			//initializing model to generate payload
			$baseCardCreateObject = new ApiServices\Cards\RequestModels\BaseCardCreate();
			$customerCreate = new ApiServices\Customers\RequestModels\CustomerCreate();

			$billingDetails = new ApiServices\SharedModels\Address();
			$phone = new ApiServices\SharedModels\Phone();

			$phone->setNumber("203 583 44 55");
			$phone->setCountryCode("44");

			$billingDetails->setAddressLine1('Dummy');
			$billingDetails->setPostcode('123');
			$billingDetails->setCountry('IN');
			$billingDetails->setCity('Delhi');
			$billingDetails->setPhone($phone);
			
			$baseCardCreateObject->setNumber($cardinfo['account_number']);
			$baseCardCreateObject->setExpiryMonth($cardinfo['expiry_month']);
			$baseCardCreateObject->setExpiryYear($cardinfo['expiry_year']);
			$baseCardCreateObject->setCvv($cardinfo['cvv']);
			
			$baseCardCreateObject->setBillingDetails($billingDetails);
			$customerCreate->setBaseCardCreate($baseCardCreateObject);
			$customerCreate->setEmail($email);
			$customerCreate->setName($name);

			try {
				/** @var \com\checkout\ApiServices\Customers\ResponseModels\Customer $customerResponse **/
				$customerResponse = $customerService->createCustomer($customerCreate);
				$res["status"] = (string)1;
				$res["message"] = $customerResponse;
			} catch (Exception $e) {
				$res["status"] = (string)0;
				$res["message"] = $e->getErrorMessage();
			}
		}else {
			$res["status"] = (string)0;
			$res["message"] = 'Security key or email or name is missing';
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
		} catch (Exception $e) {
			echo 'Caught exception: ', $e->getErrorMessage(), "\n";
		}
	}
	
	public function deleteCustomer($sk) {
		
		// provide the customer id not email
		$apiClient = new checkout\ApiClient($sk);
		$customerService = $apiClient->customerService();
		try {
			/**  @var \com\checkout\ApiServices\SharedModels\OkResponse  $customerResponse **/
			$customerResponse = $customerService->deleteCustomer('cust_B5843A79-9709-413E-8827-729174CF1A88');
		}catch (Exception $e) {
			echo 'Caught exception: ',  $e->getErrorMessage(), "\n";
		}
	}
}
