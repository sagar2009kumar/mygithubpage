<?php

class Mofluid_Paymentcheckout_Model_Mysql4_Paymentcheckout_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mofluid_paymentcheckout/payment');
    }
}
