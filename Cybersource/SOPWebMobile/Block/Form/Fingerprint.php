<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Block_Form_Fingerprint extends Mage_Core_Block_Template {

    /**
     * Store ID
     * @var mixed
     */
    protected $_storeId = null;
    /**
     * Finger print fields.
     * @var array
     */
    public $_fingerprintOptions = array();

    /**
     * Assign all the data to the block and Prepares the template
     */
    public function _construct() {
        $this->assignData();
        $this->setTemplate('cybersourcesop/fingerprint.phtml');
    }

    /**
     * Assign all the data to the block class array variable.
     */
    public function assignData() {
        $this->_storeId = Mage::app()->getStore()->getStoreId();
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        $this->_fingerprintOptions['session_id'] = Mage::getSingleton('customer/session')->getEncryptedSessionId();
        $this->_fingerprintOptions['merchant_id'] = $config['merchant_id'];
        $this->_fingerprintOptions['org_id'] = $config['device_fingerprint_org_id'];
        $this->_fingerprintOptions['fingerprint_url'] = $config['device_fingerprint_url'];
    }

    /**
     * is device fingerprint enabled
     * @return bool
     */
    public function isFingerprintEnabled() {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        return array_key_exists('device_fingerprint_enabled', $config) && $config['device_fingerprint_enabled'] ? true : false;
    }

    /**
     * Retrieves session id
     * @return mixed
     */
    public function getSessionId() {
        return $this->_fingerprintOptions['session_id'];
    }

    /**
     * Retrieves merchant id
     * @return mixed
     */
    public function getMerchantId() {
        return $this->_fingerprintOptions['merchant_id'];
    }
    /**
     * Retrieves order id
     * @return mixed
     */
    public function getOrgId() {
        return $this->_fingerprintOptions['org_id'];
    }
    /**
     * Retrieves fingerprint url
     * @return mixed
     */
    public function getFingerprintUrl() {
        return $this->_fingerprintOptions['fingerprint_url'];
    }
}
