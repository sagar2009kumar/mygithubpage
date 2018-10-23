<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Block_OneClick extends Mage_Catalog_Block_Product_Abstract {

    /**
     * Admin Config field values
     * @var array
     */
    public $_configvalues = false;

    /**
     * System Config fields
     * @var array
     */
    public $_config = null;

    /**
     * Sales order model
     * @var Mage_Sales_Model_Order
     */
    private $_order;

    /**
     * Main constructor
     */
    public function _construct()
    {
        $this->setTemplate('cybersourcesop/oneclick.phtml');
        $storeId = Mage::app()->getStore()->getStoreId();
        $this->_config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$storeId);
        if ($order = Mage::getModel('customer/session')->getCustomerOrder()) {
            $this->_order = $order;
        }
    }

    /**
     * Checks if the customer can buy using a token
     * @return bool
     */
    public function canBuy() {
        $loggedIn = $this->_getSession()->getCustomerId();
        $storeId = Mage::app()->getStore()->getId();
        $mobileEnabled = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('mobile_enabled', $storeId);
        if (isset($mobileEnabled) && $mobileEnabled) {
            if ($loggedIn) {
                $customerToken = Mage::getModel('cybersourcesop/token')->getCollection()
                    ->addFieldToFilter('customer_id', $loggedIn)
                    ->addFieldToFilter('is_default', '1')
                    ->setPageSize(1)
                    ->getFirstItem();
                if ($customerToken->getData()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieves customer session
     * @return mixed
     */
    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Retrieves the purchased product url
     * @param mixed $product_id
     * @return mixed
     */
    public function getPayUrl($product_id)
    {
        return $this->getUrl('cybersourcesop/index/oneclick', array('product_id' => $product_id));
    }

    /**
     * retrieves the quantity
     */
    public function getQty() {

    }

    /**
     * Retrieves purchased item id
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * Get the config or bail on error
     *
     * @return boolean
     */
    protected function getConfig()
    {
        if($this->_configvalues == false)
        {
            $this->_configvalues = Mage::getModel('cybersourcesop/config')->assignAllData();
        }
        return $this->_configvalues;
    }

    /**
     * Retrieves one click checkout url
     * @return string
     */
    public function getCheckoutUrl()
    {
        if ($this->_config['test']) {
            return Cybersource_SOPWebMobile_Model_Source_Consts::ONECLICK_TESTURL;
        } else {
            return Cybersource_SOPWebMobile_Model_Source_Consts::ONECLICK_URL;
        }
    }

    /**
     * Generates request form.
     * @return string
     */
    public function getFormHtml()
    {
        $config = $this->_config;
        $order = $this->_order;
        $customer_id = Mage::getModel('customer/session')->getCustomerId();
        $token = Mage::getModel('cybersourcesop/token')->getDefaultToken($customer_id);
        //add fields to oneClick to submit to Cybs
        $orderId = $this->_order->getIncrementId(); //get last real order ID that was generated for sale.
        $locale = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('cybersource_locale',$order->getStoreId());
        //populate request with values from Order

        //Customer billed in Default (website) currency, set currency code to DefaultCurrencyCode
        if (isset($config['default_currency']) && $config['default_currency'] == Cybersource_SOPWebMobile_Model_Source_Currency::DEFAULT_CURRENCY) {
            $currencyCode = $order->getOrderCurrencyCode();
            $amount = $order->getGrandTotal();
        } else {
            //Customer billed in Base currency, set currency code to BaseCurrencyCode
            $currencyCode = $order->getBaseCurrencyCode();
            $amount = $order->getBaseGrandTotal();
        }

        $fieldnames = array();
        $fieldnames['access_key'] = $config['mobile_merchant_access_key'];
        $fieldnames['profile_id'] = $config['mobile_profile_id'];
        $fieldnames['reference_number'] = $orderId;
        $fieldnames['payment_token'] = $token;
        $fieldnames['transaction_type'] =  Cybersource_SOPWebMobile_Model_Source_Consts::getCyberPaymentAction($config['payment_action']);
        $fieldnames['amount'] = $amount;
        $fieldnames['currency'] = $currencyCode;
        $fieldnames['locale'] = isset($locale)?$locale: Cybersource_SOPWebMobile_Model_Source_Consts::LOCALE;
        $fieldnames['transaction_uuid'] = $orderId.time();
        $fieldnames['signed_date_time'] =  gmdate("Y-m-d\TH:i:s\Z");
        $fieldnames['unsigned_field_names'] = '';
        $fieldnames['signed_field_names'] = 'access_key,profile_id,reference_number,payment_token,transaction_type,amount,currency,locale,transaction_uuid,signed_date_time,unsigned_field_names,signed_field_names';
        //prepare form
        $form = new Varien_Data_Form();
        $form->setAction($this->getCheckoutUrl())
            ->setId('cybersourceform')
            ->setName('cybersourceform')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($fieldnames as $key => $value) {
            $form->addField($key, 'hidden', array('name' => $key, 'value' => $value));
        }
        $secretKey = $config['mobile_merchant_secret_key'];
        //create signature
        $form->addField('signature', 'hidden', array('name' => 'signature', 'value' => $this->getSignature($fieldnames,$secretKey)));

        $html = $form->toHtml();
        $html .= '<script type="text/javascript">document.getElementById("cybersourceform").submit();</script>';
        $staticBlockId = Mage::getStoreConfig('payment/cybersourcesop/message_block');
        $staticBlockHtml = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($staticBlockId)->toHtml();
        $html .= $staticBlockHtml;
        Mage::getModel('customer/session')->setCustomerOrder(null); //remove order from session
        return $html;
    }

    /**
     * Sign the checkout fields
     * @param array $fieldnames
     * @param string $secretKey
     * @return mixed
     */
    private function getSignature($fieldnames, $secretKey) {
        return Mage::helper('cybersourcesop/security')->sign($fieldnames,$secretKey);
    }
}
