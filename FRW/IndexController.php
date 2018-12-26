<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mofluid
 * @package     Mofluid_Mofluidapi119
 * @copyright   Copyright (c) 2016 Ebizon Net Info (http://www.ebizontek.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Adminhtml base helper
 *
 * @category   Mofluid
 * @package    Mofluid API
 * @author     Kulbhushan <kulbhushan@ebiziontek.com>
 */
include_once('Service1.php');

class Mofluid_Mofluidapi119_IndexController extends Mage_Core_Controller_Front_Action {


	  public function ws_validateAuthenticate(){
		$connection_read     = Mage::getSingleton('core/resource')->getConnection('core_read');
		$requestHeaders = getallheaders();
		
		$authappid = $requestHeaders['authappid'];
		$token = $requestHeaders['token'];
		$secretkey = $requestHeaders['secretkey'];
		
		if(empty($authappid) || $authappid == null)
			return false;
		if(empty($token) || $token == null)
			return false;
		if(empty($secretkey) || $secretkey == null)
			return false;
		
		$mofluid_authentication = $connection_read->select()
			->from(Mage::getSingleton('core/resource')->getTableName('mofluid_mofluidapi119/authentication'), array('*'))
			->where('appid = ?', $authappid)
			->where('token = ?', $token)
			->where('secretkey = ?', $secretkey);
			$mofluid_pay_data = $connection_read->fetchAll($mofluid_authentication);
			if(count($mofluid_pay_data) > 0){
				return true;
			}else{
				return false;
			}
			return false;
	}
  /**
   * 
   */
  public function indexAction() {
    try {

		//$service = $_GET["service"];
		$service = $this->getRequest()->getParam('service');
	  // generate Token and secret key
	  if($service == 'gettoken'){
		        $data=array();
				$data['appid']="123";				
				$data['token']="123";				
				$data['secretkey']="123";
         //$data = ['appid' => "123", 'token' => "123", 'secretkey' =>"123"];
                                $echo= json_encode($data);
                                $this->getResponse()->setBody($echo);
                                return;
			$mofluidAuthResponse = array();
			//$authappid = $_GET["authappid"];
			$authappid =$this->getRequest()->getParam('authappid');
			if(empty($authappid) || $authappid == null){
				$echo= json_encode(array("Invalid App id"));
				$this->getResponse()->setBody($echo);
				return;
			}
			
			$connection_read     = Mage::getSingleton('core/resource')->getConnection('core_read');
			$connection_write     = Mage::getSingleton('core/resource')->getConnection('core_write');
			$mofluid_authentication_selectresource = $connection_read->select()
			->from(Mage::getSingleton('core/resource')->getTableName('mofluid_mofluidapi119/authentication'), array('*'))
			->where('appid = ?', $authappid);
			$mofluid_pay_data = $connection_read->fetchAll($mofluid_authentication_selectresource);
			
			if(count($mofluid_pay_data) > 0){
				$data=array();
				$data['appid']=$mofluid_pay_data[0]['appid'];				
				$data['token']=$mofluid_pay_data[0]['token'];				
				$data['secretkey']=$mofluid_pay_data[0]['secretkey'];							
				//$data = ['appid' => $mofluid_pay_data[0]['appid'], 'token' => $mofluid_pay_data[0]['token'], 'secretkey' => $mofluid_pay_data[0]['secretkey']];
				$echo= json_encode($data);
				$this->getResponse()->setBody($echo);
				return;
			}else{
				$token = openssl_random_pseudo_bytes(16);
				$token = bin2hex($token);
				$secretKey = md5(uniqid($authappid, TRUE));
				$table        = Mage::getSingleton('core/resource')->getTableName('mofluid_mofluidapi119/authentication');
				$query        = "INSERT INTO {$table} (`appid`,`token`,`secretkey`) VALUES ( '$authappid', '$token', '$secretKey');";
				//~ var_dump($query);die;
				$connection_write->query($query);
				$data=array();
				$data['appid']=$authappid;
				$data['token']=$token;
				$data['secretkey']=$secretKey;
				//$data = ['appid' => $authappid, 'token' => $token, 'secretkey' => $secretKey];
				$echo= json_encode($data);
				$this->getResponse()->setBody($echo);
				return;
			}
		}
		
/*	  if(!$this->ws_validateAuthenticate()){
			$echo= json_encode(array('unauthorized'));
			return;
		}*/
		// generate Token and secret key end
      //$store = $_GET["store"];
      $store = $this->getRequest()->getParam('store');
      if ($store == null || $store == '') {
        $store = 1;
      }
      $storeVar = $this->getRequest()->getParam('store_id');
      $storeId = isset($storeVar) ? $storeVar : $store;
      
      $ratingStar = $this->getRequest()->getParam('rating_star');
	$ratingDescription = $this->getRequest()->getParam('rating_desc');
      //$pageId = $_GET["pageId"];
      $pageId = $this->getRequest()->getParam('pageId');
      
      //$filterdataencode=$_GET["filterdata"];
      $filterdataencode=$this->getRequest()->getParam('filterdata');
      $filterData = $this->getRequest()->getParam('filter_data', array());
      $filterdata=base64_decode($filterdataencode);
	  //$categoryid = $_GET["categoryid"];
	  $categoryid = $this->getRequest()->getParam('category_id');
	  $categoryId = $categoryid;
         //$firstname1 = $_GET["firstname"];
         $firstname1 = $this->getRequest()->getParam('firstname');
      $firstname=base64_decode($firstname1);
      $firstName = $this->getRequest()->getParam('firstname');
            $lastName = $this->getRequest()->getParam('lastname');
      //$lastname1 = $_GET["lastname"];
      $lastname1 = $this->getRequest()->getParam('lastname');
      $lastname=base64_decode($lastname1);
      //$email = $_GET["email"];
      $email = $this->getRequest()->getParam('email');
      //$password = $_GET["password"];
      $password = $this->getRequest()->getParam('password');
      //$oldpassword = $_GET["oldpassword"];
      $oldpassword = $this->getRequest()->getParam('oldpassword');
      //$newpassword = $_GET["newpassword"];
      $newpassword = $this->getRequest()->getParam('newpassword');
      //$productid = $_GET["productid"];
      $productid = $this->getRequest()->getParam('productid');
      $productId = $this->getRequest()->getParam('product_id');
      $productQty = $this->getRequest()->getParam('product_quantity');
      //$custid = $_GET["customerid"];
      $custid = $this->getRequest()->getParam('customerid');
      //$billAdd = $_GET["billaddress"];
      $billAdd = $this->getRequest()->getParam('billaddress');
      //$shippAdd = $_GET["shippaddress"];
      $shippAdd = $this->getRequest()->getParam('shippaddress');
      //$pmethod = $_GET["paymentmethod"];
      $pmethod =$this->getRequest()->getParam('paymentmethod');
      //$smethod = $_GET["shipmethod"];
      $smethod = $this->getRequest()->getParam('shipmethod');
      //$transid = $_GET["transactionid"];
      $transid = $this->getRequest()->getParam('transactionid');
      //$product = $_GET["product"];
      $product = $this->getRequest()->getParam('product');
     // $shippCharge = $_GET["shippcharge"];
      $shippCharge = $this->getRequest()->getParam('shippcharge');
      //$search_data = $_GET["search_data"];
      //$searchdata = $_GET["search_data"];
      $searchdata = $this->getRequest()->getParam('search_data');
      $search_data = base64_decode($searchdata);
      $searchData = $this->getRequest()->getParam('search_data');
      //$username = $_GET["username"];
      $username = $this->getRequest()->getParam('username');
      // Get Requested Data for Push Notification Request
      //$deviceid = $_GET["deviceid"];
      $deviceid = $this->getRequest()->getParam('deviceid');
      //$pushtoken = $_GET["pushtoken"];
      $pushtoken = $this->getRequest()->getParam('pushtoken');
      //$platform = $_GET["platform"];
      $platform = $this->getRequest()->getParam('platform');
     // $appname = $_GET["appname"];
      $appname = $this->getRequest()->getParam('appname');
     // $description = $_GET["description"];
      $description = $this->getRequest()->getParam('description');
      //$profile = $_GET["profile"];
      $profile = $this->getRequest()->getParam('profile');
      //$paymentgateway = $_GET["paymentgateway"];
      $paymentgateway = $this->getRequest()->getParam('paymentgateway');
      //$couponCode = $_GET["couponCode"];
      $couponCode = $this->getRequest()->getParam('couponCode');
      //$orderid = $_GET["orderid"];
      $orderid = $this->getRequest()->getParam('orderid');
      //$pid = $_GET["pid"];
      $pid = $this->getRequest()->getParam('pid');
      //$products = $_GET["products"];
      $products = $this->getRequest()->getParam('products');
      //$address = $_GET["address"];
      $address = $this->getRequest()->getParam('address');
      //$country = $_GET["country"];
      $country = $this->getRequest()->getParam('country');
      //$grand_amount = $_GET["grandamount"];
      $grand_amount = $this->getRequest()->getParam('grandamount');
      //$order_sub_amount = $_GET["subtotal_amount"];
      $order_sub_amount = $this->getRequest()->getParam('subtotal_amount');
      //$discount_amount = $_GET["discountamount"];
      $discount_amount = $this->getRequest()->getParam('discountamount');
      //$mofluidpayaction = $_GET["mofluidpayaction"];
      $mofluidpayaction = $this->getRequest()->getParam('mofluidpayaction');
     // $postdata = $_POST;
      // $postdata = $this->getRequest()->getParam();
      //$mofluid_payment_mode = $_GET["mofluid_payment_mode"];
      $mofluid_payment_mode = $this->getRequest()->getParam('mofluid_payment_mode');
      //$product_id = $_GET["product_id"];
      $product_id = $this->getRequest()->getParam('product_id');
      //$gift_message = $_GET["message"];
      $gift_message =$this->getRequest()->getParam('message');

      //$count=$_GET["item_count"];
      $count=$this->getRequest()->getParam('item_count');
      $itemId = $this->getRequest()->getParam('item_id');
      $orderIncrementId = $this->getRequest()->getParam('order_increment_id');
      
      //$mofluid_paymentdata = $_GET["mofluid_paymentdata"];
      $mofluid_paymentdata = $this->getRequest()->getParam('mofluid_paymentdata');
		/**** cart info for the cart to be updated ****/
		$jsonCartInfo = $this->getRequest()->getParam('cart_info');
      //$mofluid_ebs_pgdata = $_GET["DR"];
      $mofluid_ebs_pgdata = $this->getRequest()->getParam('DR');
      //$curr_page = $_GET["currentpage"];
      $curr_page = $this->getRequest()->getParam('currentpage');
      $currentPage = $this->getRequest()->getParam('current_page', 1);
      $couponCodeRemoveFlag = $this->getRequest()->getParam('coupon_code_remove_flag');
      $couponCode = $this->getRequest()->getParam('coupon_code');
      //$blogId = $_GET["blog_id"];
      $blogId = $this->getRequest()->getParam('blog_id');
      $pageSize = $this->getRequest()->getParam('page_size', 9);
      // $manufacturerId = $_GET["manufacturer_id"];
      $manufacturerId = $this->getRequest()->getParam('manufacturer_id');
      //$page_size = $_GET["pagesize"];
      $page_size = $this->getRequest()->getParam('pagesize');
      //$sortType = $_GET["sorttype"];
      $sortType = $this->getRequest()->getParam('sorttype','');
      $sortType = $this->getRequest()->getParam('sort_type','relevance');
      //$sortOrder = $_GET["sortorder"];
      $sortOrder = $this->getRequest()->getParam('sortorder','');
      $sortOrder = $this->getRequest()->getParam('sort_order','asc');
      //$saveaction = $_GET["saveaction"];
      $saveaction = $this->getRequest()->getParam('saveaction');
      //$mofluid_orderid_unsecure = $_GET["mofluid_order_id"];
      $mofluid_orderid_unsecure = $this->getRequest()->getParam('mofluid_order_id');
      $productInfo = $this->getRequest()->getParam('product_info');
      //$currency = $_GET["currency"];
      $currency = $this->getRequest()->getParam('currency');
      //$price = $_GET["price"];
      $price = $this->getRequest()->getParam('price');
      //$from = $_GET["from"];
      $from = $this->getRequest()->getParam('from');
      //$to = $_GET["to"];
      $to = $this->getRequest()->getParam('to');
     // $is_create_quote = $_GET["is_create_quote"];
      $is_create_quote = $this->getRequest()->getParam('is_create_quote');
      //$find_shipping = $_GET["find_shipping"];
      $find_shipping = $this->getRequest()->getParam('find_shipping');
      //$messages = $_GET["messages"];
      $messages = $this->getRequest()->getParam('messages');
      //$theme = $_GET["theme"];
      $theme = $this->getRequest()->getParam('theme');
      //$timeslot = $_GET["timeslot"];
      $timeslot = $this->getRequest()->getParam('timeslot');
      //$billshipflag = $_GET["shipbillchoice"];
      $billshipflag = $this->getRequest()->getParam('shipbillchoice');
      //$type = $_GET["type"];
      $type = $this->getRequest()->getParam('type');
      //$legacy_id = $_GET["id"];
      $legacy_id = $this->getRequest()->getParam('id');
      //$img_width = $_GET["width"];
      $img_width = $this->getRequest()->getParam('width');
      //$img_height = $_GET["height"];
      $img_height =$this->getRequest()->getParam('height');
      //$get_all = $_GET["get_all"];
      $get_all = $this->getRequest()->getParam('get_all');
      //$qty = $_GET["qty"];
      $qty = $this->getRequest()->getParam('qty');
    //$customer_id = $_GET["customerid"];
    $customer_id = $this->getRequest()->getParam('customerid');
	//~ $customerId = $this->getRequest()->getParam('customer_id');
	$quoteId = $this->getRequest()->getParam('quote_id'); 
      //$apiKey = $_GET["apiKey"];
      $apiKey = $this->getRequest()->getParam('apiKey');
      //$token_id = $_GET["token_id"];
      $token_id = $this->getRequest()->getParam('token_id');
      //$card_id = $_GET["card_id"];
      $card_id =$this->getRequest()->getParam('card_id');
	//$name1 = $_GET['name'];
	$name1 = $this->getRequest()->getParam('name');
	 $name=base64_decode($name1);
       //$mofluid_Custid = $_GET["mofluid_Custid"];
       $mofluid_Custid = $this->getRequest()->getParam('mofluid_Custid');
	
      //$discription = $_GET["discription"];
      $discription = $this->getRequest()->getParam('discription');
      //$address_id=$_GET["addressid"];
      $address_id=$this->getRequest()->getParam('addressid');
     // $address_data=$_GET["address_data"];
      $address_data=$this->getRequest()->getParam('address_data');
     // $order_status=$_GET["orderStatus"]; 
      $order_status=$this->getRequest()->getParam('orderStatus');
      $deal_id = $this->getRequest()->getParam("deal_id"); 
     $address_data=base64_decode($address_data);
      $ws_service = new Service();
      $callback=$this->getRequest()->getParam('callback');
      if ($store == -1 || $store == null || $store == "") {
        $this->ws_store404($store);
      } else {
        if ($service == "sidecategory") {
          $res = $ws_service->ws_sidecategory($store, $service);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "category") {
          $res = $ws_service->ws_category($store, $service);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
        /*  Start Get Category id*/
        elseif ($service == "getorderid") { 
          $res = $ws_service->ws_getorderid($store, $service);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }  
        /* End */
          /* Start get category filter*/
         elseif ($service == "getcategoryfilter") { 
          $res = $ws_service->ws_getcategoryfilter($store,$categoryid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        }  
          /*end get category filter*/
         
        elseif ($service == "subcategory") {
          $res = $ws_service->ws_subcategory($store, $service, $categoryid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "products") {
          $res = $ws_service->ws_products($store, $service, $categoryid, $currentPage, $pageSize, $sortType, $sortOrder, $currency, $price);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
        /** Related products **/
         elseif ($service == "related_products") {
          $res = $ws_service->ws_getRelatedProducts($product_id, $currency, $service, $store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
        /** End Related products **/
        // custom service start
         elseif ($service == "filter") {
		//$test=json_encode("store=".$store."service=".$service."categoryid=".$categoryid."curr_page=".$curr_page."page_size=".$page_size
		//."sortType".$sortType."sortOrder=".$sortOrder."currency=".$currency."filterdata=".$filterdata);
		//die($test);
          //$res = $ws_service->ws_layeredFilter($store, $service, 
         // $categoryid, $curr_page, $page_size, $sortType, $sortOrder, 
          //$currency,$filterdata);
         $res=$ws_service->ws_getFilteredProducts($store, 
              $categoryid, $currentPage, $pageSize, $sortType, $sortOrder, 
          $currency,$filterdata);
          $echo= $callback.json_encode($res);
          $this->getResponse()->setBody($echo);
        } 
        // custom service end
        else if ($service == "filtersearch") {
          $res = $ws_service->ws_searchFilter($store,$search_data);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
        else if ($service == "validate_currency") {
          $res = $ws_service->ws_validatecurrency($store, $service, $currency, $paymentgateway);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "createuser") {
          $res = $ws_service->createUser($store, $service, $firstName, $lastName, $email, $password);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        } elseif ($service == "productdetail") {
          $res = $ws_service->ws_productdetail($store, $service, $productid, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "productdetailimage") {
          $res = $ws_service->ws_productdetailImage($store, $service, $productid, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "productdetaildescription") {
          $res = $ws_service->ws_productdetailDescription($store, $service, $productid, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "productinfo") {
          try {
            $res = $ws_service->ws_productinfo($store, $service, $productid, $currency);
            $echo= $callback . json_encode($res);
            $this->getResponse()->setBody($echo);
          } catch (Exception $ex) {
            $echo= 'Error' . $ex->getMessage();
            $this->getResponse()->setBody($echo);
          }
        } elseif ($service == "currency") {
          $res = $ws_service->ws_currency($store, $service);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "createorder") {
          $res = $ws_service->ws_createorder($store, $service, $custid, $pmethod, $smethod, $transid, $product, $shippCharge, $couponCode, $grand_amount, $order_sub_amount, $discount_amount, $gift_message);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "setaddress") {
          $res = $ws_service->ws_setaddress($store, $service, $custid, $address, $email, $saveaction);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "storedetails") {
          $res = $ws_service->ws_storedetails($store, $service, $theme, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        } elseif ($service == "checkout") {
          $res = $ws_service->ws_checkout($store, $service, $theme, $currency);
          $echo= $callback. json_encode($res);
          $this->getResponse()->setBody($echo);
        } 
        //~ elseif ($service == "search") {
          //~ $res = $ws_service->ws_search($store, $service, $search_data, $currentPage, $pageSize, $sortType, $sortOrder, $currency,$filterdata);
          //~ $echo= $callback . json_encode($res);
          //~ $this->getResponse()->setBody($echo);
        //~ } 
        elseif ($service == "searchfilter") {
          $res = $ws_service->ws_searchFilter($store,$search_data);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getcustomer") {
          $res = $ws_service->ws_getCustomerId($store, $service, $email);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "verifylogin") {
          $res = $ws_service->verifyLogin($store, $service, $username, $password);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        } elseif ($service == "orderinfo") {
          $res = $ws_service->orderInfo($cust_id, $orderid, $store, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "orderupdate") {
          $res = $ws_service->updateOrderStatus($cust_id, $orderid, $store, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "myorders") {
          $res = $ws_service->ws_myOrder($custid, $currentPage, $pageSize, $store, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "myprofile") {
          $res = $ws_service->ws_myProfile($custid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "changeprofilepassfilterdataword") {
          $res = $ws_service->ws_changeProfilePassword($custid, $username, $oldpassword, $newpassword, $store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "productsearchhelp") {
          $res = $ws_service->ws_productSearchHelp($store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "register_push") {
          $res = $ws_service->mofluid_register_push($store, $deviceid, $pushtoken, $platform, $appname, $description);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "setprofile") {
          $res = $ws_service->ws_setprofile($store, $service, $custid, $billAdd, $shippAdd, $profile);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "forgotpassword") { 
          $res = $ws_service->forgotPassword($email);
          $echo= $callback . json_encode($res);
       	  $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        } elseif ($service == "termCondition") {
          $res = $ws_service->ws_termCondition($store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "productQuantity") {
          $res = $ws_service->ws_productQuantity($product);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "countryList") {
          $res = $ws_service->ws_countryList($store, $paymentgateway, $pmethod);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "listShipping") {
          $res = $ws_service->ws_listShipping();
          $echo= $callback . json_encode($res);
           $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        } elseif ($service == "shipmyidenabled") {
          $res = $ws_service->ws_shipmyidenabled();
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "preparequote") {
          $res = $ws_service->prepareQuote($custid, $products, $store, $address, $smethod, $couponCode, $currency, $is_create_quote, $find_shipping, $theme);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }else if ($service == "preparequoteuser") {
          $res = $ws_service->prepareQuoteUser($custid, $store, $smethod,$address, $currency, $is_create_quote, $find_shipping, $theme);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
	 else if ($service == "placeorder") {
          $res = $ws_service->placeorder($custid, $products, $store, $address, $couponCode, $is_create_quote, $transid, $pmethod, $smethod, $currency, $messages, $theme);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "validateCoupon") {
          $res = $ws_service->ws_validateCoupon($couponCode, $custid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getProductStock1") {
          $res = $ws_service->getProductStock1($store,$service,$product_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }elseif ($service == "couponDetails") {
          $res = $ws_service->ws_couponDetails($couponCode, $store, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "sendorderemail") {
          $res = $ws_service->ws_sendorderemail($orderid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getShippingDetail") {
          $res = $ws_service->ws_getShippingDetail($pid, $address, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getFeaturedProducts") {
          $res = $ws_service->ws_getFeaturedProducts($currency, $service, $store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getNewProducts") {
          $res = $ws_service->ws_getNewProducts($currency, $service, $store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "mofluidappcountry") {
          $res = $ws_service->ws_mofluidappcountry($store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "paymentresponse") {
          $res = $ws_service->ws_printpaymentresfilterdataponse($store, $mofluidpayaction, $postdata, $mofluid_payment_mode, $mofluid_orderid_unsecure);
        } else if ($service == "authorizepaymentresponse") {
          $res = $ws_service->ws_authorizepaymentresponse($store, $service, $mofluid_orderid_unsecure, $postdata);
        } elseif ($service == "checkGiftMessage") {
          $res = $ws_service->ws_checkGiftMessage($store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "checkProductGiftMessage") {
          $res = $ws_service->ws_checkProductGiftMessage($store, $product_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "mofluidappstates") {
          $res = $ws_service->ws_mofluidappstates($store, $country);
          $echo= json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "countryStateList") {
          $res = $ws_service->ws_countryStateList($store, $country);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getmofluidextension") {
          $res = $ws_service->ws_getmofluidextension();
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "getpaymentmethod") {
          $res = $ws_service->ws_getpaymentmethod();
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "paymentresponse") {
          $res = $ws_service->ws_printpaymentresponse($store, $mofluidpayaction, $postdata, $mofluid_payment_mode);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "ebspayment") {
          $res = $ws_service->ws_ebspayment($store, $service, $mofluid_paymentdata);
        } else if ($service == "mofluid_ebs_pgresponse") {
          $res = $ws_service->ws_mofluid_ebs_pgresponse($store, $service, $mofluid_ebs_pgdata);
        } else if ($service == "mofluid_sendorder_mail") {
          $res = $ws_service->ws_sendorderemail($orderid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "get_configurable_product_details") {
          $res = $ws_service->get_configurable_products($productid, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "get_configurable_product_details_image") {
          $res = $ws_service->get_configurable_products_image($productid, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "get_configurable_product_details_description") {
          $res = $ws_service->get_configurable_products_description($productid, $currency, $store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "get_currency") {
          $res = $ws_service->get_currency($store, $service);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getallCMSPages") {
          $res = $ws_service->getallCMSPages($stfilterdataore, $pageId);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "mofluid_reorder") {
          $res = $ws_service->ws_mofluid_reorder($store, $service, $pid, $orderid, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "loginwithsocial") {
          $res = $ws_service->ws_loginwithsocial($store, $username, $firstname, $lastname);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        } elseif ($service == "mofluidUpdateProfile") {
          $res = $ws_service->mofluidUpdateProfile($store, $service, $custid, $billAdd, $shippAdd, $profile, $billshipflag);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getdeliver_timeslot") {
          $res = $ws_service->getdeliver_timeslot($store, $custid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "deliver_timeslot") {
          $res = $ws_service->deliver_timeslot($store, $timeslot, $custid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "initial") {
          $res = $ws_service->fetchInitialData($store, $service, $currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->clearHeaders()->setHeader('HTTP/1.0', 200, true)->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        } elseif ($service == "rootcategory") {
          $res = $ws_service->rootCategoryData($store, $service);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "test") {
          $res = $ws_service->test();
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "convert_currency") {
          $res = $ws_service->convert_currency($price, $from, $to);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "getImageOfSize") {
          $res = $ws_service->getImageOfSize($type, $legacy_id, $img_width, $img_height, $store);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "getAllImageOfSize") {
          $res = $ws_service->getAllImageOfSize($type, $legacy_id, $img_width, $img_height, $store, $theme);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "getProductBaseImage") {
          $res = $ws_service->getProductImages($productid, 'image', $get_all);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "getProductThumbImage") {
          $res = $ws_service->getProductImages($productid, 'thumbnail', $get_all);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "getProductSmallImage") {
          $res = $ws_service->getProductImages($productid, 'small_image', $get_all);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "mydownloads") {
          $res = $ws_service->MyDownloads($store,$custid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } elseif ($service == "getbestsellerproducts") {
          $res = $ws_service->ws_getBestsellerProducts($store, $service, $currency, $currentPage, $pageSize, $sortType, $sortOrder);
          //~ $res = $ws_service->ws_getBestsellerProducts($currency, $service, $store);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
        }
        /* cart syn */
        else if ($service == "addCartItem") {
          $res = $ws_service->ws_addCartItem($store,$service,$custid,$product_id,$qty);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
        else if ($service == "addGuestCartItem") {
          $res = $ws_service->ws_addGuestCartItem($store,$service,$custid,$product_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
        else if ($service == "removeCartItem") {
          $res = $ws_service->ws_removeCartItem($store,$service,$custid,$product_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
        else if ($service == "getCartItem") {
          $res = $ws_service->ws_getCartItem($store,$service,$custid,$currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
         else if ($service == "removeAllCartItem") {
          $res = $ws_service->ws_removeAllCartItem($store,$service,$custid);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }   else if ($service == "retrieveCustomerStripe") {
          $res = $ws_service->retrieveCustomerStripe($customer_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        } else if ($service == "createCardStripe") {
          $res = $ws_service->createCardStripe($customer_id,$token_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }else if ($service == "customerUpdateStripe") {
          $res = $ws_service->customerUpdateStripe($customer_id,$apiKey, $discription);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }else if ($service == "stripecustomercreate") {
          $res = $ws_service->stripecustomercreate($mofluid_Custid,$token_id,$email,$name,$price,$currency);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }else if ($service == "chargeStripe") {
          $res = $ws_service->chargeStripe($customer_id,$price,$currency,$card_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }elseif($service == "getcart"){
	  $res = $ws_service->ws_getCart($store,$customer_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
	}elseif($service == "removecartitem"){
	  $res = $ws_service->ws_removeItemFromCart($store,$customer_id,$product_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
	}elseif($service == "clearcart"){
	 $res = $ws_service->ws_clearCart($store,$customer_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
	}elseif($service=="update_cart_item"){
         $res = $ws_service->ws_updateCartItem($store,$customer_id,$product_id,$count);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }elseif($service=="add_to_cart"){
           $res = $ws_service->ws_addItemtoCart($store,$customer_id,$product_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }elseif($service=="address_list"){
          $res = $ws_service->ws_getAddressList($store,$customer_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }elseif($service=="add_new_address"){
         $res = $ws_service->ws_createAddress($store,$customer_id,$address_data);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }elseif($service=="update_address"){
          $res = $ws_service->ws_updateAddress($store,$customer_id,$address_id,$address_data);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }elseif($service=="get_address"){
          $res = $ws_service->ws_getAddress($store,$customer_id,$address_id);
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
       }elseif($service=="updateOrderStatus"){
          $res = $ws_service->ws_updateOrderStatus($orderid,$order_status);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setBody($echo);   
       }
       elseif($service=="webinfo"){
          $res = $ws_service->getWebsiteInfo();
          $echo= $callback . json_encode($res);
          $this->getResponse()->setBody($echo);
        }
       //To get Current Deals on Website
       elseif ($service == "getcurrentDeals") {
          $res = $ws_service->ws_currentDeals($store,$service);
          echo $this->getRequest()->getParam("callback") . json_encode($res);
       }
       //To get Upcoming Deals on Website
       elseif ($service == "getupcommingDeals") {
          $res = $ws_service->ws_upcommingDeals($store,$service);
          echo $this->getRequest()->getParam("callback") . json_encode($res);
       }
       //To get Details of Deals on Website
       elseif ($service == "getdealsDetails") {
          $res = $ws_service->ws_dealsDetails($store,$service,$deal_id,$currentPage,$pageSize,$sortType, $sortOrder, $currency, $price);
          echo $this->getRequest()->getParam("callback") . json_encode($res);
       }
       //New API to get Filters on Search Page
        elseif ($service == "getSearchFilter") {
          $res = $ws_service->ws_getSearchFilter($store,$search_data,$filterdata,0);
          echo $_GET["callback"] . json_encode($res);
        }
        //New API to get Search Page Product Listing
        elseif ($service == "getSearchCollection") {
          $res = $ws_service->ws_getSearchCollection($store, $search_data, $filterdata, $currentPage, $pageSize, $sortType, $sortOrder, $currency);
          echo $_GET["callback"] . json_encode($res);
        }
        elseif($service=="listbrands"){
          $res= $ws_service->listBrands();
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);   
       }elseif($service=="listbrandproducts"){
          $res= $ws_service->listBrandProducts($store, $service, $currency, $manufacturerId, $currentPage, $pageSize);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);   
       }elseif($service=="listblogs"){
          $res= $ws_service->listBlogs($store, $service, $currentPage, $pageSize);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);   
       }elseif($service=="blog"){
          $res= $ws_service->blog($store, $service, $blogId);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);   
       }elseif($service=="welcomescreen"){
          $res= $ws_service->welcomeScreen($store, $service);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="list_product_by_category_id"){
          $res= $ws_service->productListing($store, $service, $currency, $categoryId, $currentPage, $pageSize, $sortType, $sortOrder);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="submitreview"){
          $res= $ws_service->submitReview($store, $service, $ratingDescription, $ratingStar, $productId);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="getreviewforproduct"){
          $res= $ws_service->getReviewForProduct($storeId, $productId, $pageSize, $currPage);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo); 
       }
       //~ elseif($service=="get_category_filter"){
          //~ $res= $ws_service->getCategoryFilter($storeId, $categoryId);
          //~ $echo= $callback . json_encode($res); 
          //~ $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo); 
       //~ }
       elseif($service=="get_categories"){
          $res= $ws_service->getCategories($storeId);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo); 
       }elseif($service=="product_description"){
          $res= $ws_service->getProductDetailDescription($storeId, $productId, $currency);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo); 
       }elseif($service=="store_details"){
          $res= $ws_service->getStoreDetails($storeId, $theme);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="get_cart_items"){
          $res= $ws_service->getCartItems($storeId, $quoteId, $customerId, $currency);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="delete_cart_item"){
          $res= $ws_service->deleteItemFromCart($storeId, $quoteId, $itemId, $customerId = null);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="empty_cart"){
          $res= $ws_service->emptyCart($storeId, $quoteId, $customerId);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="apply_coupon_code"){
          $res= $ws_service->applyCouponCode($storeId, $quoteId, $couponCode, $couponCodeRemoveFlag);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="add_product_to_cart"){
          $res= $ws_service->addItemToCart($storeId, $customerId, $quoteId, $productInfo, $currency);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="get_quote"){
          $res= $ws_service->getQuote($storeId, $customerId, $quoteId);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="update_cart_items"){
          $res= $ws_service->updateCartItems($storeId, $customerId, $quoteId, $jsonCartInfo) ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="my_orders"){
          $res= $ws_service->gettingMyOrders($storeId, $currency, $currentPage, $pageSize)  ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="get_order_info"){
          $res= $ws_service->getOrderDetails($storeId, $currency, $orderIncrementId)  ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="list_all_shipping_methods"){
          $res= $ws_service->getAllActiveShippingMethods() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="list_all_countries"){
          $res= $ws_service->getCountryList() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="my_profile_data"){
          $res= $ws_service->getMyProfileData() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="list_customer_addresses"){
          $res= $ws_service->getCustomerAllAddresses() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="list_customer_addresses"){
          $res= $ws_service->getCustomerAllAddresses() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="myprofile_password_update"){
          $res= $ws_service->myProfilePasswordUpdate() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="save_billing_address"){
          $res= $ws_service->saveBillingAddress() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="save_shipping_address"){
          $res= $ws_service->saveShippingAddress() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="initialize_checkout"){
          $res= $ws_service->initializeCheckout($storeId, $quoteId, $currency) ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="place_order"){
          $res= $ws_service->createOrder() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="paypal_order_success"){
          $res= $ws_service->updatePaypalOrderSuccessStatus() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="list_shipping_methods"){
          $res= $ws_service->listShippingMethod($storeId, $quoteId) ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
       }elseif($service=="save_shipping_method") {
		   $res= $ws_service->saveShippingMethod() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
	  }elseif($service=="get_payment_method") {
		  $res= $ws_service->getPaymentMethodsList($storeId, $quoteId) ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
	  }elseif($service=="save_payment_method") {
		  $res= $ws_service->savePaymentMethod() ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
	  }elseif($service=="search") {
		  $res= $ws_service->search($storeId, $searchData, $filterData, $currentPage, $pageSize, $sortType, $sortOrder, $currency) ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
	  }elseif($service=="get_category_filter") {
		  $res= $ws_service-> ws_getNewCategoryFilter($storeId, $categoryId, $filterData) ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
	  }elseif($service=="get_category_filter_product") {
		  $res= $ws_service->ws_getLayerCollection($storeId, $categoryId, $filterData, $currentPage, $pageSize, $sortType, $sortOrder, $currency) ;
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
	  }elseif($service=="get_top_reviewed_products") {
		  $res= $ws_service->getTopReviewedProducts($storeId, $currentPage, $pageSize, $sortType, $sortOrder, $currency);
          $echo= $callback . json_encode($res); 
          $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
	  }elseif($service=="get_search_filter") {
		  $res = $ws_service->getSearchFilterCollection($storeId, $searchData, $filterData);
		  $echo = $callback.json_encode($res);
		  $this->getResponse()->setHeader('Content-Type', 'application/json', true)->setBody($echo);
	  }
        else {
          $this->ws_service404($service);
        }
      }
    } catch (Exception $exc) {
      $echo= 'Exception : ' . $exc;
      $this->getResponse()->setBody($echo);
    }

    Mage::app()->cleanCache();
    $cmd = ($this->getRequest()->getParam('cmd')) ? ($this->getRequest()->getParam('cmd')) : '';
    switch ($cmd) {
      case 'menu':
        $_helper = Mage::helper('catalog/category');
        $_categories = $_helper->getStoreCategories();
        $_categorylist = array();
        if (count($_categories) > 0) {
          foreach ($_categories as $_category) {
            $_helper->getCategoryUrl($_category);
            $_categorylist [] = array(
                'category_id' => $_category->getId(),
                'name' => $_category->getName(),
                'is_active' => $_category->getIsActive(),
                'position ' => $_category->getPosition(),
                'level ' => $_category->getLevel(),
                'url_key' => Mage::getModel('catalog/category')->load($_category->getId())->getUrlPath(),
                'thumbnail_url' => Mage::getModel('catalog/category')->load($_category->getId())->getThumbnailUrl(),
                'image_url' => Mage::getModel('catalog/category')->load($_category->getId())->getImageUrl(),
                'children' => Mage::getModel('catalog/category')->load($_category->getId())->getAllChildren()
            );
          }
        }
        $echo= json_encode($_categorylist);
        $this->getResponse()->setBody($echo);
        break;
      case 'catalog':
        $categoryid = $this->getRequest()->getParam('categoryid');
        // $filter = $this->getRequest()->getParam('filter');// my filter
        $page = ($this->getRequest()->getParam('page')) ? ($this->getRequest()->getParam('page')) : 1;
        $limit = ($this->getRequest()->getParam('limit')) ? ($this->getRequest()->getParam('limit')) : 5;
        $order = ($this->getRequest()->getParam('order')) ? ($this->getRequest()->getParam('order')) : 'entity_id';
        $dir = ($this->getRequest()->getParam('dir')) ? ($this->getRequest()->getParam('dir')) : 'desc';
        $category = Mage::getModel('catalog/category')->load($categoryid);
        $model = Mage::getModel('catalog/product');
        $collection = $category->getProductCollection()->addAttributeToFilter('status', 1)->addAttributeToFilter('visibility', array(
                    'neq' => 1
                ))->addAttributeToSort($order, $dir)/* ->setPage ( $page, $limit ) */;
        $pages = $collection->setPageSize($limit)->getLastPageNumber();
        if ($page <= $pages) {
          $collection->setPage($page, $limit);
          $productlist = $this->getProductlist($collection, 'catalog');
        }
        $echo= json_encode($productlist);
        $this->getResponse()->setBody($echo);
        break;
      case 'coming_soon':
        $page = ($this->getRequest()->getParam('page')) ? ($this->getRequest()->getParam('page')) : 1;
        $limit = ($this->getRequest()->getParam('limit')) ? ($this->getRequest()->getParam('limit')) : 5;
        // $todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
        $tomorrow = mktime(0, 0, 0, date('m'), date('d') + 1, date('y'));
        $dateTomorrow = date('m/d/y', $tomorrow);
        $tdatomorrow = mktime(0, 0, 0, date('m'), date('d') + 3, date('y'));
        $tdaTomorrow = date('m/d/y', $tdatomorrow);
        $_productCollection = Mage::getModel('catalog/product')->getCollection();
        $_productCollection->addAttributeToSelect('*')->addAttributeToFilter('visibility', array(
            'neq' => 1
        ))->addAttributeToFilter('status', 1)->addAttributeToFilter('special_price', array(
            'neq' => 0
        ))->addAttributeToFilter('special_from_date', array(
            'date' => true,
            'to' => $dateTomorrow
        ))->addAttributeToFilter(array(
            array(
                'attribute' => 'special_to_date',
                'date' => true,
                'from' => $tdaTomorrow
            ),
            array(
                'attribute' => 'special_to_date',
                'null' => 1
            )
        ))/* ->setPage ( $page, $limit ) */;
        $pages = $_productCollection->setPageSize($limit)->getLastPageNumber();
        // $count=$collection->getSize();
        if ($page <= $pages) {
          $_productCollection->setPage($page, $limit);
          $products = $_productCollection->getItems();
          $productlist = $this->getProductlist($products);
        }
        $echo= json_encode($productlist);
        $this->getResponse()->setBody($echo);
        break;
      case 'best_seller':
        $page = ($this->getRequest()->getParam('page')) ? ($this->getRequest()->getParam('page')) : 1;
        $limit = ($this->getRequest()->getParam('limit')) ? ($this->getRequest()->getParam('limit')) : 5;
        $todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $_products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*'/* array (
                          'name',
                          'special_price',
                          'news_from_date'
                          ) */)->addAttributeToFilter('visibility', array(
                    'neq' => 1
                ))->addAttributeToFilter('news_from_date', array(
                    'or' => array(
                        0 => array(
                            'date' => true,
                            'to' => $todayDate
                        ),
                        1 => array(
                            'is' => new Zend_Db_Expr('null')
                        )
                    )
                        ), 'left')->addAttributeToFilter('news_to_date', array(
                    'or' => array(
                        0 => array(
                            'date' => true,
                            'from' => $todayDate
                        ),
                        1 => array(
                            'is' => new Zend_Db_Expr('null')
                        )
                    )
                        ), 'left')->addAttributeToFilter(array(
                    array(
                        'attribute' => 'news_from_date',
                        'is' => new Zend_Db_Expr('not null')
                    ),
                    array(
                        'attribute' => 'news_to_date',
                        'is' => new Zend_Db_Expr('not null')
                    )
                ))->addAttributeToFilter('visibility', array(
                    'in' => array(
                        2,
                        4
                    )
                ))->addAttributeToSort('news_from_date', 'desc')/* ->setPage ( $page, $limit ) */;
        $pages = $_products->setPageSize($limit)->getLastPageNumber();
        // $count=$collection->getSize();
        if ($page <= $pages) {
          $_products->setPage($page, $limit);
          $products = $_products->getItems();
          $productlist = $this->getProductlist($products);
        }
        $echo= json_encode($productlist);
        $this->getResponse()->setBody($echo);
        break;
      case 'daily_sale':
        $page = ($this->getRequest()->getParam('page')) ? ($this->getRequest()->getParam('page')) : 1;
        $limit = ($this->getRequest()->getParam('limit')) ? ($this->getRequest()->getParam('limit')) : 5;
        $todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $tomorrow = mktime(0, 0, 0, date('m'), date('d') + 1, date('y'));
        $dateTomorrow = date('m/d/y', $tomorrow);
        // $collection = Mage::getResourceModel ( 'catalog/product_collection' );
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->/* addStoreFilter ()-> */addAttributeToSelect('*')->addAttributeToFilter('visibility', array(
            'neq' => 1
        ))->addAttributeToFilter('special_price', array(
            'neq' => "0"
        ))->addAttributeToFilter('special_from_date', array(
            'date' => true,
            'to' => $todayDate
        ))->addAttributeToFilter(array(
            array(
                'attribute' => 'special_to_date',
                'date' => true,
                'from' => $dateTomorrow
            ),
            array(
                'attribute' => 'special_to_date',
                'null' => 1
            )
        ));
        $pages = $collection->setPageSize($limit)->getLastPageNumber();
        // $count=$collection->getSize();
        if ($page <= $pages) {
          $collection->setPage($page, $limit);
          $products = $collection->getItems();
          $productlist = $this->getProductlist($products);
        }
        $echo= json_encode($productlist);
        $this->getResponse()->setBody($echo);
        break;
    }
  }

  /**
   * 
   */
  public function getProductlist($products, $mod = 'product') {
    $productlist = array();
    $baseCurrency = Mage::app()->getStore()->getBaseCurrency()->getCode();
    $currentCurrency = Mage::app()->getStore()->getCurrentCurrencyCode();
    foreach ($products as $product) {
      if ($mod == 'catalog') {
        $product = Mage::getModel('catalog/product')->load($product ['entity_id']);
        // $product = $_product;
      }
      // $echo= $product->getName ();
      $productlist [] = array(
          'entity_id' => $product->getId(),
          'sku' => $product->getSku(),
          'name' => $product->getName(),
          'news_from_date' => $product->getNewsFromDate(),
          'news_to_date' => $product->getNewsToDate(),
          'special_from_date' => $product->getSpecialFromDate(),
          'special_to_date' => $product->getSpecialToDate(),
          'image_url' => $product->getImageUrl(),
          'url_key' => $product->getProductUrl(),
          'regular_price_with_tax' => number_format(Mage::helper('directory')->currencyConvert($product->getPrice(), $baseCurrency, $currentCurrency), 2, '.', ''),
          'final_price_with_tax' => number_format(Mage::helper('directory')->currencyConvert($product->getSpecialPrice(), $baseCurrency, $currentCurrency), 2, '.', ''),
          'symbol' => Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
      );
    }
    return $productlist
    ;
  }

  public function testAction() {
	  echo "working";
    //die('dddd');
  }
}
