<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Soapapi_Capture extends Cybersource_SOPWebMobile_Model_Soapapi_Order
{

	/**
	 * Refund the payment transaction
	 *
	 * @param Mage_Sale_Model_Order_Payment $payment
	 * @param flaot $amount
     * @param mixed $orderref
	 * @return Mage_Cybersource_Model_Soap
	 */
	public function process(Varien_Object $payment, $amount, $orderref = null)
	{
        //get Config
        $this->_storeId = $payment->getOrder()->getStoreId();
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);

        $error = false;
        $soapClient = $this->getSoapApi();
        $this->assignOrderRef($orderref);

        $this->iniRequest($payment);

        $this->addDecisionManager($orderref);
        $this->addMerchantDefinedFields($orderref);

        //Customer billed in Default (website) currency, set currency code to OrderCurrencyCode and amount to GrandTotal
        if (isset($config['default_currency']) && $config['default_currency'] == Cybersource_SOPWebMobile_Model_Source_Currency::DEFAULT_CURRENCY) {
            $currency = $payment->getOrder()->getOrderCurrencyCode();
            //Get default grandTotal to capture from invoice.
            $invoices = $payment->getOrder()->getInvoiceCollection();
            foreach ($invoices as $invoice) {
                if ($invoice->getRequestedCaptureCase() == Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE) {
                    $amount = number_format($invoice->getGrandTotal(), 2, '.', '');
                    continue;
                }
            }
        } else {
            //Customer billed in Base currency, set currency code to BaseCurrencyCode. $amount is by default the BaseGrandTotal.
            $currency = $payment->getOrder()->getBaseCurrencyCode();
        }

        if ($payment->getParentTransactionId() && $payment->getCybersourceToken()) {
            $ccCaptureService = new stdClass();
            $ccCaptureService->run = "true";
            $ccCaptureService->authRequestToken = $payment->getCybersourceToken();
            $ccCaptureService->authRequestID = $payment->getParentTransactionId();
            $this->_request->ccCaptureService = $ccCaptureService;

            $item0 = new stdClass();
            $item0->unitPrice = $amount;
            $item0->id = 0;
            $this->_request->item = array($item0);

        } else if ($payment->getLastTransId()) {
            $ccCaptureService = new stdClass();
            $ccCaptureService->run = "true";
            $ccCaptureService->authRequestID = $payment->getLastTransId();
            $this->_request->ccCaptureService = $ccCaptureService;

            $this->addBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
            $this->addShippingAddress($payment->getOrder()->getShippingAddress());
            $this->addLineItemInfo($payment->getOrder());

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $currency;
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;

        }else{

            $ccAuthService = new stdClass();
            $ccAuthService->run = "true";
            $this->_request->ccAuthService = $ccAuthService;

            $ccCaptureService = new stdClass();
            $ccCaptureService->run = "true";
            $this->_request->ccCaptureService = $ccCaptureService;

            $this->addBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
            $this->addShippingAddress($payment->getOrder()->getShippingAddress());
            $this->addCcInfo($payment);
            $this->addLineItemInfo($payment->getOrder());

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $currency;
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;

            //add business rules for auth and capture
            $businessRules = new stdClass();

            if(isset($config['forceavs'])){
                if($config['forceavs'] === (string)Cybersource_SOPWebMobile_Model_Source_Consts::CONFIG_CARDCHECK_DECLINE){
                    $businessRules->ignoreAVSResult = Cybersource_SOPWebMobile_Model_Source_Consts::IGNORE_RULE_FALSE;
                }else{
                    $businessRules->ignoreAVSResult = Cybersource_SOPWebMobile_Model_Source_Consts::IGNORE_RULE_TRUE;
                }
            }
            if(isset($config['forcecvn'])){
                if($config['forcecvn'] === (string)Cybersource_SOPWebMobile_Model_Source_Consts::CONFIG_CARDCHECK_DECLINE){
                    $businessRules->ignoreCVResult =  Cybersource_SOPWebMobile_Model_Source_Consts::IGNORE_RULE_FALSE;
                }else{
                    $businessRules->ignoreCVResult = Cybersource_SOPWebMobile_Model_Source_Consts::IGNORE_RULE_TRUE;
                }
            }
            $this->_request->businessRules = $businessRules;

        }
        try {
            $result = $soapClient->runTransaction($this->_request);

            if ($result->reasonCode==Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_ACCEPT) {

                    /*
                    for multiple capture we need to use the latest capture transaction id
                    */
                $payment->setCcApproval(true)
                    ->setLastTransId($result->requestID)
                    ->setLastCybersourceToken($result->requestToken)
                    ->setCcTransId($result->requestID)
                    ->setTransactionId($result->requestID)
                    ->setIsTransactionClosed(0)
                    ->setCybersourceToken($result->requestToken)
                    ->setAdditionalData(serialize($result));

                    if(isset($result->ccAuthReply->avsCode)){
                        $payment->setCcAvsStatus($result->ccAuthReply->avsCode);
                    }

                	if (isset($result->ccAuthReply->cvCode)) {
                		$payment->setCcCidStatus($result->ccAuthReply->cvCode);
                	}

            }else if($result->reasonCode == Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_DM_REVIEW){

                $payment->setLastTransId($result->requestID)
                    ->setLastCybersourceToken($result->requestToken)
                    ->setCcTransId($result->requestID)
                    ->setTransactionId($result->requestID)
                    ->setIsTransactionClosed(0)
                    ->setIsTransactionPending(1)
                    ->setIsDecisionReview(1)
                    ->setCybersourceToken($result->requestToken)
                    ->setAdditionalData(serialize($result));

                if(isset($result->ccAuthReply->avsCode)){
                    $payment->setCcAvsStatus($result->ccAuthReply->avsCode);
                }

                if (isset($result->ccAuthReply->cvCode)) {
                    $payment->setCcCidStatus($result->ccAuthReply->cvCode);
                }

                $warningmessage = Cybersource_SOPWebMobile_Model_Source_Consts::getDmWarningMsg();
                Mage::getSingleton('adminhtml/session')->addError($warningmessage);

            } else {
                $reviewError = $this->processAvsCvnCodes($config,$result);
                 $errorMsg = Cybersource_SOPWebMobile_Model_Source_Consts::getErrorCode($result->reasonCode).$reviewError;
                 $error = Mage::helper('cybersourcesop')->__('There is an error in processing the payment. Please try again or contact us. '. $errorMsg);
            }
        } catch (Exception $e) {
           Mage::throwException(
                Mage::helper('cybersourcesop')->__('Gateway request error: %s', $e->getMessage())
            );
        }
        if ($error !== false) {
            Mage::throwException($error);
        }

        return $this;
	}



}
