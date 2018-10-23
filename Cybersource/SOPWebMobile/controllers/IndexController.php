<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Sales order model
     * @var Mage_Sales_Model_Order
     */
    private $_order = null;
    /**
     * Cybersource request object
     * @var Variant_Object
     */
    private $_cyberrequest = null;
    /**
     * Holds data to be logged.
     * @var array
     */
    private $_logdata = null;
    /**
     * Custom message
     * @var Variant_Object
     */
    private $_customermessage = null;
    /**
     * Sales order model
     * @var Mage_Sales_Model_Order
     */
    private $_completedOrder = null;
    /**
     * Order status
     * @var string
     */
    private $_orderStatus = null;
    /**
     * used to hold orders based on the status of the payment
     * @var bool
     */
    private $_holdorder = false;
    /**
     * Customer token ID
     * @var mixed
     */
    private $_tokenId = null;
    /**
     * Cybersource response error messge
     * @var string
     */
    private $_cyberError = null;
    /**
     * Store Id
     * @var mixed
     */
    private $_storeId = null;

    /**
     * Default action method
     */
    public function indexAction()
    {
		$repp = Mage::app()->getRequest()->getPost();

        $this->getResponse()->setBody($this->getLayout()->createBlock('cybersourcesop/form_redirect')->toHtml());
    }

    /**
     * Checks if cybersource onestep checkout is enabled
     * @return bool
     */
    private function isOneStepEnabled() {
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        if (Mage::getConfig()->getNode('modules/Cybersource_Onestepcheckout') && Mage::getConfig()->getModuleConfig('Cybersource_Onestepcheckout')->is('active', 'true')) {
            //check if module is enabled
            if ($config['onestep_enabled']) {
                //check if module is enabled in system configurations
                return true;
            }
        }
        return false;
    }


    /**
     * Handle the response from Cybersource
     *
     */
    public function receiptAction()
    {
        $this->_storeId = isset($this->_storeId)?$this->_storeId: Mage::app()->getStore()->getStoreId();
        $sysConfig = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        

        $this->_cyberrequest = Mage::app()->getRequest()->getPost();
        
        echo "<pre>"; print_r($this->_cyberrequest); die;

        $this->_checkAvsCvnSuccess();
        if(isset($this->_cyberError)){
            $this->_errorAction();
            return $this;
        }
        try{
            $onePage = Mage::getModel('cybersourcesop/type_onepage');
            //process users order
            $onePage->processOrder();
        }
        catch(Exeption $ex){
            $this->_cyberrequest = $ex->getMessage();
            $this->_errorAction();
            return $this;
        }

        // Run MerchantResponse if it is not enabled in admin.
        if (!$this->merchantResponseEnabled()) {
            $this->merchantResponse();
        }



        if (isset($sysConfig['mobile_enabled']) && $sysConfig['mobile_enabled'] == 1) {
            $secretKey = $sysConfig['mobile_merchant_secret_key'];
        } else {
            $secretKey = $sysConfig['secret_key'];
        }

        if(!Mage::helper('cybersourcesop/security')->validateResponse($secretKey, $this->_cyberrequest))
        {
            //cancel order if it can be found and redirect to checkout
            $this->_fatalErrorAction();
            return $this;
        }

        $status = $this->_cyberrequest['reason_code'];

        if(empty($status))
        {
            $this->_fatalErrorAction();
            return $this;
        }
        //redirect user to success or back to cart
        //
        //success codes are any code that has authorised the card in any way, including avs fails
        if(in_array($status, Cybersource_SOPWebMobile_Model_Source_Consts::getSuccessCodes()))
        {

            if($this->_validateReviewOrder())
            {
                $this->_processSuccess();
                return $this;

            }else
            {
                $this->_errorAction();
                return $this;
            }

        } else {
            //if status is review
            if($status == Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_DM_REVIEW)
            {
                $this->_reviewDmAction();
                return $this;
            }

            //if all else fails!
            $this->_errorAction();
            return $this;
        }

    }

    /**
     * Checks if merchant response is enabled
     * @return bool
     */
    public function merchantResponseEnabled()
    {
        $merchantResponse = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('merchant_response',$this->_storeId);
        if (isset($merchantResponse) && $merchantResponse){
            return true;
        }
        return false;
    }

    /**
     * Reviews the response from cybersource
     * @return $this
     */
    protected function _merchantReview()
    {
        $orderid = $this->_cyberrequest['req_reference_number'];
        if(!$orderid)
        {
            $session = $this->_getCheckout();
            $orderid = $session->getLastRealOrderId();
        }
        if($orderid)
        {
            try {
                $order = Mage::getModel('sales/order')->loadByIncrementId($orderid);

                if ($this->_getOrderStatus() != Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_PENDING_DM_REVIEW) {

                    $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                    $status = Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_PENDING_DM_REVIEW;
                    $comment = Mage::getStoreConfig('payment/cybersourcesop/review_comment');

                    //check if the transaction id is set
                    if(isset($this->_cyberrequest['transaction_id'])){
                        $tid = $this->_cyberrequest['transaction_id'];
                    }
                    else {
                        $tid = -1 ;
                    }

                    //load the payment
                    $payment = $order->getPayment();

                    $quoteId = $order->getQuoteId();
                    $quote = Mage::getModel('sales/quote')->load($quoteId);
                    $quotePayment = $quote->getPayment();
                    $useToken = $quotePayment->getCybersourcesopSaveToken();
                    if ($useToken == 'true') {
                        try {
                            $payment->setCybersourcesopSaveToken('true')->save();
                        } catch (Exception $e) {
                            $this->_addToLog("Error saving payment token in order: " . $e->getMessage());
                        }
                    }

                    $expireDate = explode('-',$this->_cyberrequest['req_card_expiry_date']);
                    //set payment data and save the order
                    $payment->setLastTransId($tid)
                        ->setCcTransId($tid)
                        ->setCcLast4($this->_cyberrequest['req_card_number'])
                        ->setCcType($this->_cyberrequest['req_card_type'])
                        ->setCcExpMonth($expireDate[0])
                        ->setCcExpYear($expireDate[1])
                        ->setIsTransactionClosed(0)
                        ->setAdditionalData(serialize($this->_cyberrequest));

                    // set order status to DM review
                    $order->setState($state, $status, $comment, false);
                    $order->save();
                }
            }
            catch (Exception $e) {
                $this->_addToLog("Error setting order status to pending Decision Manager:" . $e->getMessage());
                $this->_setErrorCustomerMessage(Mage::helper('cybersourcesop')->__("There was an error submitting your order."));

                //Redirect customer to cart page, where error message will display.
                $redirecturl = Mage::getUrl('checkout/cart');
                $this->_handleResponse($redirecturl);

                return $this;
            }
        }
    }

    /**
     * Retrieves the key
     * @return mixed
     */
    public function getSecretKey()
    {
        $sysConfig = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
        if (isset($sysConfig['mobile_enabled']) && $sysConfig['mobile_enabled'] == 1) {
            $secretKey = $sysConfig['mobile_merchant_secret_key'];
        } else {
            $secretKey = $sysConfig['secret_key'];
        }
        return $secretKey;
    }

    /**
     * Checks AVS and CVN validations
     * @param int $status
     * @return $this
     */
    protected function _checkSuccess($status) {
        if(in_array($status, Cybersource_SOPWebMobile_Model_Source_Consts::getSuccessCodes()))
        {
            //avs review
            if(!$this->_validateReviewOrder())
            {
                //avs / cvn fails
                $this->_errorAction();
                return $this;
            } else { //avs and cvn are good -> continue
                if (array_key_exists('payment_token',$this->_cyberrequest)) {
                    $this->_tokenId = $this->_cyberrequest['payment_token'];
                }
                if (array_key_exists('req_payment_token',$this->_cyberrequest)) {
                    $this->_tokenId = $this->_cyberrequest['req_payment_token'];
                }
                $this->_processToken($this->_tokenId);
                if(!Mage::helper('cybersourcesop/security')->validateResponse($this->getSecretKey(), $this->_cyberrequest))
                {
                    //cancel order if it can be found and redirect to checkout
                    $this->_fatalErrorAction();
                    return $this;
                }

                //complete order
                if (array_key_exists('req_reference_number',$this->_cyberrequest)) {
                    $order_increment_id = $this->_cyberrequest['req_reference_number'];
                    if($this->_completeOrder($order_increment_id))
                    {
                        //Split transaction types into array.
                        $transactionType = explode(',',$this->_cyberrequest['req_transaction_type']);

                        //invoice? Check if Sale is in the transaction types send back from Cybersource.
                        if(in_array(Cybersource_SOPWebMobile_Model_Source_Consts::SALERESPONSE, $transactionType))
                        {
                            //try and invoice if it fails the customer doesnt need to know
                            $this->_invoiceOrder($order_increment_id);

                        }
                        //check if we should hold this order based on the AVS and CVN
                        $this->_checkHoldOrder();
                        return $this;
                    }
                }
            }
        } else {
            //if status is review
            if($status == Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_DM_REVIEW)
            {
                $this->_merchantReview();
                return $this;
            }
            //payment failed due to incorrect auth / sale / issue with request
            return $this;
        }
    }

    /**
     * Checks the success of the request
     * @return $this
     */
    public function merchantResponse()
    {
        $this->_cyberrequest = Mage::app()->getRequest()->getPost();
        $status = $this->_cyberrequest['reason_code'];
        if(empty($status))
        {
            $this->_fatalErrorAction();
            return $this;
        }
        //success codes are any code that has authorised the card in any way, including avs fails
        //work through each review function

        $this->_checkSuccess($status);

    }

    // Handling Merchant Response Post URL
    public function merchantResponseAction()
    {
        Mage::log('Start Processing Merchant Response Post: ', null, Cybersource_SOPWebMobile_Model_Source_Consts::LOGFILE);

        $this->_cyberrequest = Mage::app()->getRequest()->getPost();
        $replyFields=(array)$this->_cyberrequest;

        try{
            if(isset($replyFields['req_reference_number'])){
                $quote = Mage::getModel('sales/quote')->getCollection()
                    ->addFieldToFilter('reserved_order_id', (string)$replyFields['req_reference_number'])
                    ->setPageSize(1)
                    ->getFirstItem();
                if(isset($quote) && $quote->getId()){

                    $quote->setCustomer($quote->getCustomer());
                    $this->_storeId=$quote->getStoreId();
                    Mage::getSingleton('checkout/session')->setQuote($quote);
                    Mage::getSingleton('customer/session')->setCustomer($quote->getCustomer());

                    $_REQUEST['req_reference_number'] = $replyFields['req_reference_number'];
                    $this::receiptAction();
                    if(isset($this->_cyberError)){
                        $message='Error when Processing Merchant Response Post: '.$this->_cyberError;
                    }
                    else{
                        $message = "Merchant Response Post processed successfully. Order number is ".$replyFields['req_reference_number'];
                    }
                }
                else{
                    $message='Error: Could not retrieve the quote with reserved_order_id: '.$replyFields['req_reference_number'];
                }
            }
            else{
                $message='Error: Empty Merchant Response Post.';

            }
        }
        catch(Exception $ex){
            $message='Exception thrown when Processing Merchant Response Post: ' . $ex->getMessage();
        }

        Mage::log($message, null, Cybersource_SOPWebMobile_Model_Source_Consts::LOGFILE);
        Mage::log('End Processing Merchant Response Post: ', null, Cybersource_SOPWebMobile_Model_Source_Consts::LOGFILE);
    }

    /**
     * Updates old token
     * @param int $token_id
     */
    protected function _processToken($token_id)
    {
        if ($token_id) {
            //check if token exists already (used in authorisation when token already exists)
            $tokens = Mage::getModel('cybersourcesop/token')->load($token_id,'token_id');
            if (!$tokens->getData()) {
                $this->_processNewToken($token_id);
            } else {
                $this->_updateToken($token_id, $tokens);
            }
        }
    }

    /**
     * Saves new token
     * @param int $token_id
     * @return $this
     */
    protected function _processNewToken($token_id)
    {
        //save new token
        $newToken = Mage::getModel('cybersourcesop/token');
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        try {
            $newToken->setTokenId($token_id)
                ->setCcNumber($this->_cyberrequest['req_card_number'])
                ->setCcExpiration($this->_cyberrequest['req_card_expiry_date'])
                ->setCustomerId($customerId)
                ->setCcType($this->_cyberrequest['req_card_type'])
                ->setMerchantRef($this->_cyberrequest['req_reference_number'])
                ->save();
        } catch (Exception $e) {
            $this->_errorAction();
            return $this;
        }
    }

    /**
     * Update token details
     * @param mixed $token_id
     * @param array $tokens
     * @return $this
     */
    protected function _updateToken($token_id, $tokens)
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        //token updated
        $first_name = $this->_cyberrequest['req_bill_to_forename'];
        $last_name = $this->_cyberrequest['req_bill_to_surname'];
        $email = $this->_cyberrequest['req_bill_to_email'];
        $address1 = $this->_cyberrequest['req_bill_to_address_line1'];

        if(isset($this->_cyberrequest['req_bill_to_address_line2'])){
            $address2 = $this->_cyberrequest['req_bill_to_address_line2'];
        }else{
            $address2 = '';
        }

        if(isset($this->_cyberrequest['req_bill_to_address_state'])){
            $state = $this->_cyberrequest['req_bill_to_address_state'];
        }else{
            $state = '';
        }

        if(isset($this->_cyberrequest['req_bill_to_address_postal_code'])){
            $postal_code = $this->_cyberrequest['req_bill_to_address_postal_code'];
        }else{
            $postal_code = '';
        }

        $city = $this->_cyberrequest['req_bill_to_address_city'];
        $country = $this->_cyberrequest['req_bill_to_address_country'];
        try {
            $order_increment_id = $this->_cyberrequest['req_reference_number'];
            $order = Mage::getModel('sales/order')->load($order_increment_id,'increment_id');
            $billingAddress = $order->getBillingAddress();
            //are the values all set
            if ($first_name && $last_name && $email && $address1 && $state && $postal_code && $city && $country) {
                $billingAddress->setFirstname($first_name)
                    ->setLastname($last_name)
                    ->setEmail($email)
                    ->setStreet($address1.' '.$address2)
                    ->setRegion($state) //state
                    ->setPostcode($postal_code)
                    ->setCity($city)
                    ->setCountryId($country)->save();
            }
            //update token details
            $tokens->setTokenId($token_id)
                ->setCcNumber($this->_cyberrequest['req_card_number'])
                ->setCcExpiration($this->_cyberrequest['req_card_expiry_date'])
                ->setCustomerId($customerId)
                ->setCcType($this->_cyberrequest['req_card_type'])
                ->save();


        } catch (Exception $e) {
            $this->_errorAction();
            return $this;
        }
    }

    /**
     * Process the successful request
     * @return $this
     */
    public function _processSuccess()
    {
        $this->_addToLog('Success Action called:');
        //dispatch success event
        Mage::dispatchEvent('cybersourcesop_order_success',array('order'=>$this->_completedOrder));
        $url = Mage::getUrl('checkout/onepage/success', array('_secure'=>true));
        $this->_handleResponse($url);
        return $this;
    }

    /**
     * Checks if the order should be held
     * @return bool
     */
    protected function _checkHoldOrder()
    {
        if($this->_completedOrder == null)
        {
            return true;
        }

        if($this->_completedOrder->canHold() && $this->_getOrderHold())
        {
            try{
                $this->_completedOrder->addStatusHistoryComment('Held due to AVS/CVN failure');
                //->setIsVisibleOnFront(true);
                $this->_completedOrder->hold()->save();

            }catch (Exception $e) {

                //set the error message
                $this->_addToLog("Error in putting order on hold" . $e->getMessage());
                return false;

            }

        }

    }

    /**
     * completes the order and Send New Order Confirmation Email
     * @param mixed $orderidin
     * @return bool
     */
    protected function _completeOrder($orderidin)
    {
        if (!$orderidin) {
            return false;
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderidin);

        if ($order->getId()) {

            try {

                //load the payment
                $payment = $order->getPayment();

                //sets this directly to the payment and order objects
                $this->_setPaymentInformation($payment, $order);

                $order->save();

                $this->_addToLog('Order successfully saved: ' . $orderidin);

                //Send New Order Confirmation Email
                if ($order->getCanSendNewEmailFlag()){
                    $order->sendNewOrderEmail();
                }

                //set class var
                $this->_completedOrder = $order;
                return true;

            }
            catch (Exception $e) {

                //set the error message
                $this->_addToLog("Error in processing success" . $e->getMessage());
                return false;

            }

        }else {

            //we have no information available so process this as a fatal error
            $this->_addToLog("Error setting restoring quote: last order id:". $orderidin);
            return false;
        }
    }

    /**
     * Invoice the order using order id and Send Invoice email.
     * @param mixed $ordernumin
     * @return bool
     * @throws Exception
     */
    protected function _invoiceOrder($ordernumin)
    {
        $order = $this->_completedOrder;
        if($this->_completedOrder == null)
        {
            $order = Mage::getModel('sales/order')->loadByIncrementId($ordernumin);
        }

        if(!$order->hasData())
        {
            $this->_addToLog("Cant retrieve the order to create invoice on");
            return false;
        }

        if(!$order->canInvoice())
        {
            $this->_addToLog("Cant invoice for the order:". $ordernumin);
            return false;
        }

        try {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                Throw new Exception('No products found to invoice');
            }

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transactionSave->save();

            //Send Invoice email
            $invoice->sendEmail();

            $message = Mage::helper('cybersourcesop')->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
            $comment = $order->addStatusHistoryComment($message)
                ->setIsCustomerNotified(true)
                ->save();
        }
        catch (Mage_Core_Exception $e) {

            $this->_addToLog($e->getMessage());
            return false;
        }

        return true;

    }

    /**
     * Retrieves payment information
     * @param $payment
     * @param Mage_Sales_Model_Order $order
     * @return $this
     */
    protected function _setPaymentInformation($payment, $order)
    {
        $grandTotal = $order->getBaseGrandTotal();
        $quoteId = $order->getQuoteId();
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $quotePayment = $quote->getPayment();
        $useToken = $quotePayment->getCybersourcesopSaveToken();
        if ($useToken == 'true') {
            try {
                $payment->setCybersourcesopSaveToken('true')->save();
            } catch (Exception $e) {
                $this->_addToLog("Error saving payment token in order: " . $e->getMessage());
            }
        }


        //check if the transaction id is set
        if(isset($this->_cyberrequest['transaction_id'])){
            $tid = $this->_cyberrequest['transaction_id'];
        }
        else {
            $tid = -1 ;
        }

        //set payment data and save the order
        $payment->setCcApproval(true)
            ->setLastTransId($tid)
            ->setCcTransId($tid)
            ->setCcLast4(Cybersource_SOPWebMobile_Model_Source_Consts::retrieveCardNum($this->_cyberrequest['req_card_number']))
            ->setCcType(Cybersource_SOPWebMobile_Model_Source_Consts::getMageCCs($this->_cyberrequest['req_card_type']))
            ->setPreparedMessage("Payment Sucessfull")
            ->setIsTransactionClosed(0)
            ->setAdditionalData(serialize($this->_cyberrequest))
            ->registerAuthorizationNotification($grandTotal);

        //set optional payment data
        $this->_addPaymentData($payment, 'cybersourcesop_auth_xid', 'payer_authentication_xid')
            ->_addPaymentData($payment, 'cybersourcesop_proof_xml', 'payer_authentication_proof_xml')
            ->_addPaymentData($payment, 'cybersourcesop_eci', 'payer_authentication_eci')
            ->_addPaymentData($payment, 'cybersourcesop_cavv', 'payer_authentication_cavv')
            ->_addPaymentData($payment, 'cc_avs_status', 'auth_avs_code')
            ->_addPaymentData($payment, 'cc_cid_status', 'auth_cv_result')
            ->_addPaymentData($payment,'cybersourcesop_save_token',$payment->getCybersourcesopSaveToken());


        return $this;

    }

    /**
     * Add any optional payment data to the data object
     *
     * @param Variant_Object $payment
     * @param string $field
     * @param mixed $value
     * @return Cybersource_SOPWebMobile_IndexController
     */
    protected function _addPaymentData($payment, $field, $value)
    {
        if(isset($this->_cyberrequest[$value]))
        {
            $payment->setData($field,$this->_cyberrequest[$value]);
        }

        return $this;
    }


    /**
     * Perform any actions that may alter a successful authentication
     *
     * @return boolean
     */
    protected function _validateReviewOrder()
    {

        //3d secure check here first
        if($this->_3dSecureFullPass())
        {
            return true;
        }

//        //work through each review function
        return true;

    }

    /**
     * Check AVS and CVN codes for validations.
     * @return $this
     */
    public function _checkAvsCvnSuccess(){
        $status = (int)$this->_cyberrequest['reason_code'];
        if(empty($status))
        {
            $this->_fatalErrorAction();
            return $this;
        }
        if(($status!=Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_ACCEPT)){
            $hasErrorAvsCvn=false;
            $successCodes =explode(',',str_replace(' ','',Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('forceavs_codes',$this->_storeId)));
            $successCodes=count($successCodes)>1?$successCodes:Cybersource_SOPWebMobile_Model_Source_Consts::getAvsSuccessVals();

            if($this->_processReview('forceavs',Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_AVS_FAIL,'auth_avs_code',$successCodes) == false)
            {
                $this->_cyberError = Cybersource_SOPWebMobile_Model_Source_Consts::getAVSErrorCode($this->_cyberrequest['auth_avs_code']);
                $hasErrorAvsCvn=true;
            }
            $avsError = $this->_cyberError;
            $successCodes =explode(',',str_replace(' ','',Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('forcecvn_codes',$this->_storeId)));
            $successCodes=count($successCodes)>1?$successCodes:Cybersource_SOPWebMobile_Model_Source_Consts::getCvnSuccessVals();
            if($this->_processReview('forcecvn',Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_CVV_FAIL,'auth_cv_result',$successCodes) == false)
            {
                $avnError = Cybersource_SOPWebMobile_Model_Source_Consts::getCVNErrorCode($this->_cyberrequest['auth_cv_result']);
                $this->_cyberError=$avsError .' '. $avnError;

                $hasErrorAvsCvn=true;
            }
            $reasonCodeError = Cybersource_SOPWebMobile_Model_Source_Consts::getErrorCode($this->_cyberrequest['reason_code']);
            if(isset($reasonCodeError))
            {
                $this->_cyberError = $reasonCodeError.(string) ' '. $this->_cyberError;
                $hasErrorAvsCvn=true;
            }

            if($hasErrorAvsCvn){
                //avs / cvn fails
                return $this;
            }
        }
    }

    /**
     * Performs AVS and CVV review.
     * @param string $configval
     * @param int  $failvalue
     * @param string $statuscheckval
     * @param array $statussuccessarray
     * @return bool
     */
    protected function _processReview($configval, $failvalue, $statuscheckval, $statussuccessarray)
    {
        $action = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig($configval,$this->_storeId);
        //check if cybersource returned the correct status
        if (array_key_exists($statuscheckval,$this->_cyberrequest)) {
            //only continue if a failure has been found
            if (isset($this->_cyberrequest['reason_code'])) {
                $reasonCode = (int)$this->_cyberrequest['reason_code']; //cast to integer as CYBS returns this value as INT or String for Web/Mobile.
                if(($reasonCode == $failvalue) || (!in_array($this->_cyberrequest[$statuscheckval], $statussuccessarray)))
                {
                    $this->_cyberError = $configval=='forcecvn'?Cybersource_SOPWebMobile_Model_Source_Consts::getCVNErrorCode($this->_cyberrequest[$statuscheckval]):Cybersource_SOPWebMobile_Model_Source_Consts::getAVSErrorCode($this->_cyberrequest[$statuscheckval]);
                    //decide what to do
                    if($action == Cybersource_SOPWebMobile_Model_Source_Consts::CONFIG_CARDCHECK_ACCEPT_HOLD)
                    {
                        //set order status to hold
                        $this->_setOrderHold(true);
                        return true;

                    }
                    else if($action == Cybersource_SOPWebMobile_Model_Source_Consts::CONFIG_CARDCHECK_DECLINE)
                    {
                        return false;
                    }
                }
            }

        }
        return true;
    }

    /**
     * Returns true if the order/payment pass 3d secure
     * @return bool
     */
    protected function _3dSecureFullPass()
    {
        if(Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('force3dsecure',$this->_storeId) != 1)
        {
            return false;
        }

        if(in_array($this->_getOptionalRequestVal('payer_authentication_eci'),Cybersource_SOPWebMobile_Model_Source_Consts::get3dSecureSuccessVals()))
        {
            $this->_addToLog('Full 3d secure pass');
            return true;
        }

        return false;

    }

    /**
     * Retrieves checkout session.
     * @return mixed
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Cybersource Decision Manager Review Action
     *
     * Called when a request back from cybersource has the review decision
     * Sets the order state to payment_review and status to STATUS_PENDING_DM_REVIEW
     * Sets a useful message to the customer
     *
     * @return Cybersource_SOPWebMobile_IndexController
     */
    protected function _reviewDmAction()
    {
        $session = $this->_getCheckout();
        $this->_addToLog('Review DM Action called:');


        //Check if review message is set in config and sets reviewMessage value accordingly.
        $reviewMessage = Mage::getStoreConfig('payment/cybersourcesop/review_message') ? Mage::getStoreConfig('payment/cybersourcesop/review_message') : "Your order is currently under review.";
        $this->_setNoticeCustomerMessage(Mage::helper('cybersourcesop')->__("%s", $reviewMessage));

        //Redirect customer to checkout success page, where notice message will display, informing customer that order was placed successfully, but is under review.
        $redirecturl = Mage::getUrl('checkout/onepage/success', array('_secure'=>true));
        $this->_handleResponse($redirecturl);

        return $this;
    }

    /**
     * Cybersource General Error Action
     *
     * Called when a request back from cybersource has the error decision
     * Tries to retrieve the inital quote, cancels the order and redirects back to checkout
     * Sets a more useful error message to the customer based on the cybersource response
     *
     * @return Cybersource_SOPWebMobile_IndexController
     */
    public function _errorAction()
    {
        $session = $this->_getCheckout();
        $this->_addToLog('Error Action called:');

        if(isset($this->_cyberrequest['reason_code'])){
            if(!isset($this->_cyberError)) {
                $this->_cyberError = Cybersource_SOPWebMobile_Model_Source_Consts::getErrorCode($this->_cyberrequest['reason_code']);
            }
            if ($this->_cyberrequest['reason_code'] == 102) {
                $this->_cyberError = $this->_cyberrequest['message'];
            }
        }

        $redirecturl = Mage::getUrl('checkout/cart');

        $orderid = $this->_cyberrequest['req_reference_number'];

        if(!$orderid)
        {
            $orderid = $session->getLastRealOrderId();
        }

        if($orderid)
        {
            if (Mage::getStoreConfig('payment/cybersourcesop/debug')) {

                $this->_addToLog('Cybersource Error: '.$this->_cyberError);
            }
            //attemptes to cancel the order and restore the quote so customer can try again
            $this->_cancelOrderAndRestoreQuote($orderid,$this->_cyberError);

        }

        $this->_setErrorCustomerMessage(Mage::helper('cybersourcesop')->__("There was an error submitting your payment. %s",$this->_cyberError));

        //reset any session data
        $session->unsLastRealOrderId();

        $this->_handleResponse($redirecturl);

        return $this;

    }


    /**
     * Cybersource Fatal Error Action
     *
     * Called when a request back from cybersource has failed the signiture validation
     * Tries to retrieve the inital quote, cancels the order and redirects back to checkout
     *
     * @return Cybersource_SOPWebMobile_IndexController
     */
    public function _fatalErrorAction()
    {
        $session = $this->_getCheckout();
        $this->_addToLog('Fatal Error Action called:');

        if(isset($this->_cyberrequest['reason_code'])){
            $this->_cyberError = Cybersource_SOPWebMobile_Model_Source_Consts::getErrorCode($this->_cyberrequest['reason_code']);
        }

        //no matter what happens the customer is going back to the cart
        $redirecturl = Mage::getUrl('checkout/cart');


        //try and restore the quote we cant use the cybersource response for this
        if ($session->getLastRealOrderId()) {

            //attempts to cancel the order and restore the quote
            $this->_cancelOrderAndRestoreQuote($session->getLastRealOrderId(),$this->_cyberError);

        }

        $this->_setErrorCustomerMessage(Mage::helper('cybersourcesop')->__("There was an error submitting your payment, please try again. %s",$this->_cyberError));

        //reset any session data
        $session->unsLastRealOrderId();

        $this->_handleResponse($redirecturl);

        return $this;

    }

    /**
     * Returns customer to the payment form if the payment failed for something on the card
     * Potentially dodgy as customers details may need to change
     *
     * @return Cybersource_SOPWebMobile_IndexController
     */
    public function _returnToPaymentStep()
    {
        //keep everything the same and just return to the payment step
        $paymentUrl = Mage::getUrl('cybersourcesop/index/',array('_secure' => true));

        //get error from cybsersource
        if(isset($this->_cyberrequest['reason_code'])){
            $this->_cyberError =$this->_cyberError.' '. Cybersource_SOPWebMobile_Model_Source_Consts::getErrorCode($this->_cyberrequest['reason_code']);
        }

        $this->_setErrorCustomerMessage(Mage::helper('cybersourcesop')->__("There was an error submitting your payment: ") . $this->_cyberError);

        //set the error message for the customer
        //$this->_setErrorCustomerMessage($messagein);

        $this->_handleResponse($paymentUrl);

        return $this;

    }

    /**
     * Cancel the order id and restore the quote to the users session
     *
     * @param mixed $orderidin
     * @param string $error
     * @return Cybersource_SOPWebMobile_IndexController
     */

    protected function _cancelOrderAndRestoreQuote($orderidin,$error)
    {
        $session = $this->_getCheckout();

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderidin);

        if ($order->getId()) {

            try {

                //Cancel order
                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                    $order->registerCancellation(Mage::helper('cybersourcesop')->__('Request from cybersource error. %s',$error))->save();
                }

                $quote = Mage::getModel('sales/quote')
                    ->load($order->getQuoteId());
                //Return quote
                if ($quote->getId()) {
                    $quote->setIsActive(1)
                        ->setReservedOrderId(NULL)
                        ->save();
                    $session->replaceQuote($quote);
                }

                $this->_addToLog('Retrieved quote succesfully from order: ' . $orderidin);

            }
            catch (Exception $e) {

                //set the error message
                $this->_addToLog("Error setting restoring quote:" . $e->getMessage());

            }


        }else {

            //we have no information available so just log and display error
            $this->_addToLog("Error setting restoring quote: last order id:". $orderidin);

        }

        return $this;

    }

    /**
     * Handle the redirect for the customer
     *
     * @param string $redirecturl
     * @return Cybersource_SOPWebMobile_IndexController
     */
    private function _handleResponse($redirecturl)
    {
        $session = $this->_getCheckout();

        //set message for session
        if($session && is_object($this->_customermessage))
        {
            if($this->_customermessage->messageType == 'error')
            {
                $session->addError($this->_customermessage->message);
            }else
            {
                $session->addSuccess($this->_customermessage->message);
            }
        }

        //log stuff
        $this->_createLog();

        if(empty($redirecturl))
        {
            $redirecturl = Mage::getUrl('checkout/cart');
        }

        //redirect
        if (!Mage::registry('redirect_url')) {
            Mage::register('redirect_url',$redirecturl);
        }
        $this->_redirectUrl($redirecturl);

        return $this;

    }

    /**
     * Set notice message for the customer to view
     *
     * @param string $messagein
     */
    private function _setNoticeCustomerMessage($messagein)
    {
        if($this->_customermessage == null)
        {

            $this->_customermessage = new stdClass;

        }

        if($messagein)
        {
            $this->_customermessage->message = $messagein;
            $this->_customermessage->messageType = 'notice';
        }

    }

    /**
     * Set error message for the customer to view
     *
     * @param Variant_Object $messagein
     */
    private function _setErrorCustomerMessage($messagein)
    {
        if($this->_customermessage == null)
        {

            $this->_customermessage = new stdClass;

        }

        if($messagein)
        {
            $this->_customermessage->message = $messagein;
            $this->_customermessage->messageType = 'error';
        }

    }

    /**
     * Set success message for the customer to view
     *
     * @param Variant_Object $messagein
     */
    private function _setSuccessCustomerMessage($messagein)
    {
        if($this->_customermessage == null)
        {

            $this->_customermessage = new stdClass;

        }

        if($messagein)
        {
            $this->_customermessage->message = $messagein;
            $this->_customermessage->messageType = 'success';
        }

    }

    /**
     * Retrieves order status
     * @return string
     */
    private function _getOrderStatus()
    {
        if($this->_orderStatus == null)
        {
            return Mage_Sales_Model_Order::STATE_PROCESSING;
        }

        return $this->_orderStatus;
    }

    /**
     * Sets the order status
     * @param string $statusin
     */
    private function _setOrderStatus($statusin)
    {

        $this->_orderStatus = $statusin;

    }

    /**
     * Retrieves hold status
     * @return bool
     */
    private function _getOrderHold()
    {

        return $this->_holdorder;


    }

    /**
     * Sets hold status
     * @param bool $holdstatus
     */
    private function _setOrderHold($holdstatus = false)
    {
        $this->_holdorder = $holdstatus;
    }

    /**
     * Get an optional return value from the cybersource request
     * If no value exists return blank string.

     * @param string $valuerequired
     * @return string
     */
    private function _getOptionalRequestVal($valuerequired)
    {
        if(isset($this->_cyberrequest[$valuerequired]))
        {
            return $this->_cyberrequest[$valuerequired];
        }

        return '';

    }

    /**
     * Add to a class log object
     *
     * @param Variant_Object $datain
     * @return Array
     */
    private function _addToLog($datain)
    {
        if($this->_logdata==null)
        {
            $this->_logdata = array();
        }

        $this->_logdata[] = $datain;

        return $this->_logdata;

    }

    /**
     * Log everything in the log array
     *
     * @return Bool
     */
    private function _createLog()
    {
        //only if set in admin area
        if(Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('debug',$this->_storeId))
        {
            if($this->_logdata != null)
            {
                Mage::log($this->_logdata,null,Cybersource_SOPWebMobile_Model_Source_Consts::LOGFILE);
            }
        }
        return true;
    }

    /**
     * Token action method
     */
    public function tokenAction() {
        $this->loadLayout();
        $this->renderLayout();
    }


    /**
     * oneClick postAction Processor
     */
    public function oneclickAction() {
        $product_id = $this->getRequest()->getParam('product_id');
        $qty = $this->getRequest()->getParam('qty');
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $quote = Mage::getModel('cybersourcesop/oneclick')->buildRequest($product_id,$qty);
        if ($quote->getData()) {
            //submit order with pending payment status
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();

            $order = $service->getOrder();
            Mage::getModel('customer/session')->setCustomerOrder($order);
            //load the phtml file and submit the payment to saveOrderAction
            $this->loadLayout();
            $this->renderLayout();
        }
    }

    /**
     * Retrieves customer session
     * @return mixed
     */
    public function _getSession() {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Save default token action
     */
    public function saveDefaultTokenAction() {
        $checkboxes = $this->getRequest()->getParam('checkbox');
        if(isset($checkboxes)){
            foreach ($checkboxes as $id => $state) {
                if ($state == 'on') {
                    try {
                        $token = Mage::getModel('cybersourcesop/token')->setAsDefault($id);
                    } catch (Exception $e) {
                        Mage::getSingleton('core/session')->addError(Mage::helper('cybersourcesop')->__("An error occurred while updating your default credit card token."));
                        Mage::log('Token Save Default Error: ' . $e->getMessage(), null, Cybersource_SOPWebMobile_Model_Source_Consts::LOGFILE);
                        break;
                    }
                }
            }
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('cybersourcesop')->__("Default credit card token updated successfully."));
        }
        else{
            Mage::getSingleton('core/session')->addError(Mage::helper('cybersourcesop')->__("Select the token to update."));
        }
        $this->_redirect('cybersourcesop/index/token/');
    }

    /**
     * Deletes token action
     */
    public function deleteAction(){
        $params = $this->_request->getParams();
        //Get Token.
        $token = Mage::getModel('cybersourcesop/token')->getTokenValue($params['token_id']);
        $tokenId = $token->getTokenId();
        $merchantRef = $token->getMerchantRef();
        $result = Mage::getModel('cybersourcesop/token')->createDeleteRequest($tokenId,$merchantRef);

        $session = Mage::getSingleton('core/session');

        if($result){
            $session->addSuccess(Mage::helper('cybersourcesop')->__("Saved Card sucessfully deleted."));
        }else{
            $session->addError(Mage::helper('cybersourcesop')->__("There was an error deleting your Saved Card."));
        }
        $this->_redirect('cybersourcesop/index/token/');
    }

    /**
     * Update token action
     */
    public function updateAction(){

        $tokenId= $this->getRequest()->getParam('token_id');
        $cvn = $this->getRequest()->getParam('cc-type');
        $cardNumber = $this->getRequest()->getParam('cardnumber');
        $month = $this->getRequest()->getParam('month');
        $year = $this->getRequest()->getParam('year');

        $updateData= array(
            'cc_number' => $cardNumber,
            'cc_expiration_year' => $year,
            'cc_expiration_month' => $month,
            'cc_type' => $cvn
        );
        //Get Token.
        $token = Mage::getModel('cybersourcesop/token')->getTokenValue($tokenId);
        $tokenId = $token->getTokenId();
        $merchantRef = $token->getMerchantRef();
        $result = Mage::getModel('cybersourcesop/token')->updateTokenRequest($tokenId,$merchantRef,$updateData);

        $session = Mage::getSingleton('core/session');

        if(is_string($result)){
            $session->addSuccess(Mage::helper('cybersourcesop')->__("Saved Card sucessfully updated."));
        }
        else if(is_array($result)){
            ///error here
            $session->addError(Mage::helper('cybersourcesop')->__("Failed to update token. Reason: One or more fields in the request contains invalid data (month or year). "));
        }
        else{
            $session->addError(Mage::helper('cybersourcesop')->__("There was an error updating your Saved Card."));
        }
        $this->_redirect('cybersourcesop/index/token/');
    }
}
