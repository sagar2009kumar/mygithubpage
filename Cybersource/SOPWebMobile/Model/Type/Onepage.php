<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */
class Cybersource_SOPWebMobile_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    /**
     * Store id
     * @var string
     */
    public $_storeId;

    /**
     * Checks if the cybersource payment is enabled
     * @return bool
     */
    public function isCybersourceEnabled() {
        $this->_storeId = Mage::app()->getStore()->getStoreId();

        //are the modules present
        if ( Mage::getConfig()->getNode('modules/Cybersource_SOPWebMobile')) {
            //are they active
            if (Mage::getConfig()->getModuleConfig('Cybersource_SOPWebMobile')->is('active', 'true')) {
                //is the user checking out using cybersource payment method
                $payment = $this->getQuote()->getPayment();
                if ($payment->getMethod() == 'cybersourcesop') {
                    //payment method is cybersourcesop
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
    /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function saveOrder()
    {
        $this->validate();
        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

        $quote = $this->getQuote();
        $quote->setInventoryProcessed(true);
        $service = Mage::getModel('sales/service_quote', $quote);
        if ($this->isCybersourceEnabled()) {
            $service->setIsCybersourceSubmit(true);
        }
        $service->submitAll();

        if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData();

        $order = $service->getOrder();
        if ($order) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                array('order'=>$order, 'quote'=>$this->getQuote()));

            /**
             * a flag to set that there will be redirect to third party after confirmation
             * eg: paypal standard ipn
             */
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            // add order information to the session
            $this->_checkoutSession->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
            }
        }

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_checkoutSession->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
        );

        return $this;
    }

    /**
     * Process the quote to generate the order.
     * 
     * @return Mage_Sales_Model_Order
     */
    public function processOrder() {
        $this->_quote = $this->getQuote();
        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case Mage_Checkout_Model_Type_Onepage::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }
        $this->_quote->setReservedOrderId($_REQUEST['req_reference_number']);
        $this->_quote->collectTotals();
        $service = Mage::getModel('sales/service_quote', $this->_quote);
        $service->setIsCybersourceSubmit(false);
        //set payment method
        $this->_quote->getBillingAddress()->setPaymentMethod('cybersourcesop');
        $this->_quote->getShippingAddress()->setPaymentMethod('cybersourcesop');
        $this->getQuote()->getPayment()->setMethod('cybersourcesop');
        $service->submitAll();
        $this->_quote->save();
        $session =Mage::getSingleton('checkout/session');
        $session->setLastSuccessQuoteId($this->_quote->getId());
        $session->setLastQuoteId($this->_quote->getId());
        $session->setLastOrderId($service->getOrder()->getIncrementId());
        if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        $order = $service->getOrder();

        if ($order) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                array('order'=>$order, 'quote'=>$this->getQuote()));

            // add order information to the session
            $this->_checkoutSession->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
            }
        }
        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_checkoutSession->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
        );
        return $order;
    }
}
