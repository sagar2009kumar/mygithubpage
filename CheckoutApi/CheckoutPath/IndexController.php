<?php

$lib_path = Mage::getBaseDir('lib').'/checkout-php-library-master/vendor/autoload.php';
require_once($lib_path);

use com\checkout;
use com\checkout\ApiServices;
include_once('CustomerProcessing.php');
include_once('CardProcessing.php');
include_once('ChargeProcessing.php');

class Mofluid_Paymentpaypal_IndexController extends Mage_Core_Controller_Front_Action {
	
	private $_sk = "sk_test_49edc396-cdf8-42b5-aae3-b4fee388b0ee";
	
	public function setSecretKey($secretKey) {
		$this->_sk = $_sk;	
	}

	private function getSecretKey() {
		return $this->_sk;
	}
	
	public function printResult($res) {
		$callback = $this->getRequest()->getParam("callback");
		$echo = $callback . json_encode($res);
		$this->getResponse()->setBody($echo);
	}
	
	public function getCustData($customerId) {
		$customer = Mage::getModel("customer/customer")->load($customerId);
		if($customer->getId()) {
			$temp["id"] = $customer->getId();
			$temp["email"] = $customer->getEmail();
			$temp["firstName"] = $customer->getFirstname();
			$temp["lastName"] = $customer->getLastname();
			return $temp;
		}else {
			return null;
		}
	}
	
	public function getParameters() {
		$params = $this->getRequest()->getParams();
		return $params;
	}
	
	public function testAction() {
		echo "Working";
	}
	
	public function processTransactionAction() {
		$params = $this->getParameters();
		if(isset($params["newcard"])) {
			$cardInfo = 1;
			$tempres = $this->createCardCheckout($params["id"], $cardInfo);
			if($tempres["status"] == 1) {
				$res = $this->preProcessTransaction($params["value"], $params["currency"], $params["id"], $tempres["customerCards"]["id"]);
			}else {
				$res = $tempres;
			}
		}else {
			$res = $this->preProcessTransaction($params["value"], $params["currency"], $params["id"], $params["cardId"]);
		}
		$this->printResult($res);
	}
	
	public function preProcessTransaction($value, $currency, $custId, $cardId) {
		
		if($value && $currency && $custId && $cardId) {
			$tempres = $this->createCharge($value, $currency, $custId, $cardId);
			if($tempres["status"] == 1) {
				$res = $this->captureChargeCheckout($tempres["chargeId"], $value);
			}else {
				$res = $tempres;
			}
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Parameters Missing";
		}
		return $res;
	}
	
	public function createCharge($value, $currency, $custId, $cardId) {
		$sk = $this->getSecretKey();
		$card = new ChargeCredit(); 
		$res = $card->createChargeWithCardId($sk, $cardId, $value, $currency, $custId);
		return $res;
	}
	
	public function captureChargeCheckout($chargeId, $value) {
		$sk = $this->getSecretKey();
		$card = new ChargeCredit(); 
		$res = $card->captureCharge($sk, $chargeId, $value);
		return $res;
	}
	
	public function createCardCheckout($custId, $cardInfo) {
		$cardInfo = 1;
		$sk = $this->getSecretKey();
		$card = new Card();
		$res = $card->createCard($sk, $custId, $cardInfo);
		return $res;
	}
	
	public function getCustomerCardListAction() {
		$params = $this->getParameters();
		$sk = $this->getSecretKey();
		$temp = $this->getCustData($params["customerid"]); 
		$temp = 1;
		if(isset($temp)) {
			$custEmail = $temp["email"];
			$custName = $temp["firstName"];
			$CustomerServices = new CustomerServices();
			$res = $CustomerServices->getCustomerCardServices($sk, $custEmail, $custName);
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Magento Customer Does not exists";
		}
		$this->printResult($res);
	}
	
	
	public function getCustomerListAction() {
		$sk = $this->getSecretKey();
		$CustomerServices = new CustomerServices();
		$res = $CustomerServices->getCustomerList($sk);
		$this->printResult($res);
	}
	
	public function getCustomerAction() {
		$params = $this->getParameters();
		$sk = $this->getSecretKey();
		$CustomerServices = new CustomerServices();
		//~ $temp = $this->getCustData($params["customerid"]);
		$temp = 1;
		if(isset($temp)) {
			//~ $custEmail = $this->getCustEmail($temp["email"]);
			//~ $res = $CustomerServices->getCustomer($sk, $custEmail);
			$res = $CustomerServices->getCustomer($sk, "demo@checkout.com");
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Magento Customer Does not exists";
		}
		$this->printResult($res);
	}
	
	public function createCustomerAction() {
		$params = $this->getParameters();
		$sk = $this->getSecretKey();
		$CustomerServices = new CustomerServices();
		// $temp = $this->getCustData($params["customerid"]);
		$temp = 1;
		if(isset($temp)) {
			//~ $res = $CustomerServices->createCustomer($sk, $temp["email"], $temp["firstName"]);
			$res = $CustomerServices->createCustomer($sk, "ramnaam6@gmail.com", "Radha Rani");
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Magento Customer Does not exists";
		}
		$this->printResult($res);
	}
	
	public function deleteCustomerAction() {
		$params = $this->getParameters();
		$sk = $this->getSecretKey();
		$CustomerServices = new CustomerServices();
		// $temp = $this->getCustData($params["customerid"]);
		$temp = 1;
		if(isset($temp)) {
			//~ $res = $CustomerServices->deleteCustomer($sk, $temp["email"]);
			$res = $CustomerServices->deleteCustomer($sk, "ramnaam3@gmail.com");
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Magento Customer Does not exists";
		}
		$this->printResult($res);
	}
	
	public function getCustomerCardAction() {
		$params = $this->getParameters();
		$sk = $this->getSecretKey();
		$CustomerServices = new CustomerServices();
		$temp = 1;
		if(isset($temp)) {
			//~ $cardId = $params["cardId"];
			//~ $checkoutCustId = $temp["checkoutCustId"];
			$checkoutCustId = "cust_8E9AA7B9-3D58-498A-9C07-C3F1EB4EF6E9"; // 
			$cardId = "card_52DC8AD6-C3B6-4C76-BD21-A60FC467A136"; //
			$card = new Card();
			$res = $card->getCard($sk, $checkoutCustId, $cardId);
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Magento Customer Does not exists";
		}
		$this->printResult($res);
	}
	
	public function getCustomerCardListPreviousAction() {
		
		$params = $this->getParameters();
		$sk = $this->getSecretKey();
		//~ $temp = $this->getCustData($params["customerid"]);
		$temp = 1;
		if(isset($temp)) {
			//~ $custEmail = $this->getCustEmail($temp["email"]);
			//~ $res = $CustomerServices->getCustomer($sk, $custEmail);
			$custEmail = "demo@checkout.com";
			$card = new Card();
			$res = $card->getCustomerCardList($sk, $custEmail);
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Magento Customer Does not exists";
		}
		$this->printResult($res);
	}
	
	public function createCardAction() {
		
		$params = $this->getParameters();
		$sk = $this->getSecretKey(); 
		//~ $temp = $this->getCustData($params["customerid"]);
		$temp = 1; 
		if(isset($temp)) {
			//~ $custEmail = $this->getCustEmail($temp["email"]);
			//~ $res = $CustomerServices->getCustomer($sk, $custEmail);
			$cardInfo = 1;
			$custEmail = "demo@checkout.com";
			$card = new Card();
			$res = $card->createCard($sk, $custEmail, $cardInfo);
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Magento Customer Does not exists";
		}
		$this->printResult($res);
	}
	
	public function deleteCardAction() {
		
		$params = $this->getParameters();
		$sk = $this->getSecretKey();
		//~ $temp = $this->getCustData($params["customerid"]);
		$temp = 1; 
		if(isset($temp)) {
			//~ $custEmail = $this->getCustEmail($temp["email"]);
			//~ $res = $CustomerServices->getCustomer($sk, $custEmail);
			$cardId = "card_CCCD2B89-8884-4F93-90E2-B80FDD8079BA";
			$custId = "cust_FD475E30-B4F6-495D-9B0E-EB001E55685C";
			$card = new Card();
			$res = $card->deleteCard($sk, $custId, $cardId);
		}else {
			$res["status"] = (string)0;
			$res["message"] = "Magento Customer Does not exists";
		}
		$this->printResult($res);
	}
	
}

