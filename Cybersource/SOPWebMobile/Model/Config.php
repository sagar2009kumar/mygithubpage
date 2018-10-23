<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Config extends Mage_Core_Model_Abstract
{
    //class to build the payment details
    /**
     * Array of checkout Form Fields
     * @var array
     */
    public $_fields = array();
    /**
     * Stores cybersource url
     * @var string
     */
    public $_url = null;
    /**
     * Holds the details of the order to be placed
     * @var Mage_Sale_Model_Order
     */
    public $_order = null;
    /**
     * The store id where the order is placed.
     * @var string
     */
    public $_storeId = null;

    /**
     *  used when Token is used for auth
     * @var string
     */
    public $_authoriseToken = null;
    /**
     * Payment token
     * @var string
     */
    protected $_tokenId;
    /**
     * Model used for encryption
     * @var Cybersource_SOPWebMobile_Model_Security_Encryption
     */
    protected $_encryptionmodel = null;
    /**
     * Set to true if the billing address provided by the customer does
     * match with the one associated with the card.
     * @var bool
     */
    protected $_addressMatch;

    /**
     * Main constructor
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Assign all config fields to the request
     * @return $this|bool
     */
    public function assignAllData()
    {
        try {
            $this->setOrder();
            $this->assignCoreFields();
            $this->assignSystemConfigFields();
            $this->assignOrderFields();
            $this->assignLineItems();
            $this->addPaymentFields();
            $this->addMerchantDefinedFields();
            $this->addAdditionalMerchantDefinedFields();
            $this->addUnsignedFields();
            $this->addSignedFields();
        } catch (Exception $e) {
            Mage::log('Fatal Config Error: ' . $e->getMessage(), null, Cybersource_SOPWebMobile_Model_Source_Consts::LOGFILE);
            return false;
        }

        return $this;
    }

    /**
     * Returns Checkout Form Fields
     * @return array
     */
    public function getCheckoutFormFields()
    {
        if ($this->isMobile()) {
            if(isset($this->_fields['card_cvn']))
                unset($this->_fields['card_cvn']);
            if(isset($this->_fields['card_number']))
                unset($this->_fields['card_number']);
            if(isset($this->_fields['card_expiry_date']))
                unset($this->_fields['card_expiry_date']);
            if(isset($this->_fields['card_type']))
                unset($this->_fields['card_type']);
        }

        return $this->_fields;

    }

    /**
     * Return the code based on the Magento code
     * @param string $ccin
     * @return mixed
     */
    public function getCCVals($ccin)
    {
        return Cybersource_SOPWebMobile_Model_Source_Consts::getCyberCCs($ccin);

    }

    /**
     * Returns signed Checkout Form Fields
     * @return mixed
     */
    public function getSignature()
    {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        if ($this->isMobile()) {
            return Mage::helper('cybersourcesop/security')->sign($this->getCheckoutFormFields(),$config['mobile_merchant_secret_key']);
        } elseif ($this->isOneStepEnabled()) {
            $formFields = $this->getCheckoutFormFields();
            if(isset($formFields['card_cvn']))
                unset($formFields['card_cvn']);
            if(isset($formFields['card_number']))
                unset($formFields['card_number']);
            if(isset($formFields['card_expiry_date']))
                unset($formFields['card_expiry_date']);
            if(isset($formFields['card_type']))
                unset($formFields['card_type']);
            return Mage::helper('cybersourcesop/security')->sign($formFields,(string)$config['secret_key']);
        }

        return Mage::helper('cybersourcesop/security')->sign($this->getCheckoutFormFields(),$config['secret_key']);

    }

    /**
     * Returns true if web / Mobile is enabled and false otherwise.
     * @return bool
     */
    public function isMobile() {
        $storeId = null;
        if ($this->_storeId) {
            $storeId = $this->_storeId;
        }
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$storeId);
        if (isset($config['mobile_enabled']) && $config['mobile_enabled'] == 1) {
            return true;
        }
        return false;
    }

    /**
     * Setter method for the storeID.
     * @param mixed $storeId
     * @return mixed
     */
    public function setStoreId($storeId) {
        $this->_storeId = $storeId;
        return $this->_storeId;
    }

    /**
     *   Add unsigned Checkout Form Fields to the request
     */
    protected function addUnSignedFields()
    {
        if (!$this->isMobile()) {
            $formFields = array();
            $cardFields = $this->getCheckoutFormFields();
            if(isset($cardFields['card_cvn']))
                $formFields['card_cvn'] = $cardFields['card_cvn'];
            if(isset($cardFields['card_number']))
                $formFields['card_number'] = $cardFields['card_number'];
            if(isset($cardFields['card_expiry_date']))
                $formFields['card_expiry_date'] = $cardFields['card_expiry_date'];
            if(isset($cardFields['card_type']))
                $formFields['card_type'] = $cardFields['card_type'];
            $fieldnames = implode(',', array_keys($formFields));
            $this->addField('unsigned_field_names',$fieldnames,true);
        }
    }


    /**
     *   Add signed Checkout Form Fields to the request
     */
    protected function addSignedFields()
    {

        $formFields = $this->getCheckoutFormFields();
        unset($formFields['card_cvn']);
        unset($formFields['card_number']);
        unset($formFields['card_expiry_date']);
        unset($formFields['card_type']);
        $fieldnames = implode(',', array_keys($formFields));
        $fieldnames = $fieldnames . ',signed_field_names';
        $this->addField('signed_field_names',$fieldnames,true);
    }

    /**
     *   Add core fields to the request
     */
    protected function assignCoreFields()
    {
        $url = Mage::getUrl('cybersourcesop/index/receipt',array('_secure' => true ));
        $position = strpos($url,"?");
        if ($position) {
            $newString = substr($url,0,$position);
            $url = $newString;
        }
        $locale = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('cybersource_locale',$this->_order->getStoreId());

        $this->addField('payment_method', Cybersource_SOPWebMobile_Model_Source_Consts::PAY_METHOD)
            ->addField('locale', isset($locale)?$locale:Cybersource_SOPWebMobile_Model_Source_Consts::LOCALE)
            ->addField('customer_ip_address', $this->_order->getRemoteIp())
            ->addField('signed_date_time', gmdate("Y-m-d\TH:i:s\Z"))
            ->addField('override_custom_receipt_page', $url);

    }

    /**
     * Get config values from the user
     * Throw a different exception if it doesnt work
     */
    protected function assignSystemConfigFields()
    {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        if (isset($config['test']) && $config['test'] == 1) {
            if ($this->isMobile()) {
                $this->_url = $this->setCyberUrl(3);  //mobile test url
            } else {
                $this->_url = $this->setCyberUrl(1);  //standard test url
            }
        } else {
            if ($this->isMobile()) {
                $this->_url = $this->setCyberUrl(4); //mobile live url
            } else {
                $this->_url = $this->setCyberUrl(2); //standard live url
            }
        }
        //this is a standard web mobile payment
        if($this->isMobile()){
            if (isset($config['mobile_payment_action']) && $config['mobile_payment_action'] == 'sale') {
                //user selected Capture
                $config['payment_action'] = Cybersource_SOPWebMobile_Model_Source_MobilePaymentAction::ACTION_CAPTURE;
                if ($this->_mustUpdate()) {
                    //user selected Capture + Update Token
                    $config['payment_action'] = Cybersource_SOPWebMobile_Model_Source_MobilePaymentAction::ACTION_CAPTURE_UPDATE_TOKEN;
                }
            } else {
                //User selected Authorize
                $config['payment_action'] = Cybersource_SOPWebMobile_Model_Source_MobilePaymentAction::ACTION_AUTHORIZE;
                if ($this->_mustUpdate()) {
                    //User selected Authorize + Update Token
                    $config['payment_action'] = Cybersource_SOPWebMobile_Model_Source_MobilePaymentAction::ACTION_AUTHORIZE_UPDATE_TOKEN;
                }
            }
        }

        // Set payment action to save token (can only occur for new cards)
        if ($this->_mustSave()) {
            //user selected to save a token
            if ($this->isMobile()) { //Web / Mobile
                if ($config['mobile_payment_action'] == 'sale') {
                    //the user selected Authorize + Capture + Create Token
                    $config['payment_action'] = Cybersource_SOPWebMobile_Model_Source_MobilePaymentAction::ACTION_CAPTURE_CREATE_TOKEN;
                    //user wants to update token
                } else {
                    //the user selected Authorize + Create Token
                    $config['payment_action'] = Cybersource_SOPWebMobile_Model_Source_MobilePaymentAction::ACTION_AUTHORIZE_CREATE_TOKEN;
                }
            } else { //SOP
                if ($config['payment_action'] == 'authorize_capture') {
                    //the user selected Authorize + Capture + Create Token
                    $config['payment_action'] = 'authorize_capture_create_payment_token';
                } else {
                    //the user selected Authorize + Create Token
                    $config['payment_action'] = 'authorize_create_payment_token';
                }
            }
        }
        if($this->_useToken() && !$this->isMobile()){
            //does the billing address match that the user has entered?
            $this->_addressMatch = Mage::getModel('cybersourcesop/token')->matchAddress($this->_authoriseToken,$this->_order);

            if ($config['payment_action'] == 'authorize_capture') {
                //the user selected Authorize + Capture + Create Token
                if (!$this->_addressMatch) {
                    $config['payment_action'] = 'authorize_capture_update_payment_token';
                }
            } else {
                //the user selected Authorize + Create Token
                if (!$this->_addressMatch) {
                    $config['payment_action'] = 'authorize_update_payment_token';
                }
            }
        }
        //Customer billed in Default (website) currency, set currency code to DefaultCurrencyCode
        if (isset($config['default_currency']) && $config['default_currency'] == Cybersource_SOPWebMobile_Model_Source_Currency::DEFAULT_CURRENCY) {
            $currencyCode = $this->_order->getOrderCurrencyCode();
            $currencyCode=isset($currencyCode)?$currencyCode:$this->_order->getQuoteCurrencyCode();
        } else {
            //Customer billed in Base currency, set currency code to BaseCurrencyCode
            $currencyCode = $this->_order->getBaseCurrencyCode();
        }

        if (isset($config['merchant_access_key']) && isset($config['profile_id'])) {
            if (isset($config['mobile_enabled']) && $config['mobile_enabled'] == 1) {
                $this->addField('access_key', $config['mobile_merchant_access_key'],true)
                    ->addField('profile_id', $config['mobile_profile_id'],true)
                    ->addField('currency', $currencyCode,true)
                    ->addField('transaction_type', Cybersource_SOPWebMobile_Model_Source_Consts::getCyberPaymentAction($config['payment_action']));
            } else {
                $this->addField('access_key', $config['merchant_access_key'],true)
                    ->addField('profile_id', $config['profile_id'],true)
                    ->addField('currency', $currencyCode,true)
                    ->addField('transaction_type', Cybersource_SOPWebMobile_Model_Source_Consts::getCyberPaymentAction($config['payment_action']));
            }
        }
        //device fingerprint is enabled and is decision manager enabled? Add fingerprint ID
        if ($this->enableDM()) {
            if (isset($config['device_fingerprint_enabled'])) {
                $this->addField('device_fingerprint_id',Mage::getSingleton('customer/session')->getEncryptedSessionId());
            }
            if($config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::FRONTEND_ORDERS || $config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::ALL_ORDERS){
                $this->addField('skip_decision_manager', "false", true);
            }
        } else{
            $this->addField('skip_decision_manager', "true", true);
        }

        if(isset($config['forceavs']) && $config['forceavs'] != Cybersource_SOPWebMobile_Model_Source_Consts::CONFIG_CARDCHECK_DECLINE)
        {
            $this->addField('ignore_avs', "true",true);
        }

        if(isset($config['forcecvn']) && $config['forcecvn'] != Cybersource_SOPWebMobile_Model_Source_Consts::CONFIG_CARDCHECK_DECLINE)
        {
            $this->addField('ignore_cvn', "true",true);
        }

        //finally check that the signature has been set
        if (isset($config['mobile_enabled']) && $config['mobile_enabled'] == 1) {
            if(empty($config['mobile_merchant_secret_key']))
            {
                Throw new Exception('Secret Key invalid');
            }
        }else{
            if(empty($config['secret_key']))
            {
                Throw new Exception('Secret Key invalid');
            }
        }
    }

    /**
     *
     * Returns the decision manager system config field.
     *
     */
    public function getDMConfig() {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        return array_key_exists('dm_enabled', $config) && $config['dm_enabled'] ?  true : false;
    }

    /**
     *
     * Returns true / false if Decision Manager should be used for the payment.
     *
     */
    public function enableDM() {
        if ($this->getDMConfig()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *   Assign order field to the request
     */
    protected function assignOrderFields()
    {
        //do not include billing address when token is being used and user does not want to update it.
        if ((!$this->_useToken() && !$this->_mustUpdate()) || ($this->_useToken() && !$this->_addressMatch)) {
            $billingaddress = $this->getOrder()->getBillingAddress();
            $billingStreets = $billingaddress->getStreet();

            // Max character count for bill_to_company_name API request field is 40.
            $billToCompany = $billingaddress->getCompany();
            if(strlen($billToCompany) > 40){
                $billToCompany = substr($billToCompany,0,40);
            }
            $id = 1;
            foreach ($billingStreets as $street) {
                $this->addField('bill_to_address_line'.$id, $street,true);
                $id++;
            }
            $this->addField('bill_to_address_city', $billingaddress->getCity(),true)
                // Get region Code.
                ->addField('bill_to_address_state', $billingaddress->getRegionCode())
                ->addField('bill_to_forename', $billingaddress->getFirstname(),true)
                ->addField('bill_to_surname', $billingaddress->getLastname(),true)
                ->addField('bill_to_email', $this->getEmailAddress($this->getOrder()->getCustomerEmail()),true)
                ->addField('bill_to_phone', $this->cleanPhoneNum($billingaddress->getTelephone()),true)
                ->addField('bill_to_address_country', $billingaddress->getCountry(),true)
                ->addField('bill_to_address_postal_code', $billingaddress->getPostcode(),$this->getPostCodeMandatory($billingaddress->getCountry()))
                ->addField('bill_to_company_name', $billToCompany,false);
            //shipping info
            $shippingaddress = $this->getOrder()->getShippingAddress();
            if ($shippingaddress) {
                $shippingStreets = $shippingaddress->getStreet();
                // Max character count for ship_to_company_name API request field is 40.
                $shipToCompany = $shippingaddress->getCompany();

                if(strlen($shipToCompany) > 40){
                    $shipToCompany = substr($shipToCompany,0,40);
                }
                $id = 1;
                foreach ($shippingStreets as $shipStreet) {
                    $this->addField('ship_to_address_line'.$id, $shipStreet,true);
                    $id++;
                }
                $shipPostCode=  $shippingaddress->getPostcode();
                if(strlen($shipPostCode) > 10){
                    $shipPostCode = substr($shipPostCode,0,10);
                }

                $this->addField('ship_to_address_city', $shippingaddress->getCity(),true)
                    // Get region Code.
                    ->addField('ship_to_address_state', $shippingaddress->getRegionCode())
                    ->addField('ship_to_forename', $shippingaddress->getFirstname(),true)
                    ->addField('ship_to_surname', $shippingaddress->getLastname(),true)
                    ->addField('ship_to_email', $this->getEmailAddress($this->getOrder()->getCustomerEmail()),true)
                    ->addField('ship_to_phone', $this->cleanPhoneNum($shippingaddress->getTelephone()),true)
                    ->addField('ship_to_address_country', $shippingaddress->getCountry(),true)
                    ->addField('ship_to_address_postal_code',$shipPostCode ,$this->getPostCodeMandatory($shippingaddress->getCountry()))
                    ->addField('ship_to_company_name', $shipToCompany,false);
            }
        }
        //add order information
        $this->addField('reference_number', $this->getOrder()->getIncrementId(),true)
            ->addField('returns_accepted', 'true',true)
            ->addField('transaction_uuid', $this->getUuid($this->getOrder()->getIncrementId()),true);
        if ($this->_mustUpdate()) {
            $this->addField('allow_payment_token_update', 'true',true);
        }
    }

    /**
     * Checks if the postcode is mandatory for the country. It returns true if the postcode is
     * required and false otherwise.
     * @param  string $country Country code
     * @return bool
     */
    protected function getPostCodeMandatory($country){
        $postalRequiredCountries = Cybersource_SOPWebMobile_Model_Source_Consts::getPostCodeRequiredCountries();
        if(isset($country) && in_array(strtoupper($country),$postalRequiredCountries)){
            return true;
        }
        return false;
    }


    /**
     *  Add order line items to the request
     */
    protected function assignLineItems() {
        //get config
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);

        $items = $this->_order->getAllVisibleItems();
        $i = 0;
        foreach ($items as $item) {
            $this->addField('item_'.$i.'_code',$item->getProductId(),true);
            $this->addField('item_'.$i.'_name',$item->getName(),true);
                $this->addField('item_'.$i.'_quantity',number_format($item->getQty(), 0, '.', ''),true);
            $this->addField('item_'.$i.'_sku',$item->getSku(),true);
            $this->addField('item_'.$i.'_tax_amount',number_format($item->getTaxAmount(), 2, '.', ''),true);

            //Customer billed in Default (website) currency, set unitPrice to  price
            if (isset($config['default_currency']) && $config['default_currency'] == Cybersource_SOPWebMobile_Model_Source_Currency::DEFAULT_CURRENCY) {
                $this->addField('item_'.$i.'_unit_price',number_format($item->getPrice(), 2, '.', ''),true);
            } else {
                //Customer billed in Base currency, set unitPrice to basePrice
                $this->addField('item_'.$i.'_unit_price',number_format($item->getBasePrice(), 2, '.', ''),true);
            }
            $i++;
        }
        $this->addField('line_item_count',$i, true);
        if($this->getOrder()){
            $taxAmount = $this->getOrder()->getShippingAddress()->getData('tax_amount');
            $this->addField('tax_amount',number_format($taxAmount, 2, '.', ''),true);
        }
    }

    /**
     * Generates a unique reference for each transaction: transaction_uuid
     * @param string $orderidin
     * @return string
     */

    private function getUuid($orderidin)
    {
        return $orderidin.time();

    }

    /**
     * Add the payment fields
     *
     */
    protected function addPaymentFields()
    {
        //Get config
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        //Customer billed in Default (website) currency, set $totals to GrandTotal
        if (isset($config['default_currency']) && $config['default_currency'] == Cybersource_SOPWebMobile_Model_Source_Currency::DEFAULT_CURRENCY) {
            $totals = number_format($this->getOrder()->getGrandTotal(), 2, '.', '');
        } else {
            //Customer billed in Base currency, set $totals to BaseGrandTotal
            $totals = number_format($this->getOrder()->getBaseGrandTotal(), 2, '.', '');
        }
        $this->addField('amount', $totals,true);

        if (!$this->isOneStepEnabled()) {
            $crypt = $this->_getEncryptor();
        }
        //Token is being used - only authorize CVN and use Token
        if ($this->_useToken()) {
            //add payment token to request
            $this->addField('payment_token',$this->_authoriseToken);
            //always add cvn to request.
            if(Cybersource_SOPWebMobile_Model_Source_Consts::useCvn($this->_storeId))
            {
                $this->addField('card_cvn', base64_decode($crypt->decrypt(Mage::app()->getRequest()->getParam('rpdzzimldboiecl'))),true);
            }
        } else {
            //No Token being used, process payment normally
            if ((isset($config['mobile_enabled']) && $config['mobile_enabled'] == 1)) {
                //this is a mobile user paying
                $this->addField('card_number', ' ',true)
                    ->addField('card_type', ' ',true)
                    ->addField('card_cvn', ' ',true)
                    ->addField('card_expiry_date', ' ', true);
            } else {
                if ($this->isOneStepEnabled()) {
                    $this->addField('card_number', '0000',true)
                        ->addField('card_type', '0000',true)
                        ->addField('card_cvn', '0000',true)
                        ->addField('card_expiry_date', '0000', true);
                } else {
                    //reattach the expiry values
                    $expmonth = base64_decode($crypt->decrypt(Mage::app()->getRequest()->getParam('ftkaueypxwhqkki')));
                    $expyear = base64_decode($crypt->decrypt(Mage::app()->getRequest()->getParam('zqbgnxelpjrerpi')));
                    $expiry = $expmonth . "-".$expyear;
                    //add encrypted values to field
                    $this->addField('card_number', base64_decode($crypt->decrypt(Mage::app()->getRequest()->getParam('kigqyhjuhkfgjvb'))),true)
                        ->addField('card_type', base64_decode($crypt->decrypt(Mage::app()->getRequest()->getParam('kuklglvtziycxss'))),true)
                        ->addField('card_expiry_date', $expiry, true);
                    //always add cvn to request.
                    if(Cybersource_SOPWebMobile_Model_Source_Consts::useCvn($this->_storeId))
                    {
                        $this->addField('card_cvn', base64_decode($crypt->decrypt(Mage::app()->getRequest()->getParam('rpdzzimldboiecl'))),true);
                    }
                }

            }
        }
    }

    /**
     * Retrieve and format the email address
     *
     * @param string $emailin
     * @return string
     */
    public function getEmailAddress($emailin)
    {
        if($emailin)
        {
            return $emailin;
        }else
        {
            return Mage::getStoreConfig('trans_email/ident_general/email');
        }
    }

    /**
     * Clean the phone number, cybersource is very picky about the phone number validation
     *
     * @param string $phoneNumberIn
     * @return string|mixed
     */
    protected function cleanPhoneNum($phoneNumberIn)
    {

        $filtered = preg_replace("/[^0-9]/","",$phoneNumberIn);

        if(strlen($filtered) < 6)
        {

            return '000000000';

        }else
        {

            return $filtered;
        }
    }

    /**
     * Get the order details of the
     *
     * @return boolean
     */
    protected function getOrder()
    {
        if($this->_order)
        {
            return $this->_order;
        }else
        {
            return false;
        }

    }

    /**
     * Checks if Cybersource_Onestepcheckout module is enabled in system configurations
     * @return bool
     */
    private function isOneStepEnabled() {
        //check if module is present and is active
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        if (Mage::getConfig()->getNode('modules/Cybersource_Onestepcheckout') && Mage::getConfig()->getModuleConfig('Cybersource_Onestepcheckout')->is('active', 'true')) {
            //check if module is enabled
            if (isset($config['onestep_enabled']) && $config['onestep_enabled']) {

                //check if module is enabled in system configurations
                return true;
            }
        }
        return false;
    }

    /**
     * Sets _order attribute.
     * @return Mage_Sale_Model_Order
     */
    private function setOrder()
    {
        $this->setStoreId(Mage::app()->getStore()->getId());
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();
        $quote->reserveOrderId();
        $quote->setIncrementId($quote->getReservedOrderId())->save();
        $this->_order = $quote;
        return $this->_order;
    }

    /**
     *
     * Checks if the field is required and throws an exception if the value is empty or null.
     * @param string $fieldname
     * @param mixed $value
     * @param bool $required
     * @return $this
     * @throws Exception
     */
    protected function addField($fieldname, $value,$required=false)
    {
        //check if value is required
        if($required == true && empty($value))
        {
            Throw new Exception('Required setting missing: '.$fieldname);

        }else{

            if(!empty($value))
            {

                $this->_fields[$fieldname] = $value;

            }
        }
        return $this;
    }

    /**
     * Returns cybersource url of the selected payment mode.
     * @param int $configvaluein
     * @return string
     */
    protected function setCyberUrl($configvaluein)
    {
        $returnValue = '';
        switch($configvaluein) {
            case 1 : $returnValue = Cybersource_SOPWebMobile_Model_Source_Consts::TESTURL;
                break;
            case 2 : $returnValue =  Cybersource_SOPWebMobile_Model_Source_Consts::LIVEURL;
                break;
            case 3 : $returnValue = Cybersource_SOPWebMobile_Model_Source_Consts::MOBILE_TESTURL;
                break;
            case 4 :$returnValue = Cybersource_SOPWebMobile_Model_Source_Consts::MOBILE_LIVEURL;
                break;
        }
        return $returnValue;
    }

    /**
     *
     * Returns cybersource url.
     * @return string
     */
    public function getCyberUrl()
    {
        return $this->_url;
    }

    /**
     * Returns the encryption model
     * @return Cybersource_SOPWebMobile_Model_Security_Encryption
     */
    private function _getEncryptor()
    {
        if($this->_encryptionmodel == null)
        {
            $this->_encryptionmodel = Mage::getModel('cybersourcesop/security_encryption')->setCryptWithKey(null,false,true);
        }
        return $this->_encryptionmodel;
    }


    /**
     * Checks if the token should be saved
     * @return bool
     */
    public function _mustSave() {
        //we pass the value of 1 when a checkbox is selected.
        $tokenChecked = Mage::app()->getRequest()->getParam('rqowieufrhgnmzx');
        if ($tokenChecked) {
            $crypt = $this->_getEncryptor();
            $tokenChecked = base64_decode($crypt->decrypt($tokenChecked,true));
        }
        if (1 == $tokenChecked) {
            return true;
        }
        return false;
    }


    /**
     * Checks if the token should be updated
     * @return bool
     */
    public function _mustUpdate() {
        //we pass the value of 1 when a checkbox is selected.
        $updateChecked = Mage::app()->getRequest()->getParam('vneifvnasdkjfnwi');
        if ($updateChecked) {
            $crypt = $this->_getEncryptor();
            $updateChecked = base64_decode($crypt->decrypt($updateChecked,true));
        }
        if (1 == $updateChecked) {
            return true;
        }
        return false;
    }

    //authorise using token
    /**
     * Decrypt the payment token to be used in the transaction.
     * It returns true if the token was decrypted successfully. An exception will
     * be thrown otherwise.
     * @return bool
     * @throws Exception
     */
    public function _useToken() {
        $token = Mage::app()->getRequest()->getParam('zswaqecdrytgbvh');
        if ($token) {
            //decrypt the payment token to be used in the transaction for future use.
            $crypt = $this->_getEncryptor();
            $tokenId = base64_decode($crypt->decrypt($token),true);
            if ($tokenId) {
                $this->_authoriseToken = $tokenId;
            } else {
                Throw new Exception('Could not decrypt token ID');
            }

            return true;
        }
        return false;
    }

    /**
     * Adds Merchant Defined field to the request
     */
    protected function addMerchantDefinedFields()
    {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        $order = $this->getOrder();

        if($this->enableDM() && ($config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::FRONTEND_ORDERS || $config['dm_orders'] === Cybersource_SOPWebMobile_Model_Source_Consts::ALL_ORDERS)){
            if($config['mdd_enabled']){

                $myvals = $this->getMerchantDefinedFields();

                foreach($myvals as $array => $value){
                    //If field is set to none or is empty, continue.
                    if(empty($order[$value['value']]) || $order[$value['value']] === '-- none --'){
                        continue;
                    }else{
                        //Add Merchant Defined field to request.
                        $this->addField('merchant_defined_data'.$value['mdd'],$order[$value['value']], true);
                    }
                }
            }
        }

    }

    /**
     * Returns true if Merchant Defined Fields is enabled in system config and false otherwise.
     * @return bool
     */
    protected function getMDDConfig() {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        return $config['mdd_enabled'] ? true : false;
    }

    /**
     * Returns true if Merchant Defined Fields is enabled and false otherwise.
     * @return bool
     */
    public function enableMDD() {
        if ($this->getMDDConfig()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns an array of Merchant Defined Fields if Merchant Defined Fields is enabled, otherwise false is returned.
     * @return bool|mixed
     */
    protected function getMerchantDefinedFields() {
        if($this->enableMDD()) {
            $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
            return unserialize($config['mdd_fields']);
        } else {
            return false;
        }
    }

    /**
     * Adds Merchant Defined Fields to the request.
     */
    protected function addAdditionalMerchantDefinedFields() {
        if ($this->enableMDD()) {
            $fields = $this->getAdditionalFields();
            if ($fields) {
                foreach ($fields as $key => $value) {
                    $this->addField('merchant_defined_data'.$key,$value,true);
                }
            }
        }
    }
    /**
     *  Returns an array of additional fields
     * @return array
     */
    public function getAdditionalFields() {
        $return = array();
        return $return ;
    }
}
