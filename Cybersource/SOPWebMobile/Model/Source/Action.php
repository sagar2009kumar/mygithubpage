<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */


class Cybersource_SOPWebMobile_Model_Source_Action
{
    /**
     * Returns array from arrays with labels and payment method values
     * @return array
     */
    public function toOptionArray()
	{
		return array(
				array(
						'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
						'label' => Mage::helper('core')->__('Authorize & Capture')
				),
				array(
						'value' => Mage_Payment_Model_Method_Abstract::ACTION_ORDER,
						'label' => Mage::helper('core')->__('Order')
				),
				array(
						'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
						'label' => Mage::helper('core')->__('Authorize')
				),

		);
	}
}