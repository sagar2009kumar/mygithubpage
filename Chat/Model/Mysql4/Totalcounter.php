<?php

class Mofluid_Chat_Model_Mysql4_Totalcounter extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('mofluid_chat/totalcounter', 'customer_id');
        $this->_isPkAutoIncrement = false; // for updating without auto-increment
    }
}
