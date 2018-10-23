<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Soapapi_Token extends Cybersource_SOPWebMobile_Model_Soapapi_Abstract
{

    /**
     *
     * Retrieved payment transaction information
     * @access protected
     * @var Varien_Object
     */
    protected $paySubscriptionRetrieveReply;

    /**
     * Retrieve the payment transaction information
     *
     * @param Cybersource_SOPWebMobile_Model_Resource_Token::_importRow() return $csvRow
     * @return Mage_Cybersource_Model_Soap
     */
    public function getCyberToken($csvRow)
    {
        $error = false;

        //set params to retrieve customer profile
        $this->iniRequest();
        //check if merchantID is retrieved from abstract class
        if ($this->_request->merchantID) {
            //only add tokens from the same merchant ID.
            if ($this->_request->merchantID == $csvRow['merchant_ID']) {
                $this->_request->merchantID = $csvRow['merchant_ID'];
            } else {
                Mage::throwException(
                    Mage::helper('cybersourcesop')->__('Invalid Merchant ID specified within CSV File.')
                );
            }
        }
        //build request
        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $csvRow['purchaseTotals_currency'];

        $recurringSubscriptionInfo = new stdClass();
        $recurringSubscriptionInfo->subscriptionID = $csvRow['recurringSubscriptionInfo_subscriptionID'];

        $paySubscriptionRetrieveService = new stdClass();
        $paySubscriptionRetrieveService->run = $csvRow['paySubscriptionRetrieveService_run'];

        $this->_request->paySubscriptionRetrieveService = $paySubscriptionRetrieveService;
        $this->_request->recurringSubscriptionInfo = $recurringSubscriptionInfo;
        $this->_request->purchaseTotals = $purchaseTotals;

        //replace merchantReferenceCode
        $this->_request->merchantReferenceCode = $csvRow['merchantReferenceCode'];

        $soapClient = $this->getSoapApi();

        try {
			echo "<pre>"; print_r($this->_request); die;
            $result = $soapClient->runTransaction($this->_request);
            if ($result->reasonCode==Cybersource_SOPWebMobile_Model_Source_Consts::SOAP_SUCCESS) {
                $success= $result->paySubscriptionRetrieveReply;
            } else {
                $error = Mage::helper('cybersourcesop')->__('There is an error in retrieving a token for token ID: %s', $csvRow['recurringSubscriptionInfo_subscriptionID']);
            }
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('cybersourcesop')->__('Cybersource Gateway request error: %s', $e->getMessage())
            );

        }

        if ($error !== false) {
            Mage::throwException($error);
        }
        //return the customer's record.
        return $success;
    }
}
