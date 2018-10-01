<?php
class Mofluid_Paymentcheckout_Model_Mysql4_Paymentcheckout extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('mofluid_paymentcheckout/payment', 'payment_method_id'); 
    }
}
