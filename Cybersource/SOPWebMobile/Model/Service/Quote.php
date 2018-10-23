<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Service_Quote extends Mage_Sales_Model_Service_Quote {
    /**
     * Cybersource checkout status
     * @var bool
     */
    public $_isCybersource  = false;
    /**
     * Submit the quote. Quote submit process will create the order based on quote data
     *
     * @return Mage_Sales_Model_Order
     */
    public function submitOrder()
    {

        if ($this->_isCybersource) {
            $this->_order = $this->_quote;
            return $this->_quote;
        }
        return parent::submitOrder();
    }

    /** Sets the status of the cybersource checkout
     * @param $val
     * @return mixed
     */
    public function setIsCybersourceSubmit($val) {
        $this->_isCybersource = $val;
        return $this->_isCybersource;
    }

}
