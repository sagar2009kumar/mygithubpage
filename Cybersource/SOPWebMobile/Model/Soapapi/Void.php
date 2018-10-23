<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Soapapi_Void extends Cybersource_SOPWebMobile_Model_Soapapi_Abstract
{

	/**
	 * Reverse Authentication on the payment transaction (VOID)
	 *
	 * @param Mage_Sale_Model_Order_Payment $payment
	 * @param float $amount
	 * @return Mage_Cybersource_Model_Soap
	 */
	public function process(Varien_Object $payment)
	{
		//get Config
		$error = false;
		if(is_object($payment))
		{
			$additionalData = unserialize($payment->_data['additional_data']);
			//un serialize data to get the code
			if($additionalData)
			{

					$this->assignOrderRef($additionalData->merchantReferenceCode);

					$lastTransId = $payment->getLastTransId();
					if ($lastTransId) {
						$soapClient = $this->getSoapApi();
						$this->iniRequest($payment);
						$ccAuthReversalService = new stdClass();
						$ccAuthReversalService->run = "true";
						$ccAuthReversalService->authRequestID = $additionalData->requestID;
						$ccAuthReversalService->authRequestToken = $additionalData->requestToken;
						$ccAuthReversalService->reversalReason = 'incomplete';
						$this->_request->ccAuthReversalService = $ccAuthReversalService;

						$purchaseTotals = new stdClass();
						$purchaseTotals->currency = $additionalData->purchaseTotals->currency;
						$purchaseTotals->grandTotalAmount = $additionalData->ccAuthReply->amount;
						$this->_request->purchaseTotals = $purchaseTotals;

						try {
							$result = $soapClient->runTransaction($this->_request);

							if ($result->reasonCode==Cybersource_SOPWebMobile_Model_Source_Consts::SOAP_SUCCESS) {
								$payment->setTransactionId($result->requestID);
								$message = Mage::helper('cybersourcesop')->__('Order has been canceled due to Payment Void.');
								 $payment->getOrder()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $message)
								->cancel()
								->save();
							} else {
								$error = Mage::helper('cybersourcesop')->__('There is an error in reversing the authentication on the payment. Please try again or process manually');
							}
						} catch (Exception $e) {
							Mage::throwException(
								Mage::helper('cybersourcesop')->__('Gateway request error: %s', $e->getMessage())
							);

						}
					} else {
						$error = Mage::helper('cybersourcesop')->__('Error in authentication reversal on the payment.');

					}
			}

		}

		if ($error !== false) {
			Mage::throwException($error);
		}

		return $this;
	}


}
