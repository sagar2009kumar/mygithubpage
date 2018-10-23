<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Block_Token extends Mage_Customer_Block_Account_Dashboard
{

    /**
     * Collection model
     *
     * @var Mage_Oauth_Model_Resource_Token_Collection
     */
    protected $_collection;

    /**
     * Prepare collection
     */
    protected function _construct()
    {
        /** @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        /** @var $collection Cybersourcesop_Model_Resource_Token_Collection */
        $collection = Mage::getModel('cybersourcesop/token')->getCollection()->addCustomerFilter($session->getCustomer()->getId());

        $this->_collection = $collection;
    }

    /**
     * Get count of total records
     *
     * @return int
     */
    public function count()
    {
        return $this->_collection->getSize();
    }

    /**
     * Get collection
     *
     * @return Mage_Oauth_Model_Resource_Token_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Get delete url
     * @param Variant_Object $item
     * @return mixed
     */
    public function getDeleteLink($item) {
        return $this->getUrl('cybersourcesop/index/delete', array('token_id' => $item->getId()));

    }
    /**
     * Get update url
     * @param Variant_Object $item
     * @return mixed
     */
    public function getUpdateLink($item) {
        return $this->getUrl('cybersourcesop/index/update', array('token_id' => $item->getId()));
    }

    /**
     * checks if tokenisation is enabled in admin
     * @return bool
     */
    public function isTokenisationEnabled() {
        $enabled = Mage::getStoreConfig('payment/cybersourcesop/enable_tokenisation');
        if (isset($enabled) && $enabled == 1) {
            return true;
        } else {
            return false;
        }
    }

    //checks if web/mobile is enabled in admin
    /**
     * checks if web/mobile is enabled in admin
     * @return bool
     */
    public function isWebMobileEnabled() {
        $enabled = Mage::getStoreConfig('payment/cybersourcesop/mobile_enabled');
        if (isset($enabled) && $enabled == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets credit card type
     * @param $ccType
     * @return mixed
     */
    public function getCcType($ccType) {
        $ccTypes = Cybersource_SOPWebMobile_Model_Source_Consts::getCCMap();
        foreach ($ccTypes as $code) {
            if($code->cybercode == $ccType)
            {
                return $code->name;
            }
        }
    }

    /**
     * Gets save default token url.
     * @return string
     */
    public function getSaveDefaultUrl() {
        return $this->getUrl('cybersourcesop/index/saveDefaultToken');
    }
}
