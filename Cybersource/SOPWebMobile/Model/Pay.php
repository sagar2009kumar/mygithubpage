<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Pay extends Mage_Payment_Model_Method_Cc
{
    /**
     * Payment method code
     * @access protected
     * @var string
     */
	protected $_code = 'cybersourcesop';
    /**
     * System config path of the block displayed when redirected to cybersource
     * @access protected
     * @var string
     */
	protected $_formBlockType = 'cybersourcesop/form_pay';
    /**
     * System config path
     * @access protected
     * @var string
     */
	protected $_infoBlockType = 'cybersourcesop/info_pay';
    /**
     *
     * @access protected
     * @var bool
     */
	protected $_isInitializeNeeded = true;
    /**
     * Payment details
     * @access protected
     * @var Variant_Object
     */
	protected $_paymentdetailsrequest = null;

    /**
     *
     * @access protected
     * @var bool
     */
    protected $_isGateway = true;
    /**
     *
     * @access protected
     * @var bool
     */
    protected $_canUseInternal          = true;
    /**
     * Used when checking out with saved card
     * @access protected
     * @var bool
     */
    protected $_canSaveCc = false;
    /**
     * Used to check if multi-shipping is enabled
     * @access protected
     * @var bool
     */
    protected $_canUseForMultishipping = false;
    /**
     * Used to authorize the payment
     * @access protected
     * @var bool
     */
	protected $_canAuthorize            = true;
    /**
     * Used to capture the payment
     * @access protected
     * @var bool
     */
	protected $_canCapture              = true;
    /**
     * Used during refund
     * @access protected
     * @var bool
     */
	protected $_canRefund               = true;
    /**
     * Used to refund partial capture
     * @access protected
     * @var bool
     */
	protected $_canRefundInvoicePartial = true;
    /**
     * Used to void transaction
     * @access protected
     * @var bool
     */
	protected $_canVoid                 = true;
    /**
     * Used to invoice the order
     * @access protected
     * @var bool
     */
	protected $_canCancelInvoice        = true;

    /**
     * Model used for encryption
     * @access protected
     * @var Cybersource_SOPWebMobile_Model_Security_Encryption
     */
	protected $_encryptionmodel = null;


	/**
	 * Redirect to the post page
	 *
	 * @return string|boolean
	 */
	public function getOrderPlaceRedirectUrl()
	{

		if((int)$this->_getOrderAmount() > 0){
			$url = Mage::getUrl('cybersourcesop/index/index',array('_secure' => true));
			$url = Mage::getModel('core/url')->sessionUrlVar($url); // process ___SID

			return $url .'?'. $this->getPaymentString();
		}else{
			return false;
		}
	}

	/**
	 * Assign data to info model instance
	 * Overridden to encrpy and pass the credit card details to the next page
	 *
	 * @param   mixed $data
	 * @return  Mage_Payment_Model_Info
	 */
	public function assignData($data)
	{
		if (!($data instanceof Varien_Object)) {
			$data = new Varien_Object($data);
		}

		$info = $this->getInfoInstance();

        //Get store ID.
        $storeId = Mage::app()->getStore()->getStoreId();
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$storeId);

        //add to get string, give no indication of the values by names for security

		//credit card details

		//check if token is used and add to request

        if ($data->getPaymentToken()) {
            $this->addToPaymentString('zswaqecdrytgbvh',$data->getPaymentToken());
            //user wants to update token
            if ($data->getCcUpdate()) {
                $this->addToPaymentString('vneifvnasdkjfnwi', $data->getCcUpdate());
            }
        } else {
            $info->setCcType($data->getCcType())
                 ->setCcNumber($data->getCcNumber())
                 ->setCcLast4(substr($data->getCcNumber(), -4));
        }

        //no token is being used - still include CC Details for Config.
        $this->addToPaymentString('kigqyhjuhkfgjvb', $data->getCcNumber())
		->addToPaymentString('kuklglvtziycxss', $data->getCcType())
		->addToPaymentString('ftkaueypxwhqkki', $data->getCcExpMonth())
		->addToPaymentString('zqbgnxelpjrerpi', $data->getCcExpYear());

        //save CC as Token? - working
		$this->addToPaymentString('rqowieufrhgnmzx', $data->getCcSave());
        if ($data->getCcSave()) {
            $info->setCybersourcesopSaveToken('true');
        }
        //update Token
        //add if config allows
		if(Cybersource_SOPWebMobile_Model_Source_Consts::useCvn($storeId))
		{
			$this->addToPaymentString('rpdzzimldboiecl', $data->getCcCid());
		}

        if (isset($config['mobile_enabled']) && $config['mobile_enabled'] == 1) {
            $this->addToPaymentString('mobile',true);
        }

		$info->setCcOwner($data->getCcOwner())
		->setCcCid($data->getCcCid())
		->setCcExpMonth($data->getCcExpMonth())
		->setCcExpYear($data->getCcExpYear())
		->setCcSsIssue($data->getCcSsIssue())
		->setCcSsStartMonth($data->getCcSsStartMonth())
		->setCcSsStartYear($data->getCcSsStartYear())
		;


		return $this;
	}



	/**
	 * Build an array of encrypted values
	 * Base 64 encde to get around the non printing characters issue
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return Cybersource_SOPWebMobile_Model_Pay
	 */
	private function addToPaymentString($field,$value)
	{
		if($value != '')
		{
                $this->_paymentdetailsrequest[$field] = $this->_getEncryptor()->encrypt(base64_encode($value));
		}

		return $this;

	}

	/**
	 * return a url string of the payment details
	 *
	 * @return string
	 */
	private function getPaymentString()
	{
		return http_build_query($this->_paymentdetailsrequest, '', '&');
	}

    /**
     * Validates the payment method/card
     * @return $this
     */
    public function validate()
	{

		return $this;
	}


	/**
	 * Instantiate state and set it to state object
	 * ONly used for the front end
	 * @param string $paymentAction
	 * @param Varien_Object
	 */
	public function initialize($paymentAction, $stateObject)
	{
		$state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
		$stateObject->setState($state);
		$stateObject->setStatus('pending_payment');
		$stateObject->setIsNotified(false);
	}


	/**
	 * flag if we need to run payment initialize while order place
	 *
	 * @return bool
	 */
	public function isInitializeNeeded()
	{
		if($this->_isAdmin())
		{
			return false;

		}else
		{
			return parent::isInitializeNeeded();
		}

	}

    /**
     * Returns grant total amount
     * @return float
     */
    private function _getOrderAmount()
	{
		$info = $this->getInfoInstance();
		if ($this->_isPlacedOrder()) {
			return (double)$info->getOrder()->getQuoteBaseGrandTotal();
		} else {
			return (double)$info->getQuote()->getBaseGrandTotal();
		}
	}

	/**
	 * Check if the request is from the admin area
	 *
	 * @return boolean
	 */
	private function _isAdmin()
	{
		if(Mage::app()->getStore()->isAdmin())
		{
			return true;
		}

		if(Mage::getDesign()->getArea() == 'adminhtml')
		{
			return true;
		}

		return false;
	}

    /**
     * Checks the status of the checkout.
     * @return bool
     */
    private function _isPlacedOrder()
	{
		$info = $this->getInfoInstance();
		if ($info instanceof Mage_Sales_Model_Quote_Payment) {
			return false;
		} elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
			return true;
		}
	}

    /**
     * Gets the encryption model
     * @return Cybersource_SOPWebMobile_Model_Security_Encryption
     */
    private function _getEncryptor()
	{
        if($this->_encryptionmodel == null)
        {
            $this->_encryptionmodel = Mage::getModel('cybersourcesop/security_encryption')->setCryptWithKey(null,true);
        }
        return $this->_encryptionmodel;
	}

	/**
	 * Order increment ID getter (either real from order or a reserved from quote)
	 *
	 * @return string
	 */
	private function _getOrderId()
	{
		$info = $this->getInfoInstance();

		if ($this->_isPlacedOrder()) {
			return $info->getOrder()->getIncrementId();
		} else {
			if (!$info->getQuote()->getReservedOrderId()) {
				$info->getQuote()->reserveOrderId();
			}
			return $info->getQuote()->getReservedOrderId();
		}
	}

    /**
     * Process the refunds
     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     */
    public function refund(Varien_Object $payment, $amount)
	{

		Mage::getModel('cybersourcesop/soapapi_refund')->process($payment, $amount);

	 	return $this;
	}

	/**
	 * Overridden for admin area SOAP calls
	 *
	 * @see Mage_Payment_Model_Method_Abstract::authorize()
     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     */
    public function authorize(Varien_Object $payment, $amount)
	{
		if($this->_isAdmin())
		{
			//call soap API
			Mage::getModel('cybersourcesop/soapapi_auth')->process($payment, $amount, $this->_getOrderId());

		}

		return $this;
	}

	/**
	 * Overridden for admin area SOAP calls
	 *
	 * @see Mage_Payment_Model_Method_Abstract::authorize()

     * @param Varien_Object $payment
     * @param $amount
     * @return $this
     */
    public function capture(Varien_Object $payment, $amount)
	{

		if($this->_isAdmin())
		{
			//call soap API
			Mage::getModel('cybersourcesop/soapapi_capture')->process($payment, $amount, $this->_getOrderId());

		}

		return $this;


	}

    /**
     * Checks if partial capture is enabled
     * @return bool
     */
    public function canCapturePartial()
    {
        //Get store ID.
        $storeId = $this->getInfoInstance()->getOrder()->getStoreId();
        $partialCapture = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('partial_capture_enabled',$storeId);
        if($partialCapture == true){
            $this->_canCapturePartial = true;
        }

        return $this->_canCapturePartial;
    }

	/**
	 * Void payment abstract method
	 *
	 * @param Varien_Object $payment
	 *
	 * @return Mage_Payment_Model_Abstract
	 */
	public function void(Varien_Object $payment) {
		if ($this->_isAdmin()) {
			//call Soap API
			Mage::getModel('cybersourcesop/soapapi_void')->process($payment);
		}
		return $this;
	}

}
