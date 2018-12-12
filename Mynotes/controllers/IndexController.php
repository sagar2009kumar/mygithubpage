<?php

class Mofluid_Mynotes_IndexController extends Mage_Core_Controller_Front_Action {

	/**** to indicate that the error has occurred ****/
	private	static $_isError = 0;
	private	static $_errorMessage = array();
	private static $_customerId = null;

	public function indexAction() {
	
		echo "Hello Notes System";

	}

	/**** Function to add the notes of the customer ****/

	public function addAction() {

		/**** check if the api is valid or not ****/
		$this->validateApi();
		if(self::$_isError) return;	

		/**** If api is valid ****/
		$postedData = Mage::helper('mofluid_mynotes')->getJsonPostData();

		$validPdId = Mage::helper('mofluid_mynotes')->checkField($postedData['product_id']);
		$validND = Mage::helper('mofluid_mynotes')->checkField($postedData['note_description']);

		/**** check whether product id is present or not ****/
		if(!$validPdId) {
			$res = array('status'=>0, 'errorMessage'=>'Field missing product_id');
			$this->setJsonResponse($res, 422);
			return;
		}
		/**** check whether the note description is present or not ****/
		if(!$validND) {
			$res = array('status'=>0, 'errorMessage'=>'Field missing note_description');
			$this->setJsonResponse($res, 422);
			return;
		}

		$_product = Mage::getModel('catalog/product')->load($postedData['product_id']);

		if(!$_product->getId()){
			$res = array('status'=>0, 'errorMessage'=>'Invalid Product Id.');
			$this->setJsonResponse($res, 400);
			return;
		}

		/**** getting the current time and date ****/
		$currentTime =	Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');

		/**** Load the model of the mynotes ****/
		$model = Mage::getModel('mofluid_mynotes/mynotes');
		
		/**** setting the various attributes of the model ****/
		$model->setCustomerId(self::$_customerId);
		$model->setProductId($postedData['product_id']);
		$model->setNoteDescription($postedData['note_description']);
		$model->setCreatedAt($currentTime);
		
		try {
			$model->save();
			$res = array('status'=> 1, 'message'=>'Note saved successfully.');
			$this->setJsonResponse($res);
		}catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>$e->getMessage());
			$this->setJsonResponse($res, 400);
		}	
	}
	
	/**** Function to get the particular notes ****/

	public function getAction() {

		/**** check if the api is valid or not ****/
		$this->validateApi();
		if(self::$_isError) return;

		/**** If api is valid ****/

		$noteId = $this->getRequest()->getParam('id', null);

		if(is_null($noteId)) {
			$res = array('status'=>0, 'errorMessage'=>'Field missing id.');
            $this->setJsonResponse($res, 422);
            return;	
		}

		$model = Mage::getModel('mofluid_mynotes/mynotes')->load($noteId);

		if(!$model->getId()) {
			$res = array('status'=>0, 'errorMessage'=>'Note does not exists.');
			$this->setJsonResponse($res, 400);
		}	
		
		$productId = $model->getProductId();
		$noteDescription = $model->getNoteDescription();
		$noteId = $model->getId();
		$_product = Mage::getModel('catalog/product')->load($productId);
		$productUrl = $_product->getProductUrl();
		$productImageUrl = (string)Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
		$productName = $_product->getName();
			
		$res = array('status'=>1, 'noteId'=>$noteId, 'noteDescription'=> $noteDescription, 'productId'=>$productId, 'productName'=>$productName, 'productImage'=>$productImageUrl, 'productUrl'=>$productUrl);	
		
		$this->setJsonResponse($res, 200);	
	}

	/**** Function to list the notes of a customer ****/

	public function listnotesAction() {

		/**** check if the api is valid or not ****/
        $this->validateApi();
        if(self::$_isError) return;	

		$currentPage = $this->getRequest()->getParam('current_page', 1);
		
		$result = Mage::getModel('mofluid_mynotes/mynotes')->getNotesList(self::$_customerId, $currentPage);

		if($result['status'] == 1) {
			$this->setJsonResponse($result, 200);
		}else{
			$this->setJsonResponse($result, 400);
		}
	}
	
	/**** Function to validate the api ****/
	/* This function will check if the customer is valid if valid then set the private member */

	public function validateApi() {
	
		/**** Authenticate the api ****/
        $customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();

        if(is_null($customerId)) {
			self::$_isError = 1;
			$this->setJsonResponse(Mofluid_Mynotes_Helper_Data::CUSTOMER_VALIDATION_ERROR_MSG, 401); 	
			return;
        }

		self::$_customerId = $customerId;
	}

	/**** Function to set the json response data for the request ****/	

	public function setJsonResponse($body = Mofluid_Mynotes_Helper_Data::DEFAULT_MSG, $statusCode = 200,$contentType = 'application/json' ) {

		$body = Mage::helper('core')->jsonEncode($body);
		$this->getResponse()->clearHeaders()->setHeader('HTTP/1.0', $statusCode, true);
		$this->getResponse()->setHeader('Content-Type', $contentType); 
	    $this->getResponse()->setBody($body);
	}

}
