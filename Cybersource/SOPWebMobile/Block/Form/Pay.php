<?php
/**
* © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
* “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
* (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
* Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
* You should read the Agreement carefully before using the code.
*/

class Cybersource_SOPWebMobile_Block_Form_Pay extends Mage_Payment_Block_Form_Cc
{
    /**
     * Store ID
     * @var mixed
     */
    public $_storeId = null;
    /**
     * Used to check if customer has saved tokens
     * @var bool
     */
    public $_hasTokens = false;
    /**
     * Collection of customer's tokens
     * @var array
     */
    public $_collection = null;

    /**
     * Prepares tokens collection
     */
    protected function _construct()
	{
		parent::_construct();
        $this->_storeId = Mage::app()->getStore()->getId();

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
		    $this->setTemplate('cybersourcesop/cc.phtml');
        }
        else {
            $this->setTemplate('cybersourcesop/cc_guest.phtml');
        }
        $mobileEnabled = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('mobile_enabled',$this->_storeId);
        if (isset($mobileEnabled) && $mobileEnabled) {
            $this->setTemplate('cybersourcesop/cc_mobile.phtml');
        }
        //is tokenisation enabled
        if ($this->isTokenisationEnabled()) {
            //DOES THE CUSTOMER HAVE TOKENS
            if ($tokenCollection = $this->getCustomerTokens()) {
                if ($tokenCollection->getData()) {
                    $this->_hasTokens = true;
                    $this->_collection = $tokenCollection;
                }
            }
        }
	}

    /**
     * Return cache live time. Force cache to be disabled
     * @return null
     */
    public function getCacheLifetime()
	{

		return null;

	}

    /**
     * Retrieves card regex
     * @return string
     */
    public function getCardsRegex()
	{
		$allcards = Cybersource_SOPWebMobile_Model_Source_Consts::getCCMap();
		$regexhtml = '';
		$newline = "\n";

		foreach($allcards as $card)
		{
			$regexhtml .= sprintf("'%s': %s,",$card->cybercode, $card->regex);
			$regexhtml . $newline;
		}

		return $regexhtml;
	}

    /**
     * checks if tokenisation is enabled in admin
     * @return bool
     */
    public function isTokenisationEnabled() {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        return $config['enable_tokenisation'] ? true : false;
    }

    /**
     * gets tokens that belong to customer
     * @return bool
     */
    public function getCustomerTokens() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        //gets tokens that belong to customer
        $tokenModel = Mage::getModel('cybersourcesop/token')->getCollection()->addFieldToFilter('customer_id',$customer->getId());
        if ($tokenModel->getData()) {
            //Send data back to front-end.
            return $tokenModel;
        }
        //Do not render saved CCs
        return false;
    }

    /**
     * Retrieves card type
     *
     * @param string $cardType
     * @return string
     */
    public function getCardClass($cardType) {
        $type = '';
        switch ($cardType) {
            case 001 : $type = 'visa';
                       break;
            case 002 : $type = 'mastercard';
                       break;
            case 003 : $type = 'amex';
                       break;
            case 033 : $type = 'visa';
                       break;
            case 042 : $type = 'maestro';
                       break;
        }
        return $type;
    }

    /**
     * Formats the date.
     * @param $date
     * @return string
     */
    public function formatExpirationDate($date) {
        $year = substr($date,strlen($date)-2);
        $month =  substr($date,0,2);
        $result = $month . '-' . $year;
        return $result;
    }

    /**
     * Checks the status of payment method
     * @return bool
     */
    public function isRegistering() {
        $method = $this->getMethod()->getData('info_instance')->getQuote()->getCheckoutMethod(true);
        if ($method == 'register'){
           return true;
        }
        return false;
    }

    /**
     * Checks if the block content is to be used.
     * @return bool
     */
    public function useBlockContent(){
        $useBlockContent = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('use_block_content',$this->_storeId);
        if(isset($useBlockContent) && $useBlockContent){
            return true;
        }
        return false;
    }

    /**
     * Retrieves content block
     * @return bool|mixed
     */
    public function getContentBlockId(){
        $blockId = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('block_content_id',$this->_storeId);
        if(isset($blockId) && $blockId){
            return $blockId;
        }
        return false;
    }
}
