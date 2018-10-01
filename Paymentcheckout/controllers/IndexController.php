<?php

require_once(Mage::getBaseDir('lib').'/CheckoutPHP/vendor/autoload.php');
use  com\checkout;
use com\checkout\ApiServices;
include_once('CustomerProcessing.php');
include_once('CardProcessing.php');
include_once('ChargeProcessing.php');

class Mofluid_Paymentcheckout_IndexController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		// Please keep the right secret Key ! ! IMPORTANT 
		$params = $this->getRequest()->getParams(); 
		
		$sk = 'sk_test_49edc396-cdf8-42b5-aae3-b4fee388b0ee';
		
		if(isset($params['cardinfo'])) {
			$card = array();
			$temp = base64_decode($params['cardinfo']);
			$temp = json_decode($temp, true);
			$card = new stdClass();
			$card["account_number"] = $temp['account_number'];
			$card["expiry_month"] = $temp['expiry_month'];
			$card["expiry_year"] = $temp['expiry_year'];
			$card["cvv"] = $temp['cvv'];
		}
	
		$card["account_number"] = '4242424242424242';
		$card["expiry_month"] = '12';
		$card["expiry_year"] = '2019';
		$card["cvv"] = '100';
	
		
		 $res = $this->createCust($sk, 'lakshya.kumar@ebizontek.com', 'Lakshya Kumar', $card);
		// $res = $this->getCust($sk, 'lakshya.kumar@ebizontek.com');
		 //$res = $this->getCustList($sk);
		$callback = $this->getRequest()->getParam('callback');
		$echo = $callback . json_encode($res);
		$this->getResponse()->setBody($echo);
	}
	
	public function createCust($sk, $email, $name, $cardinfo) {
		$CustomerServices = new CustomerServices();
		$res = $CustomerServices->createCustomer($sk, $email, $name, $cardinfo);
		return $res;
	}
	
	public function getCust($sk, $email) {
		$CustomerServices = new CustomerServices();
		$res = $CustomerServices->getCustomer($sk, $email);
		return $res;
	}
	
	public function getCustList($sk) {
		$CustomerServices = new CustomerServices();
		$res = $CustomerServices->getCustomerList($sk);
		return $res;
	}
	
}
