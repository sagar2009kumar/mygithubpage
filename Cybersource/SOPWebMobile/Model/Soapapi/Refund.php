<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */
class Cybersource_SOPWebMobile_Model_Soapapi_Refund extends Cybersource_SOPWebMobile_Model_Soapapi_Order
{

	/**
	 * Refund the payment transaction
	 *
	 * @param Mage_Sale_Model_Order_Payment $payment
	 * @param float $amount
	 * @return Mage_Cybersource_Model_Soap
	 */
	public function process(Varien_Object $payment, $amount)
	{
        //get Config
		$this->_storeId = $payment->getOrder()->getStoreId();
		$config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);

		//Customer billed in Default (website) currency, set currency code to OrderCurrencyCode and amountRefund to GrandTotal
		if (isset($config['default_currency']) && $config['default_currency'] == Cybersource_SOPWebMobile_Model_Source_Currency::DEFAULT_CURRENCY) {
			$amount = number_format($payment->getCreditmemo()->getGrandTotal(), 2, '.', '');
		}
		$error = false;

		if(is_object($payment))
		{
			$additionalData = $payment->getAdditonalData();
			//un serialize data to get the code
			if($additionalData)
			{
				$alldata = unserialize($additionalData);

				if(isset($alldata['req_reference_number']))
				{
					$this->assignOrderRef($alldata['req_reference_number']);
				}
			}
            //else no additional data use order ID
		    if (!$this->_orderref) {
                $orderId = $payment->getOrder()->getIncrementId();
                $this->assignOrderRef($orderId);

            }
		}
		$lastTransId = $payment->getLastTransId();
		if ($lastTransId && $amount>0) {
			$soapClient = $this->getSoapApi();
			$this->iniRequest($payment);
			$ccCreditService = new stdClass();
			$ccCreditService->run = "true";
			$ccCreditService->captureRequestToken = $lastTransId;
			$ccCreditService->captureRequestID = $payment->getCcTransId();
            $this->_request->ccCreditService = $ccCreditService;

			$this->addBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
			$this->addShippingAddress($payment->getOrder()->getShippingAddress());

            $purchaseTotals = new stdClass();
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;

            try {
				$result = $soapClient->runTransaction($this->_request);

				if ($result->reasonCode==Cybersource_SOPWebMobile_Model_Source_Consts::SOAP_SUCCESS) {
                    $payment->setTransactionId($result->requestID);

				} else {
					$error = Mage::helper('cybersourcesop')->__('There is an error in refunding the payment. Please try again or process manually');
				}
			} catch (Exception $e) {
				Mage::throwException(
				Mage::helper('cybersourcesop')->__('Gateway request error: %s', $e->getMessage())
				);

			}
		} else {
			$error = Mage::helper('cybersourcesop')->__('Error in refunding the payment.');

		}
		if ($error !== false) {
			Mage::throwException($error);
		}

		return $this;
	}


}
