<?php

class Mofluid_Paymentcheckout_Model_Paymentcheckout extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mofluid_paymentcheckout/paymentcheckout');
    }
}
