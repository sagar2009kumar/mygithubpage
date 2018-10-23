<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Update extends Mage_Core_Model_Abstract
{
    /**
     * Reports url..
     * @access public
     */
    public $_url = null;
    /**
     * Holds system config values.
     * @access public
     * @var array
     */
    public $_config = null;
    /**
     * Holds fields used during the On-Demand Conversion Report.
     * @access public
     * @var array
     */
    public $_fields = null;
    /**
     * Set to tru if the response from cybersource has to be logged.
     * @access public
     */
    public $_logResponse = null;
    /**
     * Holds ids whose oders will be converted during the On-Demand Conversion Report.
     * @access public
     * @var array
     */
    public $_ordersIDs = null;
    /**
     * Report parameters
     * @access public
     * @var array
     */
    public $_params = null;
    /**
     * Holds system config values.
     * @access public
     * @var Variant_Object
     */
    public $_xml = null;
    /**
     * Token conversion
     * @access public
     */
    public $_tokenConversion = null;
    /**
     * Request Id
     * @access public
     */
    public $_requestID = null;
    /**
     * Generated merchant reference
     * @access public
     */
    public $_merchantRef = null;
    /**
     * Store Id
     * @access public
     * @var string|int
     */
    public $_storeId = null;

    /**
     *  called on the cron to generate report
     */
    public function requestConversionReport()
    {
        foreach (Mage::app()->getStores() as $store) {
            $this->_storeId = $store->getStoreId();
            $this->doRequest();
        }
    }

    /**
     * Used to send request to cybersource
     */
    public function doRequest(){

        $this->getConfig();
        $this->_logResponse = $this->logResponseEnabled();

        Mage::log('### Conversion Report Started for store :'.$this->_storeId.' ###',null, Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);

        //Check if On-Demand Report is enabled, and run update of orders
        if($this->reportEnabled()){

            $this->getReportUrl();
            $this->getReportFields();
            $this->getReviewOrderIds();
            $this->getReportParams();

            try{
                $ch = curl_init($this->_url);
                curl_setopt( $ch, CURLOPT_POST, 1);
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $this->_params);
                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt( $ch, CURLOPT_HEADER, 0);
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

                //Get response from On-Demand Report
                $response = curl_exec( $ch );

                //Create SimpleXMLElement Object of response
                $this->_xml = new SimpleXMLElement($response);

                //Check if there is any orders to convert.
                if(isset($this->_xml->Conversion))
                {
                    foreach($this->_xml as $conversion)
                    {
                        //Check if Merchant Reference Number is provided.
                        if(isset($conversion['MerchantReferenceNumber'])) {

                            if(empty($conversion['MerchantReferenceNumber'])){
                                Mage::log('No Merchant Reference Number provided.', null, Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
                                continue;
                            }else{
                                $this->_requestID = (string)$conversion['RequestID'];
                                $this->_merchantRef = (string)$conversion['MerchantReferenceNumber'];
                                $newDecision = $conversion->NewDecision;

                                if(in_array($this->_merchantRef, $this->_ordersIDs))
                                {
                                    $this->updateOrderStatus($this->_merchantRef, $newDecision);
                                }
                            }
                        }
                    }
                } else {
                    Mage::log('There are no orders to convert.', null,Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
                }

            } catch (Exception $e){
                //log exception here.
                Mage::log('Error: '.$e->getMessage(), null,Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
            }
        }else{
            Mage::log('On-Demand Report Update is disabled.', null, Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
        }
        Mage::log('### Conversion Report Finished for store :'.$this->_storeId.' ###',null, Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
    }

    /**
     * Loads system config
     */
    protected function getConfig(){
        $this->_config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$this->_storeId);
    }

    /**
     * Checks if generate report is enabled
     * @return bool
     */
    protected function reportEnabled(){
        if(isset($this->_config['report_enabled'])){
            return $this->_config['report_enabled'];
        }
        return false;
    }

    /**
     * Checks if responses of On-Demand Report logging is enabled
     * @return bool
     */
    protected function logResponseEnabled()
    {
        //Check if responses of On-Demand Report logging is enabled.
        if (isset($this->_config['log_report_response'])) {
            return $this->_config['log_report_response'];
        }
        return false;
    }

    /**
     * Gets report url from system config
     */
    protected function getReportUrl()
    {
        //set url
        if (isset($this->_config['report_test'])) {
            $this->_url = $this->setReportUrl($this->_config['report_test']);
        }
    }
    /**
     * sets report url from system config
     * @param $configvaluein
     * @return mixed
     */
    protected function setReportUrl($configvaluein)
    {
        if($configvaluein == '1')
        {
            return $this->_config['report_test_url'];
        }else
        {
            return $this->_config['report_live_url'];
        }
    }

    /**
     * Downloads the report of orders converted
     */
    protected function getReportFields()
    {
        //on-demand conversion detail report timezone is always in GMT
        date_default_timezone_set('GMT');

        //Downloads the report of orders converted in the past 23 hours.
        $startDate = date('Y-m-d',strtotime('-1 days'));
        $endDate = date('Y-m-d');
        $startTime = date('H:i:s',strtotime('+1 hour'));
        $endTime = date('H:i:s');

        $this->addReportField('merchantID',$this->_config['merchant_id'],true)
            ->addReportField('username',$this->_config['username'],true)
            ->addReportField('password',$this->_config['login'],true)
            ->addReportField('startDate',$startDate,true)
            ->addReportField('startTime',$startTime,true)
            ->addReportField('endDate',$endDate,true)
            ->addReportField('endTime',$endTime,true);
    }

    /**
     * Adds report field
     * @param string $fieldname
     * @param string $value
     * @param bool $required
     * @return $this
     */
    protected function addReportField($fieldname, $value,$required=false)
    {
        //check if value is required
        if($required && empty($value))
        {
            Mage::log('Required setting missing: '.$fieldname, null,Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
        }else{
            if(!empty($value))
            {
                $this->_fields[$fieldname] = $value;
            }
        }
        return $this;
    }

    /**
     * Sets report parameters
     */
    protected function getReportParams()
    {
        $firstParam = true;

        //Creates the string of params send to the On-Demand Conversion Report URL
        foreach($this->_fields as $name => $value){
            if($firstParam){
                $this->_params .= $name.'='.$value;
                $firstParam = false;
            }else{
                $this->_params .= '&'.$name.'='.$value;
            }
        }
    }

    /**
     * Gets orders in Magento with status STATUS_PENDING_DM_REVIEW
     * @return $this
     */
    protected function getReviewOrderIds()
    {
        // Get orders in Magento with status STATUS_PENDING_DM_REVIEW
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', Cybersource_SOPWebMobile_Model_Source_Consts::STATUS_PENDING_DM_REVIEW)
            ->setPageSize($this->_config['order_pagesize']);

        $this->_ordersIDs = array();

        foreach ($orders as $order) {
            $this->_ordersIDs[] = $order->getIncrementId();
        }

        return $this;
    }

    /**
     * updates the order state/status.
     * @param string $orderID
     * @param string $decision
     * @return $this
     */
    protected function updateOrderStatus($orderID, $decision)
    {
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderID);

        //Switches on the newDecision and updates the order state/status accordingly.
        switch($decision){
            case Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_ACCEPT_STATUS:
                // Check if token must be saved?
                $payment = $order->getPayment();
                if ($payment->getCybersourcesopSaveToken()) {
                    //convert a transaction to a customer profile
                    $requestId = $this->_requestID;
                    if ($requestId) {
                        $tokenId = Mage::getModel('cybersourcesop/token')->createConvertOrderRequest($requestId,$this->_merchantRef,$this->_storeId);
                        if ($tokenId) {
                            //create the token
                            $newToken = Mage::getModel('cybersourcesop/token');
                            try {
                                $newToken->setTokenId($tokenId)
                                    ->setCcNumber($payment->getCcLast4())
                                    ->setCcExpiration($payment->getCcExpMonth() . '-' . $payment->getCcExpYear())
                                    ->setCustomerId($order->getCustomerId())
                                    ->setCcType($payment->getCcType())
                                    ->setMerchantRef($this->_merchantRef)
                                    ->save();
                            } catch (Exception $e) {
                                $message = Mage::helper('cybersourcesop')->__('There was an error saving the customer token on the converted order: %s',$orderID);
                                Mage::log($message, null, Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
                                return $this;
                            }
                        } else {
                            $message = Mage::helper('cybersourcesop')->__('There was an error converting the order and retrieving a token ID: %s',$tokenId);
                            Mage::log($message, null, Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
                            return $this;
                        }
                    }
                }

                try{
                    //Set transaction ID and register Authorization.
                    $payment->setTransactionId($this->_requestID)
                        ->registerAuthorizationNotification($order->getBaseGrandTotal());
                    $order->sendNewOrderEmail();
                    $order->save();
                }catch (Exception $e){
                    $message = Mage::helper('cybersourcesop')->__('There was an error setting the transaction ID: %s. ',$this->_requestID);
                    Mage::log($message.$e->getMessage(), null, Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
                }
                break;
            case Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_REJECT_STATUS:
                //Set order status to canceled
                $message = Mage::helper('cybersourcesop')->__('Order has been Rejected.');
                // register Cancellation
                $order->registerCancellation($message)->save();
                break;
            default:
                //log error
                $message = Mage::helper('cybersourcesop')->__('There was an error updating the converted order: %s',$orderID);
                Mage::log($message, null, Cybersource_SOPWebMobile_Model_Source_Consts::CONVERSION_LOG, $this->_logResponse);
                break;
        }
    }

    /**
     * Updates pending payments and sets them to state cancelled.
     */


    public function updatePendingPayments() {

        foreach (Mage::app()->getStores() as $store) {
            $storeId = $store->getStoreId();

            $log_enabled = $this->isCancelLogEnabled($storeId);
            Mage::log('### Cancel Order Cron Started for store :'.$storeId.' ###',null, Cybersource_SOPWebMobile_Model_Source_Consts::UPDATE_LOG, $log_enabled);

            $orders = Mage::getModel('sales/order')->getCollection();
            //join payment onto order object to access method instance
            $orders->getSelect()->join(
                array('p' => $orders->getResource()->getTable('sales/order_payment')),
                'p.parent_id = main_table.entity_id',
                array()
            );

            try{
                $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$storeId);
                if (isset($config['update_cron']) && $config['update_cron'] == 1) {
                    $limitAmount = (int)$config['update_cron_limit'];
                    //filter cybersource pending payments only, thats older than 10mins.
                    $orders->addFieldToFilter('method', 'cybersourcesop')
                        ->addFieldToFilter('status', 'pending_payment')
                        ->addFieldToFilter('store_id', $storeId)
                        ->addFieldToFilter('created_at', array(
                            'lt' =>  new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -'10:00' HOUR_MINUTE)")))
                        ->getSelect()
                        ->limit($limitAmount);
                    //iterate through orders and set state to cancelled.
                    foreach ($orders as $order) {
                        $message = Mage::helper('cybersourcesop')->__('Order has been canceled.');
                        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $message);
                        $order->cancel();
                        $order->save();
                        $order->cronRunning();
                        $order->sendOrderUpdateEmail(true);
                        Mage::log('Order Canceled: '.$order->getIncrementId(),null,Cybersource_SOPWebMobile_Model_Source_Consts::UPDATE_LOG, $log_enabled);
                    }
                }else{
                    Mage::log('Update Orders disabled in Config.',null,Cybersource_SOPWebMobile_Model_Source_Consts::UPDATE_LOG, $log_enabled);
                }
            }catch(Mage_Core_Exception $e){
                Mage::log($e->getMessage(),null, Cybersource_SOPWebMobile_Model_Source_Consts::UPDATE_LOG, $log_enabled);
            }
            Mage::log('### Cancel Order Cron Finished for store :'.$storeId.' ###',null, Cybersource_SOPWebMobile_Model_Source_Consts::UPDATE_LOG, $log_enabled);
        }
    }

    /**
     * Checks if the cancel_log is enabled.
     * @param string $storeId
     * @return bool
     */
    public function isCancelLogEnabled($storeId){
        $config = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig(null,$storeId);

        if(isset($config['cancel_log_enabled']) && $config['cancel_log_enabled']){
            return true;
        }
        return false;
    }
}
