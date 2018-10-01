<?php

class Mofluid_Paymentcheckout_Model_Mysql4_Paymentcheckout extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('mofluid_paymentcheckout/payment', 'payment_method_id');
    }
}
