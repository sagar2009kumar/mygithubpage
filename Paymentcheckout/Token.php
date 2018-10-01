<?php

require_once('/var/www/html/CheckoutPHP/vendor/autoload.php');
use  com\checkout;
use com\checkout\ApiServices;


$cust_sarah = 'cust_A5071714-2F36-41B3-A753-9D76BA2730DA';
	$cust_ramanand = 'cust_4A04679E-0069-4711-B56E-ABC92B28E8A1';
	
	$sk = 'sk_test_9f4494a8-7969-4ee3-879f-fa9e6ed91db8';
	$pk = 'pk_test_05b87552-94f2-40d0-a93b-41444b72b759';
	//~ $ws_service = new CustomerServices();
	//~ $var = $ws_service->getCustomerList($sk); 
	//~ echo "<pre>"; print_r($var); die;
	// $tokenclass = new TokenApi();

$paytoken = 'pay_tok_64D8FD20-6D67-4D32-BA30-51597329B3E7';
	
	$sarah_email = 'sarah.mitchellramjaykar@checkout.com';
	$ramanand_email = 'demo@checkout.com';
	
	$sk = 'sk_test_9f4494a8-7969-4ee3-879f-fa9e6ed91db8'; // Sarah Mitchell
	$custid = 'cust_A5071714-2F36-41B3-A753-9D76BA2730DA'; // Ramanand Krishna
	
	$cardid1 = 'card_C8099342-1A17-4A59-8D83-81708B9919DE';
	$cardid2 = 'card_2F63DED5-25A3-4909-A443-5FDDEDCCA543';
	$cardid3 = 'card_23C2FF6C-18B0-4F8D-BB9A-CB1FC339C4B9';
	$cardid4 = 'card_63A14309-FCF4-41DB-BD6B-CA2302C836D1';

$apiClient = new checkout\ApiClient($sk);
//create an instance of a token service
$tokenService = $apiClient->tokenService();
//initializing the request models
$tokenPayload = new ApiServices\Tokens\RequestModels\PaymentTokenCreate();

$metaData = array('key'=>'value');
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
$tokenPayload->setEmail($sarah_email);

$tokenPayload->setMetadata($metaData);
$tokenPayload->setProducts($product);
$tokenPayload->setShippingDetails($shippingDetails);
 
try {
    /** @var ResponseModels\PaymentToken $paymentToken  **/
    $paymentToken = $tokenService->createPaymentToken($tokenPayload);
}catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
    echo 'Caught exception Message: ',  $e->getErrorMessage(), "\n";
    echo 'Caught exception Error Code: ',  $e->getErrorCode(), "\n";
    echo 'Caught exception Event id: ',  $e->getEventId(), "\n";
}


$apiClient = new checkout\ApiClient($sk);
// create a charge serive
$charge = $apiClient->chargeService();

try {
    /**  @var ResponseModels\Charge  $ChargeRespons **/
    $ChargeResponse = $charge->verifyCharge($paytoken);
echo "<pre>"; print_r($ChargeResponse); 
} catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
    echo 'Caught exception Message: ',  $e->getErrorMessage(), "\n";
    echo 'Caught exception Error Code: ',  $e->getErrorCode(), "\n";
    echo 'Caught exception Event id: ',  $e->getEventId(), "\n";
}
/*
 * curl -H "Authorization: sk_test_9f4494a8-7969-4ee3-879f-fa9e6ed91db8" -H "Content-Type: application/json;charset=UTF-8" -X POST -d '{
          "autoCapTime": "24",
          "autoCapture": "Y",
          "chargeMode": 1,
          "email": "sarah.mitchellramjaykar@checkout.com",
          "description": "charge description",
          "value": "4298",
          "currency": "USD",
          "trackId": "TRK12345",
          "transactionIndicator": "1",
          "customerIp":"96.125.185.51",
          "cardId": "card_63A14309-FCF4-41DB-BD6B-CA2302C836D1",
          "cvv": "100",
          "shippingDetails": {
            "addressLine1": "623 Slade Street",
            "addressLine2": "Flat 9",
            "postcode": "E149SR",
            "country": "UK",
            "city": "London",
            "state": "Greater London",
            "phone": {
                 "countryCode": "44",
                 "number": "0754617885"
             }
          },
          "products": [
            {
              "description": "Tablet 1 gold limited",
              "image": null,
              "name": "Tablet 1 gold limited",
              "price": 100.0,
              "quantity": 1,
              "shippingCost": 10.0,
              "sku": "1aab2aa",
              "trackingUrl": "https://www.tracker.com"
            },
            {
              "description": "Tablet 2 gold limited",
              "image": null,
              "name": "Tablet 2 gold limited",
              "price": 200.0,
              "quantity": 2,
              "shippingCost": 10.0,
              "sku": "1aab2aa",
              "trackingUrl": "https://www.tracker.com"
            }
          ],
          "metadata": {
            "key1": "value1"
          },
          "udf1": "udf 1 value",
          "udf2": "udf 2 value",
          "udf3": "udf 3 value",
          "udf4": "udf 4 value",
          "udf5": "udf 5 value"
        }' https://sandbox.checkout.com/api2/v2/charges/card*/
