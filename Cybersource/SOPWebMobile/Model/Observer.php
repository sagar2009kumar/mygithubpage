<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Observer
{
    /**
     * Mage::dispatchEvent($this->_eventPrefix.'_save_after', $this->_getEventData());
     * protected $_eventPrefix = 'sales_order';
     * protected $_eventObject = 'order';
     * event: sales_order_save_after
     */
    /**
     * Sends an email if the order was placed with Cybersource.
     * @param Varien_Event_Observer $observer
     */
    public function sendInvoiceEmail(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        //check if order was placed with Cybersource
        $method = $order->getPayment()->getMethodInstance()->getCode();

        if($method === 'cybersourcesop'){
            if ($order->hasInvoices()) {
                foreach ($order->getInvoiceCollection() as $inv) {
                    //Check if invoice has been paid
                    if($inv->getIsPaid()){
                        //send invoice email
                        $inv->sendEmail();
                    }
                }
            }
        }

    }
}
