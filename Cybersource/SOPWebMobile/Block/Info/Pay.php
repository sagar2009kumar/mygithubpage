<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Block_Info_Pay extends Mage_Payment_Block_Info
{
    /**
     * Prepares information
     * @param null $transport
     * @return null|Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
	{
		if (null !== $this->_paymentSpecificInformation) {
			return $this->_paymentSpecificInformation;
		}
		$info = $this->getInfo();
		$transport = new Varien_Object();
		$transport = parent::_prepareSpecificInformation($transport);

        if ($info->getCcLast4()) {
            $transport->addData(array(
                Mage::helper('payment')->__('Credit Card #') => $info->getCcLast4(),
                Mage::helper('payment')->__('Card Type') => Cybersource_SOPWebMobile_Model_Source_Consts::getCCname($info->getCcType()),
            ));
        }

		//if admin section
		if($this->getIsSecureMode() == false)
		{
            $successCodes =explode(',',str_replace(' ','',Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('forceavs_codes',$this->_storeId)));
            $successCodes=count($successCodes)>1?$successCodes:Cybersource_SOPWebMobile_Model_Source_Consts::getAvsSuccessVals();

            if(in_array($info->getData('cc_avs_status'), $successCodes))
			{
				$avstext = "MATCH / PARTIAL MATCH";
			}
			else
			{
				$avstext = "NO MATCH";
			}

            $successCodes =explode(',',str_replace(' ','',Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('forcecvn_codes',$this->_storeId)));
            $successCodes=count($successCodes)>1?$successCodes:Cybersource_SOPWebMobile_Model_Source_Consts::getCvnSuccessVals();

            if(in_array($info->getData('cc_cid_status'), $successCodes))
			{
				$cvntext = "MATCH";
			}
			else
			{
				$cvntext = "NO MATCH";
			}

			$transport->addData(array(
			Mage::helper('payment')->__('AVS Result') => $info->getData('cc_avs_status') . " - " . $avstext,
			Mage::helper('payment')->__('CVN Result') => $info->getData('cc_cid_status') . " - " . $cvntext
		));

		}

		return $transport;
	}
}
