<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Token extends Mage_Core_Model_Abstract
{
    /**
     * Set to tru if the response from cybersource has to be logged.
     * @access public
     */
    public $_logResponse = null;
    /**
     * Holds system config values.
     * @access public
     * @var array
     */
    public $_config = null;
    /**
     * Cybersource URL
     * @access public
     * @var string
     */
    public $_url = null;
    /**
     * Request parameters
     * @access public
     * @var array
     */
    public $_params = null;
    /**
     * Request fields
     * @access public
     * @var array
     */
    public $_fields = null;
    /**
     * Customer token
     * @access public
     * @var string
     */
    public $_tokenID = null;
    /**
     * Store Id
     * @access public
     * @var string|int
     */
    public $_storeId = null;
    /**
     * Generated merchant reference
     * @access public
     */
    public $_merchantRef = null;
    /**
     * Old customer's token. Used when updating tokenID.
     * @access public
     */
    public $_oldTokenID = null;

    /**
     * Getting Soap Api object
     *
     * @param   array $options
     * @return  Mage_Cybersource_Model_Api_ExtendedSoapClient
     */
    protected function getSoapApi($options = array())
    {
        $wsdlURL = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('test', $this->_storeId) ? Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('wsdl_test_url', $this->_storeId) : Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('wsdl_live_url', $this->_storeId);
        if (strlen($wsdlURL) > 10) {
            $wsdl = $wsdlURL;
        } else {
            $wsdl = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('test', $this->_storeId) ? Cybersource_SOPWebMobile_Model_Source_Consts::WSDL_URL_TEST : Cybersource_SOPWebMobile_Model_Source_Consts::WSDL_URL_LIVE;
        }
        $_api = new Cybersource_SOPWebMobile_Model_Soapapi_Client_ExtendedSoapClient($wsdl, $options, $this->_storeId);
        return $_api;
    }

    /**
     * Main constructor
     */
    protected function _construct()
    {
        $this->_init('cybersourcesop/token');
        $this->getConfig();
    }

    /**
     * Loads payment system config
     */
    protected function getConfig()
    {
        if (!$this->_storeId) {
            $this->_storeId = Mage::app()->getStore()->getStoreId();
        }
        $this->_config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null, $this->_storeId);
    }

    /**
     * Generates token delete request
     * @param $tokenId
     * @param $merchantRef
     * @return array|bool|string
     */
    public function createDeleteRequest($tokenId, $merchantRef)
    {
        $this->_merchantRef = $merchantRef;

        $request = new stdClass();
        $request->merchantID = $this->_config['merchant_id'];
        $request->merchantReferenceCode = $this->_generateReferenceCode();

        $recurringSubscriptionInfo = new stdClass();
        $recurringSubscriptionInfo->subscriptionID = $tokenId;
        $request->recurringSubscriptionInfo = $recurringSubscriptionInfo;

        $paySubscriptionDelete = new stdClass();
        $paySubscriptionDelete->run = 'true';
        $request->paySubscriptionDeleteService = $paySubscriptionDelete;

        $this->_params = $request;

        return $this->callCyber();
    }

    /**
     * Generates update token request
     * @param $tokenId
     * @param $merchantRef
     * @param null $updateData
     * @return array|bool|string
     */
    public function updateTokenRequest($tokenId, $merchantRef, $updateData = null)
    {
        $this->_merchantRef = $merchantRef;
        $this->_oldTokenID = $tokenId;

        $request = new stdClass();
        $request->merchantID = $this->_config['merchant_id'];
        $request->merchantReferenceCode = $this->_generateReferenceCode();

        $card = new stdClass();
        $card->accountNumber = $updateData['cc_number'];
        $card->expirationMonth = $updateData['cc_expiration_month'];
        $card->expirationYear = $updateData['cc_expiration_year'];
        $card->cardType = $updateData['cc_type'];
        $request->card = $card;

        $recurringSubscriptionInfo = new stdClass();
        $recurringSubscriptionInfo->subscriptionID = $tokenId;
        $request->recurringSubscriptionInfo = $recurringSubscriptionInfo;

        $paySubscriptionUpdate = new stdClass();
        $paySubscriptionUpdate->run = 'true';
        $request->paySubscriptionUpdateService = $paySubscriptionUpdate;

        $this->_params = $request;

        $output = $this->callCyber();
        if (is_string($output)) {

            $this->load($output, 'token_id');
            $newTokenID = $output;

            if ($this->getData() && $newTokenID) {

                $strLength = strlen($updateData['cc_number']);
                $lastFour = str_repeat('x', $strLength - 4) . substr($updateData['cc_number'], $strLength - 4, 4);

                $this->setTokenId($newTokenID)
                    ->setCcNumber($lastFour)
                    ->setCcExpiration($updateData['cc_expiration_month'] . '-' . $updateData['cc_expiration_year'])
                    ->setCcType($updateData['cc_type'])
                    ->save();
            }
        }
        return $output;
    }

    /**
     * Returns generated merchant reference code
     * @return string
     */
    protected function _generateReferenceCode()
    {
        //else use unique hash
        if (is_null($this->_merchantRef)) {
            return Mage::helper('core')->uniqHash();
        }
        return $this->_merchantRef;
    }

    /**
     *  Creates Convert order  cybersource request
     * @param $requestId
     * @param $orderId
     * @param $storeId
     * @return array|bool|string
     */
    public function createConvertOrderRequest($requestId, $orderId, $storeId)
    {
        $this->_storeId = $storeId;
        $this->getConfig();
        $config = $this->_config;
        $request = new stdClass();
        $request->merchantID = $config['merchant_id'];
        $request->merchantReferenceCode = $orderId;

        $paySubscription = new stdClass();
        $paySubscription->run = 'true';
        $paySubscription->paymentRequestID = $requestId;
        $request->paySubscriptionCreateService = $paySubscription;

        $subInfo = new stdClass();
        $subInfo->frequency = 'on-demand';
        $request->recurringSubscriptionInfo = $subInfo;

        $this->_params = $request;
        $tokenID = $this->callCyber();
        return $tokenID;
    }

    /**
     * Returns a collection of tokens
     * @return mixed
     */
    protected function getTokens()
    {
        return $this->getCollection();
    }

    /**
     * Loads token by id
     * @param $id
     * @return Cybersource_SOPWebMobile_Model_Token
     */
    public function getTokenValue($id)
    {
        $token = $this->load($id);
        return $token;
    }

    /**
     * Sends the request to cybersource
     * @return array|bool|string
     */
    protected function callCyber()
    {
        $soapClient = $this->getSoapApi();
        $tokenId = (string)$this->_params->recurringSubscriptionInfo->subscriptionID;
        $result = $soapClient->runTransaction($this->_params);
        if (isset($result->paySubscriptionCreateReply)) {
            if ((int)$result->paySubscriptionCreateReply->reasonCode == Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_ACCEPT) {
                $this->_tokenID = (string)$result->paySubscriptionCreateReply->subscriptionID;
            } else {
                return false;
            }
        } elseif (isset($result->paySubscriptionDeleteReply)) {
            if ((int)$result->paySubscriptionDeleteReply->reasonCode == Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_ACCEPT) {
                $this->load($tokenId, 'token_id');
                if ($this->getData()) {
                    //Delete Token from Magento
                    $this->delete();
                }
                return true;
            } else {
                return false;
            }
        } elseif (isset($result->paySubscriptionUpdateReply)) {
            if ((int)$result->paySubscriptionUpdateReply->reasonCode == Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_ACCEPT) {
                //Update the token:
                if (isset($result->paySubscriptionUpdateReply->subscriptionIDNew)) {
                    $this->_tokenID = (string)$result->paySubscriptionUpdateReply->subscriptionIDNew;
                } else {
                    $this->_tokenID = (string)$result->paySubscriptionUpdateReply->subscriptionID;
                }

            } else {
                $response = array((int)$result->paySubscriptionUpdateReply->reasonCode, isset($result->invalidField) ? (string)$result->invalidField : "");
                return $response;
            }
        } else {
            return false;
        }

        return $this->_tokenID;
    }


    /**
     * returns the Token ID from an ACCEPT response or false
     * @param Variant_Object $xml
     * @return bool
     */
    protected function getTokenFromRequest($xml)
    {
        if ($xml) {
            if ($xml->decision == Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_ACCEPT) {
                return $xml->paySubscriptionCreateReply->subscriptionID;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * core iterator callback method to set all customer default tokens to nothing.
     * @param array $args
     */
    public function saveDefaultTokenCallback($args)
    {
        $defaultToken = Mage::getModel('cybersourcesop/token');
        $defaultToken->setData($args['row']);
        $defaultToken->setIsDefault(0);
        $defaultToken->save();
    }

    /**
     * Sets the default token
     * @param $token_id
     * @return $this|string
     */
    public function setAsDefault($token_id)
    {

        $this->load($token_id);
        $customer_id = $this->getCustomerId();
        $defaultToken = Mage::getModel('cybersourcesop/token')->getCollection()
            ->addFieldToFilter('customer_id', $customer_id);

        if (count($defaultToken) > 1) {
            Mage::getSingleton('core/resource_iterator')->walk($defaultToken->getSelect(), array(array($this, 'saveDefaultTokenCallback')));
        } else {
            $defaultToken->setIsDefault(0)->save();
        }
        //clear out all other tokens assigned to customer and set their default to null

        //save it as the default last.
        try {
            $this->setIsDefault(1)->save();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $this;
    }

    /**
     * Loads default token for the customer
     * @param string $customer_id
     * @return mixed
     */
    public function getDefaultToken($customer_id)
    {
        $collection = $this->getResourceCollection()->addCustomerFilter($customer_id)->getDefault()->setPageSize(1);
        return $collection()->getFirstItem()->getTokenId();
    }

    /**
     * Compares the address provided by the customer with ones associated with the token.
     * @param string $tokenId
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function matchAddress($tokenId, $order)
    {
        $token = $this->load($tokenId, 'token_id');
        $orderId = $token->getMerchantRef();
        $soapClient = $this->getSoapApi();
        $this->getConfig();
        $config = $this->_config;
        $request = new stdClass();
        $request->merchantID = $config['merchant_id'];
        $request->merchantReferenceCode = $orderId;

        $paySubscription = new stdClass();
        $paySubscription->run = 'true';
        $request->paySubscriptionRetrieveService = $paySubscription;

        $recurringSubscriptionInfo = new stdClass();
        $recurringSubscriptionInfo->subscriptionID = $tokenId;
        $request->recurringSubscriptionInfo = $recurringSubscriptionInfo;

        $this->_params = $request;
        try {
            $result = $soapClient->runTransaction($this->_params);
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('cybersourcesop')->__('SOAP request error: %s', $e->getMessage())
            );


        }

        if (isset($result->reasonCode) && (int)$result->reasonCode == Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_ACCEPT) {
            if (isset($result->paySubscriptionRetrieveReply)) {

                $subscriptionRetrieve = $result->paySubscriptionRetrieveReply;

                //result must be a valid address
                $billingAddress = $order->getBillingAddress();
                $shippingAddress = $order->getShippingAddress();

                $stateNotApplicable = 'N/A';

                $billingAddressState = '';
                $region = Mage::getModel('directory/region')->loadByName($billingAddress->getRegion(), $billingAddress->getCountryId());
                if ($region->getData()) {
                    $billingAddressState = (strlen($region->getCode()) > 2 ? substr($region->getCode(), 0, 2) : $region->getCode()); // The length of the region code has to be 2.
                }

                $region = Mage::getModel('directory/region')->loadByName($shippingAddress->getRegion(), $shippingAddress->getCountryId());
                $shippingAddressState = '';
                if ($region->getData()) {
                    $shippingAddressState = (strlen($region->getCode()) > 2 ? substr($region->getCode(), 0, 2) : $region->getCode()); // The length of the region code has to be 2.
                }

                $street = isset($subscriptionRetrieve->street1) ? (string)$subscriptionRetrieve->street1 : "";
                $city = isset($subscriptionRetrieve->city) ? (string)$subscriptionRetrieve->city : "";
                $country = isset($subscriptionRetrieve->country) ? (string)$subscriptionRetrieve->country : "";
                $state = isset($subscriptionRetrieve->state) ? (string)$subscriptionRetrieve->state : "";
                $postalCode = isset($subscriptionRetrieve->postalCode) ? (string)$subscriptionRetrieve->postalCode : "";
                $phoneNumber = isset($subscriptionRetrieve->phoneNumber) ? (string)$subscriptionRetrieve->phoneNumber : "";

                $shipToStreet = isset($subscriptionRetrieve->shipToStreet1) ? (string)$subscriptionRetrieve->shipToStreet1 : "";
                $shipToCity = isset($subscriptionRetrieve->shipToCity) ? (string)$subscriptionRetrieve->shipToCity : "";
                $shipToCountry = isset($subscriptionRetrieve->shipToCountry) ? (string)$subscriptionRetrieve->shipToCountry : "";
                $shipToPostalCode = isset($subscriptionRetrieve->shipToPostalCode) ? (string)$subscriptionRetrieve->shipToPostalCode : "";
                $shipToState = isset($subscriptionRetrieve->shipToState) ? (string)$subscriptionRetrieve->shipToState : "";

                // Check Billing Address of token against Billing address in Order.
                if (isset($street) && $street != $billingAddress->getStreet(0)) {
                    return false;
                }
                if (isset($city) && $city != $billingAddress->getCity()) {
                    return false;
                }
                if (isset($country) && $country != $billingAddress->getCountry()) {
                    return false;
                }
                if (isset($state) && $state != $billingAddressState) {
                    if ($state != $stateNotApplicable) {
                        return false;
                    }
                }
                if (isset($postalCode) && $postalCode != $billingAddress->getPostcode()) {
                    return false;
                }
                if (isset($phoneNumber) && $phoneNumber != $billingAddress->getTelephone()) {
                    return false;
                }
                // Check Shipping Address of token against Shipping address in Order.
                if (isset($shipToStreet) && $shipToStreet != $shippingAddress->getStreet(0)) {
                    return false;
                }
                if (isset($shipToCity) && $shipToCity != $shippingAddress->getCity()) {
                    return false;

                }
                if (isset($shipToCountry) && $shipToCountry != $shippingAddress->getCountry()) {
                    return false;
                }
                if (isset($shipToPostalCode) && $shipToPostalCode != $shippingAddress->getPostcode()) {
                    return false;
                }
                if (isset($shipToState) && $shipToState != $shippingAddressState) {
                    if ($subscriptionRetrieve->state != $stateNotApplicable) {
                        return false;
                    }
                }
            }
        }
        // Should only return true if the addresses are the same, and the token will not be updated,
        return true;
    }

}
