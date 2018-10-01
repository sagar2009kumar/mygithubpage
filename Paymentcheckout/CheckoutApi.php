<?php

require_once('/var/www/html/CheckoutPHP/vendor/autoload.php');
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
		}catch (Exception $e) {
			$res["status"] = (string)0;
			$res["message"] = 'Caught exception: '.$e->getErrorMessage()."\n";
		}
		return $res;
	}
	
	public function getCustomer($sk, $checkout_custid) {
		
		$apiClient = new checkout\ApiClient($sk);
		$customerService = $apiClient->customerService();
		try {
			/**  @var \com\checkout\ResponseModels\Customer  $customerResponse **/
			$customerResponse = $customerService->getCustomer($checkout_custid);
			// Preprocessing is remaining
			$res["status"] = (string)1;
			$res["customer"] = $customerResponse;
		}catch (Exception $e) {
			$res["status"] = (string)0;
			$res["message"] = 'Caught exception: '.$e->getErrorMessage()."\n";
		}
		return $res;
	}
	
	public function createCustomer($sk) {
		
		$apiClient = new checkout\ApiClient($sk);

		$customerService = $apiClient->customerService();

		//initializing model to generate payload
		$baseCardCreateObject = new ApiServices\Cards\RequestModels\BaseCardCreate();
		$customerCreate = new ApiServices\Customers\RequestModels\CustomerCreate();

		$billingDetails = new ApiServices\SharedModels\Address();
		$phone = new ApiServices\SharedModels\Phone();

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
		$customerCreate->setBaseCardCreate($baseCardCreateObject);
		$customerCreate->setEmail('demo@checkout.com');
		$customerCreate->setName('Ramanand Krishna');

		try {
			/** @var \com\checkout\ApiServices\Customers\ResponseModels\Customer $customerResponse **/
			$customerResponse = $customerService->createCustomer($customerCreate);
		} catch (Exception $e) {
			echo 'Caught exception: ', $e->getErrorMessage(), "\n";
		}
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


class TokenApi {

	public function createToken($sk) {
		
		$apiClient = new checkout\ApiClient($sk);
		//create an instance of a token service
		$tokenService = $apiClient->tokenService();
		//initializing the request models
		$tokenPayload = new ApiServices\Tokens\RequestModels\PaymentTokenCreate();

		$metaData = array('ram'=>'shyam');
		$product = new ApiServices\SharedModels\Product();
		//initializing models to generate payload
		$shippingDetails = new ApiServices\SharedModels\Address();
		$phone = new ApiServices\SharedModels\Phone();

		$product->setName('A4 office paper');
		$product->setDescription('a4 white copy paper');
		$product->setQuantity(1);
		$product->setShippingCost(10);
		$product->setSku('ABC123');
		$product->setTrackingUrl('http://www.tracker.com');

		$phone->setNumber("203 583 44 55");
		$phone->setCountryCode("44");

		$shippingDetails->setAddressLine1('1 Glading Fields"');
		$shippingDetails->setPostcode('N16 2BR');
		$shippingDetails->setCountry('GB');
		$shippingDetails->setCity('London');
		$shippingDetails->setPhone($phone);

		$tokenPayload->setCurrency("USD");
		$tokenPayload->setAutoCapture("Y");
		$tokenPayload->setValue("100");
		$tokenPayload->setCustomerIp("88.216.3.135");
		$tokenPayload->setDescription("test");
		$tokenPayload->setEmail('sarah.mitchellramjaykar@checkout.com');

		$tokenPayload->setMetadata($metaData);
		$tokenPayload->setProducts($product);
		$tokenPayload->setShippingDetails($shippingDetails);
		 
		try {
			/** @var ResponseModels\PaymentToken $paymentToken  **/
			$paymentToken = $tokenService->createPaymentToken($tokenPayload);
			return $paymentToken->getId();
		}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
			echo 'Caught exception Message: ',  $e->getErrorMessage(), "\n";
			echo 'Caught exception Error Code: ',  $e->getErrorCode(), "\n";
			echo 'Caught exception Event id: ',  $e->getEventId(), "\n";
		}
	}
}

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

class CardCharge {
	
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
	
}

class ChargeCredit {
	
	public function createCharge($sk, $chargeId) {
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



	$cust_sarah = 'cust_A5071714-2F36-41B3-A753-9D76BA2730DA';
	$cust_ramanand = 'cust_4A04679E-0069-4711-B56E-ABC92B28E8A1';
	
	$sk = 'sk_test_49edc396-cdf8-42b5-aae3-b4fee388b0ee';
	$pk = 'pk_test_13905ab9-721d-4b37-af55-6d4c23815c83';
	
	
	$charge_id  = 'charge_test_ADFB49FC151X7234BFE9';
	

	//~ $ws_service = new CustomerServices();
	//~ $var = $ws_service->getCustomerList($sk); 
	//~ echo "<pre>"; print_r($var); die;
	$tokenclass = new TokenApi();

$paytoken = 'pay_tok_64D8FD20-6D67-4D32-BA30-51597329B3E7';
	
	$sarah_email = 'sarah.mitchellramjaykar@checkout.com';
	$ramanand_email = 'demo@checkout.com';
	
	$sk = 'sk_test_49edc396-cdf8-42b5-aae3-b4fee388b0ee'; // Sarah Mitchell
	$custid = 'cust_A5071714-2F36-41B3-A753-9D76BA2730DA'; // Ramanand Krishna
	$cust_ramanand_new = 'cust_FD475E30-B4F6-495D-9B0E-EB001E55685C';
	$cardid1 = 'card_C8099342-1A17-4A59-8D83-81708B9919DE';
	$cardid2 = 'card_2F63DED5-25A3-4909-A443-5FDDEDCCA543';
	$cardid3 = 'card_23C2FF6C-18B0-4F8D-BB9A-CB1FC339C4B9';
	$cardid4 = 'card_63A14309-FCF4-41DB-BD6B-CA2302C836D1';
	
	$chargecredit = new ChargeCredit($sk, $charge_id);
	$var = $chargecredit->createCharge(	$sk, $charge_id);
	echo "<pre>"; print_r($var); die;
	// $ws_service->createCustomer($sk);
	// echo "<pre>"; print_r($ws_service->getCustomerList($sk));
	//~ $var = $ws_service->getCustomerList($sk);
	//~ echo "<pre>"; print_r($var); die;
	//~ $ws_card = new CardCharge();
	//~ $var = $ws_card->createChargeWithFullCardDetails($sk);
	//~ echo "<pre>"; print_r($var);
	
	//~ $card = new Card();
	
	//~ $var = $card->createCard($sk, $cust_sarah);
	//~ echo "<pre>"; print_r($var); 
	
	//~ $cardid4 = 'card_23BA689A-BC3E-4F6F-BACD-7BB9B0F6D57B';
	//~ $CustomerServices = new CustomerServices($sk);
	//~ $var = $CustomerServices->getCustomerList($sk);
	//~ echo "<pre>"; print_r($var); die;
	//~ $cardCharge = new CardCharge();
//~ $var = $cardCharge->createChargeWithCardId($sk, $cardid4, 100, 'USD', $cust_ramanand_new);
//~ echo "<pre>"; print_r($var);
	//~ die;
	$card = new Card();
	//~ $var1 = $card->deleteCard($sk, $cust_sarah, $cardid3);
	//~ echo "<pre>"; print_r($var1); 
	//~ $var = $card-> getCustomerCardList($sk, $cust_sarah);
	//~ echo "<pre>"; print_r($var);
	//~ $var = $card->updateCard($sk, $cust_sarah, $cardid4);
	//~ echo "<pre>"; print_r($var);
	//~ $var = $card-> getCard($sk, $cust_sarah, $cardid4);
	//~ echo "<pre>"; print_r($var);
	
	//~ $charge = new CardCharge();
	//~ $var = $charge->createChargeWithCardId($sk, $cardid4, 220, 'USD', $cust_sarah);
	//~ echo "<pre>"; print_r($var); 
	//~ $var = $charge->createChargeWithFullCardDetails($sk);
	//~ echo "<pre>"; print_r($var);
	
	//~ $createChargeUrl = 'https://sandbox.checkout.com/api2/v2/charges/card';
	
	//~ $config['postedParam'] = array (
            //~ 'trackId'           => 12345,
            //~ 'customerName'      => 'Sarah Mitchell',
            //~ 'email'             => 'sarah.mitchellramjaykar@checkout.com',
            //~ 'value'             => 100,
            //~ 'currency'          => 'USD',
			//~ 'cardId'			=> $cardid4
        //~ );
        
      //~ $data = '{
          //~ "autoCapTime": "24",
          //~ "autoCapture": "Y",
          //~ "email": "sarah.mitchellramjaykar@checkout.com",
          //~ "value": "5555",
          //~ "currency": "usd",
          //~ "trackId": "TRK12345",
          //~ "cardId": "card_63A14309-FCF4-41DB-BD6B-CA2302C836D1"
        //~ }';
	
	//~ $ch = curl_init();
	//~ curl_setopt($ch, CURLOPT_URL,$createChargeUrl);
	//~ curl_setopt($ch, CURLOPT_POST, 1);
	//~ curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		//~ 'Authorization: '.$sk,
		//~ 'Content-Type:application/json;charset=UTF-8'
		//~ ));
	//~ curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	//~ curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//~ curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	//~ $server_output = curl_exec($ch);
	//~ curl_close ($ch);

            //~ $response = json_decode($server_output);

	//~ echo "<pre>"; print_r($response);
	$postedParam['cardToken'] = $tokenclass->createToken($sk);echo $postedParam['cardToken'];
	$postedParam['customerId'] = $cust_sarah;
	 $createChargeUrl = "https://sandbox.checkout.com/api2/v2/charges/token";

             // curl to create apple pay charge at cko
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$createChargeUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: '.$sk,
                'Content-Type:application/json;charset=UTF-8'
                ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postedParam));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $server_output = curl_exec($ch);
            curl_close ($ch);
	echo "<pre>"; print_r($server_output); 
