<?php
/*
* © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
* “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
* (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
* Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
* You should read the Agreement carefully before using the code.
*/

/**
 *
 * Cybersourcesop Web Mobile Payment Action Dropdown source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cybersource_SOPWebMobile_Model_Source_MobilePaymentAction
{
    const ACTION_AUTHORIZE = 'authorization';
    const ACTION_AUTHORIZE_CREATE_TOKEN = 'authorization,create_payment_token';
    const ACTION_AUTHORIZE_UPDATE_TOKEN = 'authorization,update_payment_token';
    const ACTION_CAPTURE = 'sale';
    const ACTION_CAPTURE_CREATE_TOKEN = 'sale,create_payment_token';
    const ACTION_CAPTURE_UPDATE_TOKEN = 'sale,update_payment_token';

    /**
     * Returns payment actions
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ACTION_AUTHORIZE,
                'label' => Mage::helper('cybersourcesop')->__('Authorize Only')
            ),
            array(
                'value' => self::ACTION_CAPTURE,
                'label' => Mage::helper('cybersourcesop')->__('Authorize and Capture')
            ),
        );
    }
}
