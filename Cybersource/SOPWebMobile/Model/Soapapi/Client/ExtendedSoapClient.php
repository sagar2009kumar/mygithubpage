<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Soapapi_Client_ExtendedSoapClient extends SoapClient
{
    /**
     * Store ID
     * @access public
     * @var string
     */
    public $_storeId = null;

    /**
     * Main constructor
     * @param string $wsdl <p>WSDL URL</p>
     * @param array $options <p>The URL to request.</p>
     * @param string $storeId
     *
     */
    public function __construct($wsdl, $options = array(), $storeId = null)
    {
        //Sets store id for crons
        if($storeId){
            $this->_storeId = $storeId;
        }
        parent::__construct($wsdl, $options);
    }

    /**
     * Sends request to cybersource
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $oneWay
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $oneWay = 0)
    {
        $storeId = Mage::getModel('core/session')->getSoapRequestStoreId();

        if(!$storeId){
            $storeId = $this->_storeId;
        }

        //as you can specify multiple merchant IDs / SOAP Keys for different stores -
        //for admin refunds / auths / captures / orders each store view can have multiple soap keys / merchant IDs for different stores.
        //in order to introduce store scope, we pass the store ID in the abstract class which is taken from the payment that was made on a
        //specific store view.
        $user = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('merchant_id',$storeId);
        $password = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('soapkey',$storeId);
        $soapHeader = "<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>$user</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">$password</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>";

        $requestDOM = new DOMDocument('1.0');
        $soapHeaderDOM = new DOMDocument('1.0');
        $requestDOM->loadXML($request);
        $soapHeaderDOM->loadXML($soapHeader);

        $node = $requestDOM->importNode($soapHeaderDOM->firstChild, true);
        $requestDOM->firstChild->insertBefore(
        $node, $requestDOM->firstChild->firstChild);
        $request = $requestDOM->saveXML();
        $response = parent::__doRequest($request, $location, $action, $version, $oneWay);
        return $response;
    }
}
