<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Soapapi_Order extends Cybersource_SOPWebMobile_Model_Soapapi_Abstract
{
	/**
	 * overwrites the method of Mage_Payment_Model_Method_Cc
	 * Assign data to info model instance
	 *
	 * @param   mixed $data
	 * @return  Mage_Payment_Model_Info
	 */
	public function assignData($data)
	{

		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}
		parent::assignData($data);
		$info = $this->getInfoInstance();

		return $this;
	}

	/**
	 * Getting customer IP address
	 *
	 * @return IP address string
	 */
	protected function getIpAddress()
	{
		return Mage::helper('core/http')->getRemoteAddr();
	}

	/**
	 * Assigning billing address to soap
	 *
	 * @param Varien_Object $billing
	 * @param String $email
	 */
	protected function addBillingAddress($billing, $email)
	{
		if (!$email) {
			$email = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getEmail();
		}

        // Max character count for bill_to_company API request field is 40.
        $billToCompany = $billing->getCompany();

        if(strlen($billToCompany) > 40){
            $billToCompany = substr($billToCompany,0,40);
        }

		$billTo = new stdClass();
		$billTo->firstName = $billing->getFirstname();
		$billTo->lastName = $billing->getLastname();
		$billTo->company = $billToCompany;
		$billTo->street1 = $billing->getStreet(1);
		$billTo->street2 = $billing->getStreet(2);
		$billTo->city = $billing->getCity();
		$billTo->state = $billing->getRegion();
		$billTo->postalCode = $billing->getPostcode();
		$billTo->country = $billing->getCountry();
		$billTo->phoneNumber = $billing->getTelephone();
		$billTo->email = ($email ? $email : Mage::getStoreConfig('trans_email/ident_general/email'));
		$billTo->ipAddress = $this->getIpAddress();
		$this->_request->billTo = $billTo;
	}

	/**
	 * Assigning shipping address to soap object
	 *
	 * @param Varien_Object $shipping
	 */
	protected function addShippingAddress($shipping)
	{
		//checking if we have shipping address, in case of virtual order we will not have it
		if ($shipping) {

            // Max character count for ship_to_company API request field is 40.
            $shipCompany = $shipping->getCompany();

            if(strlen($shipCompany) > 40){
                $shipCompany = substr($shipCompany,0,40);
            }

			$shipTo = new stdClass();
			$shipTo->firstName = $shipping->getFirstname();
			$shipTo->lastName = $shipping->getLastname();
			$shipTo->company = $shipCompany;
			$shipTo->street1 = $shipping->getStreet(1);
			$shipTo->street2 = $shipping->getStreet(2);
			$shipTo->city = $shipping->getCity();
			$shipTo->state = $shipping->getRegion();

            $shipPostCode= $shipping->getPostcode();
            if(strlen($shipPostCode) > 10){
                $shipPostCode = substr($shipPostCode,0,10);
            }
			$shipTo->postalCode = $shipPostCode;
			$shipTo->country = $shipping->getCountry();
			$shipTo->phoneNumber = $shipping->getTelephone();
			$this->_request->shipTo = $shipTo;
		}
	}

	/**
	 * Assigning credit card information
	 *
	 * @param Mage_Model_Order_Payment $payment
	 */
	protected function addCcInfo($payment)
	{
		$card = new stdClass();
		$card->fullName = $payment->getCcOwner();
		$card->accountNumber = $payment->getCcNumber();
		$card->expirationMonth = $payment->getCcExpMonth();
		$card->expirationYear =  $payment->getCcExpYear();
		if ($payment->hasCcCid()) {
			$card->cvNumber =  $payment->getCcCid();
		}

		$this->_request->card = $card;
	}
    /**
     * Assigning purchased items to soap
     *
     * @param Mage_Sales_Model_Order $order
     */
    protected function addLineItemInfo($order) {
        //get Config.
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);

        $orderItems = $order->getAllItems();
        $i = 0;
        $items = array();
        foreach ($orderItems as $orderItem) {
            ${"item" . $i } = new stdClass();
            ${"item" . $i }->id = $i;
            //Customer billed in Default (website) currency, set unitPrice to  price
            if (isset($config['default_currency']) && $config['default_currency'] == Cybersource_SOPWebMobile_Model_Source_Currency::DEFAULT_CURRENCY) {
                ${"item" . $i }->unitPrice = number_format($orderItem->getPrice(), 2, '.', '');
            } else {
                //Customer billed in Base currency, set unitPrice to basePrice
                ${"item" . $i }->unitPrice = number_format($orderItem->getBasePrice(), 2, '.', '');
            }
            ${"item" . $i }->quantity = number_format($orderItem->getQtyOrdered(), 0, '.', '');
            ${"item" . $i }->productSKU = $orderItem->getSku();
            ${"item" . $i }->productName = $orderItem->getName();
            ${"item" . $i }->taxAmount = number_format($orderItem->getTaxAmount(), 2, '.', '');
            $items[] =  ${"item".$i};
            $i++;
        }
        $this->_request->item = $items;
    }
    /**
     *
     * Returns the decision manager system config field.
     * @return boolean
     */
    public function getDMConfig() {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        return $config['dm_enabled'] ? true : false;
    }

    /**
     *
     * Returns true / false if Decision Manager should be used for the payment.
     * Accepts an order ID in to load the current order when specifying custom logic.
     * @param mixed $orderref
     * @return boolean
     */
    public function enableDM($orderref) {
        if ($this->getDMConfig()) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Assigning purchased items to soap
     *
     * @param mixed $orderref Sales order reference number
     * @return Mage_Sales_Model_Order
     */
    public function getOrder($orderref) {
        return Mage::getModel('sales/order')->load($orderref);
    }
    /**
     * Add decision manager to soap
     *
     * @param mixed $orderref Sales order reference number     *
     */
    protected function addDecisionManager($orderref)
    {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);

        $decisionManager = new stdClass();
        //Check if Decision Manager is enabled for back-end orders
        if($this->enableDM($orderref) && ($config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::BACKEND_ORDERS || $config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::ALL_ORDERS)){
            $decisionManager->enabled = "true";
        }else{
            $decisionManager->enabled = "false";
        }
        $this->_request->decisionManager = $decisionManager;
    }

    /**
     * Adds merchantDefinedData to soap
     *
     * @param mixed $orderref Sales order reference number     *
     */
    protected function addMerchantDefinedFields($orderref)
    {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderref);

        if($this->enableDM($orderref) && ($config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::BACKEND_ORDERS || $config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::ALL_ORDERS)){
            if($this->enableMDD($orderref)){

                $merchantDefinedData = new stdClass();
                $existingFields = count($config['mdd_fields']);
                //loop through Merchant Defined fields in config. Currently only 10 merchant defined fields can be added.
                //use of $i = 1 and $i < 11 to ensure the correct number ($i) is appended to the merchant_defined_data field
                for($i = 1;$i < $existingFields;$i++){
                    //If field is set to none or is empty, continue.
                    if(empty($order[$config['mdd_'.$i]]) || $order[$config['mdd_'.$i]] === '-- none --'){
                        continue;
                    }else{
                        //Add Merchant Defined field to request.
                        $merchantField = 'field'.$i;
                        $merchantDefinedData->$merchantField = $order[$config['mdd_'.$i]];
                    }
                }
                //add additional user defined fields from sample module
                if ($this->addAdditionalMerchantDefinedFields($orderref)) {
                    array_push($merchantDefinedData,$this->addAdditionalMerchantDefinedFields($orderref));
                }
                $this->_request->merchantDefinedData = $merchantDefinedData;

            }
        }
    }

    /**
     *
     * Adds additional merchantDefinedData to soap
     *
     * @param mixed $orderref Sales order reference number
     * @return Varien_Object|boolean
     */
    protected function addAdditionalMerchantDefinedFields($orderref)
    {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        $existingFields = count($config['mdd_fields']);

        if($this->enableDM($orderref) && ($config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::BACKEND_ORDERS || $config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::ALL_ORDERS)){
            if($this->enableMDD($orderref)){

                $merchantDefinedData = new stdClass();
                $fields = $this->getAdditionalMerchantFields($orderref);
                if ($fields) {
                    //loop through Merchant Defined fields in config. Currently only 10 merchant defined fields can be added.
                    //use of $i = 1 and $i < 11 to ensure the correct number ($i) is appended to the merchant_defined_data field
                    foreach($fields as $key => $value){
                        //Add Merchant Defined field to request.
                        $merchantField = 'field';
                        $merchantField += $existingFields+$key;
                        $merchantDefinedData->$merchantField = $value;

                    }
                    return $merchantDefinedData;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * Returns empty array.
     * @param mixed $orderref
     * @return array
     */

    public function getAdditonalMerchantFields($orderref) {
        return array();
    }

    /**
     *
     * Returns AVS and CVN fail codes as an array.
     * @return array
     */
	protected function getSoapReviewCodes()
	{
		return array(Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_AVS_FAIL,Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_CVV_FAIL);

	}


	/**
	 * To assign transaction id and token after capturing payment
	 *
	 * @param Mage_Sale_Model_Order_Invoice $invoice
	 * @param Mage_Sale_Model_Order_Payment $payment
	 * @return Mage_Cybersource_Model_Soap
	 */
	public function processInvoice($invoice, $payment)
	{
		parent::processInvoice($invoice, $payment);
		$invoice->setTransactionId($payment->getLastTransId());
		$invoice->setCybersourceToken($payment->getLastCybersourceToken());
		return $this;
	}


	/**
	 * To assign transaction id and token before voiding the transaction
	 *
	 * @param Mage_Sale_Model_Order_Invoice $invoice
	 * @param Mage_Sale_Order_Payment $payment
	 * @return Mage_Cybersource_Model_Soap
	 */
	public function processBeforeVoid($invoice, $payment)
	{
		parent::processBeforeVoid($invoice, $payment);
		$payment->setVoidTransactionId($invoice->getTransactionId());
		$payment->setVoidCybersourceToken($invoice->getCybersourceToken());
		return $this;
	}
    /**
     *
     * Returns true if decision manager config is enabled and false otherwise.
     * @return boolean
     */
    protected function getMDDConfig() {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        return $config['mdd_enabled'] ? true : false;
    }
    /**
     *
     * Returns true if decision manager is enabled  and false otherwise.
     * @param mixed $orderref
     * @return boolean
     */
    public function enableMDD($orderref) {
        if ($this->getMDDConfig()) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     * Process payment authorisation/capture response and returns false if there is no error during AVS and/or CVN check.
     * Otherwise, the error message is returned.
     *
     * @param String $configval
     * @param Varien_Object $cybersourceResponse payment authorisation/capture response
     * @return boolean|string
     */
    protected function processAvsCvnCodes($config,$paymentAuthResponse){
        try{
            $successCodes = explode(',',str_replace(' ','',$config['forceavs_codes']));
            $successCodes = count($successCodes)>1?$successCodes:Cybersource_SOPWebMobile_Model_Source_Consts::getAvsSuccessVals();
            $reviewError=  $this->reviewAvsCvnCodes('forceavs', Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_AVS_FAIL, 'avsCode', $successCodes,$paymentAuthResponse );
            $successCodes = explode(',',str_replace(' ','',$config['forcecvn_codes']));
            $successCodes = count($successCodes)>1?$successCodes:Cybersource_SOPWebMobile_Model_Source_Consts::getCvnSuccessVals();
            $reviewError  = $reviewError.$this->reviewAvsCvnCodes('forcecvn', Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_CVV_FAIL, 'cvCode', $successCodes,$paymentAuthResponse );
            unset($successCodes);
            if($reviewError){
                return $reviewError;
            }
        }
        catch (Exception $e) {
            $reviewError=$e->getMessage();
            return $reviewError;
        }
        return true;
    }
    /**
     *
     * returns false if there is no error during AVS and/or CVN check.
     * Otherwise, the error message is returned.
     *
     * @param String $configval
     * @param int $failvalue cybersource reason code
     * @param String $statuscheckval ccAuthReply avsCode or cvCode
     * @param array $statussuccessarray holds AVS or CVN response codes
     * @param Varien_Object $cybersourceResponse payment authorisation/capture response
     * @return boolean|string
     */
    protected function reviewAvsCvnCodes($configval, $failvalue, $statuscheckval, $statussuccessarray,$cybersourceResponse )
    {
        $codeErrorMessage=false;
        $action = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig($configval,$this->_storeId);
        if($action == Cybersource_SOPWebMobile_Model_Source_Consts::CONFIG_CARDCHECK_DECLINE){
            if(isset($cybersourceResponse->ccAuthReply)){
                $ccAuthReply = (array)$cybersourceResponse->ccAuthReply;
                if (array_key_exists($statuscheckval,$ccAuthReply)) {

                    if (isset($cybersourceResponse->reasonCode)) {
                        $reasonCode = (int)$cybersourceResponse->reasonCode; //cast to integer as CYBS returns this value as INT or String for Web/Mobile.
                        if(($reasonCode == $failvalue) || (!in_array($ccAuthReply[$statuscheckval], $statussuccessarray)))
                        {
                            $codeErrorMessage = $codeErrorMessage. $configval=='forcecvn'?Cybersource_SOPWebMobile_Model_Source_Consts::getCVNErrorCode($ccAuthReply[$statuscheckval]):Cybersource_SOPWebMobile_Model_Source_Consts::getAVSErrorCode($ccAuthReply[$statuscheckval]);
                        }
                    }
                }
            }
            else{
                $codeErrorMessage='Credit card failed authorization.';
            }
        }
        return $codeErrorMessage;
    }
}
