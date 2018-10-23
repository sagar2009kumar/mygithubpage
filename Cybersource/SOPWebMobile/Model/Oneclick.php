<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */


class Cybersource_SOPWebMobile_Model_Oneclick extends Mage_Core_Model_Abstract {
    /**
     * Holds system config fields
     * @access private
     * @var array
     */
    private $_config;
    /**
     * Quantity of the item(s) purchased
     * @access private
     * @var int
     */
    private $_qty;
    /**
     * Holds Cart details
     * @access private
     * @var Mage_checkout_Model_Cart
     */
    public $_cart;
    /**
     * Holds customer details
     * @access private
     * @var Mage_Customer_Model_Customer
     */
    public $_customer;
    /**
     * Item being purchased
     * @access private
     * @var Mage_Catalog_Model_Product
     */
    public $_product;
    /**
     * Transaction model
     * @access private
     * @var Mage_Core_Model_Resource_Transaction
     */
    public $_transaction;
    /**
     * Sales order model
     * @access private
     * @var Mage_Sales_Model_Order
     */

    public $_order;
    /**
     * Customers addresses (billing and shipping)
     * @access private
     * @var Variant_Object
     */
    public $_addresses;
    /**
     * Customer group id
     * @access private
     * @var string|int
     */
    private $_groupId;
    /**
     * The ID of the store associated with the customer
     * @access private
     * @var string|int
     */
    private $_storeId;
    /**
     * Customer session
     * @access private
     * @var Mage_Customer_Model_Session
     */
    private $_session;


    /**
     * Builds a request and returns Varien Form
     * @param $product_id
     * @param $qty
     * @return bool|string
     */
    public function buildRequest($product_id,$qty) {
        $form = new Varien_Data_Form();
        $this->_config = Mage::getModel('cybersourcesop/config');
        $form->setAction($this->getCyberUrl())
            ->setId('cybersourceform')
            ->setName('cybersourceform')
            ->setMethod('POST')
            ->setUseContainer(true);
        //load the product that was clicked on
        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getEntityId();
        $this->_customer = Mage::getModel('customer/customer')->load($customer_id);
        $this->_addresses = $this->_customer->getAddresses();
        $this->_transaction = Mage::getModel('core/resource_transaction');
        $this->_qty = $qty;
        $this->_product = Mage::getModel('catalog/product')->load($product_id);
        //add the quantity of the product to cart
        $this->_cart = Mage::getSingleton('checkout/cart')
            ->truncate() // remove all active items in cart page
            ->init() //reinitialise cart
            ->addProduct($this->_product->getId(),$this->_qty) //add product to cart
            ->save();
        $this->_quote = $this->_cart->getQuote();
        $this->_storeId = $this->_customer->getStoreId();
        $this->_groupId = $this->_customer->getCustomerGroupId();
        $this->_session = Mage::getSingleton('customer/session');
        $this->_session->setCartWasUpdated(true);
        //create a quote
        try {
            $quote = $this->prepareOrder();
            if ($quote->getData()) {
                return $quote;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Adds the billing / shipping / payment details to the quote
     * @return mixed
     */
    protected function prepareOrder() {
        //Add the billing / shipping / payment details to the quote
        $quote = Mage::getModel('sales/quote')
            ->setStoreId(Mage::app()->getStore('default')->getId());
        $quote->assignCustomer($this->_customer);
        // add product(s)
        $product = Mage::getModel('catalog/product')->load($this->_product->getId());
        $buyInfo = array(
            'qty' => $this->_qty,
        );
        $quote->addProduct($product, new Varien_Object($buyInfo));
        $addressData = $this->_addresses[1]->getData();
        $billingAddress = $quote->getBillingAddress()->addData($addressData);
        $shippingAddress = $quote->getShippingAddress()->addData($addressData);
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate')
            ->setPaymentMethod('cybersourcesop');
        $quote->getPayment()
              ->importData(array('method' => 'cybersourcesop'));
        $quote->collectTotals()
              ->save();
        return $quote;
    }

}
