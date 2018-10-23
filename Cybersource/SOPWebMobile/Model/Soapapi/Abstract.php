<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */
class Cybersource_SOPWebMobile_Model_Soapapi_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * SOAP request object.
     * @access protected
     * @var Varien_Object
     */
	protected $_request;
    /**
     * Sales order reference number
     * @access protected
     * @var string
     */
	protected $_orderref = null;
    /**
     * Store Id where the order is being placed
     * @access protected
     * @var int|string
     */
    public $_storeId = null;

    /**
     * Initialise SOAP request
     *
     * @param Mage_Sale_Model_Order_Payment $payment
     */
	protected function iniRequest($payment = null)
	{
		$this->_request = new stdClass();

        $storeId = null;
        //if a payment has been passed in, it will contain a store that the payment was made from.
        if($payment){
            $storeId = $payment->getOrder()->getStoreId();
        }else{
            $orderId = Mage::app()->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $storeId = $order->getStoreId();
            $this->_storeId = $storeId;
        }
        //set store ID in session for use with SOAP API Requests.
        Mage::getModel('core/session')->setSoapRequestStoreId($storeId);
        $this->_request->merchantID = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('merchant_id',$storeId);
		$this->_request->merchantReferenceCode = $this->_generateReferenceCode();

		$this->_request->clientLibrary = "PHP";
		$this->_request->clientLibraryVersion = phpversion();
	}

    /**
     * Sets storeId
     *
     * @param string $refin
     */
	protected function assignOrderRef($refin)
	{
		if($refin)
		{
			$this->_orderref = $refin;
		}
	}

    /**
     * Generator for merchant reference code
     *
     * @return random number
     */
    protected function _generateReferenceCode()
    {
    	//else use unique hash
    	if(is_null($this->_orderref))
    	{
    		return Mage::helper('core')->uniqHash();
    	}

    	return $this->_orderref;
    }


	/**
	 * Getting Soap Api object
	 *
	 * @param   array $options
	 * @return  Mage_Cybersource_Model_Api_ExtendedSoapClient
	 */
	protected function getSoapApi($options = array())
	{
        $wsdlURL = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('test',$this->_storeId) ? Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('wsdl_test_url',$this->_storeId):Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('wsdl_live_url',$this->_storeId);
        if(strlen($wsdlURL)>10){
            $wsdl=$wsdlURL;
        }else{
            $wsdl = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('test',$this->_storeId) ? Cybersource_SOPWebMobile_Model_Source_Consts::WSDL_URL_TEST  : Cybersource_SOPWebMobile_Model_Source_Consts::WSDL_URL_LIVE;
        }
		$_api = new Cybersource_SOPWebMobile_Model_Soapapi_Client_ExtendedSoapClient($wsdl, $options);
		return $_api;
	}


}
