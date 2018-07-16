<?php

class Mofluid_Chatsystem_Model_Mysql4_Requestcounter extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('mofluid_chatsystem/requestcounter', 'id');
        $this->_isPkAutoIncrement = false; // for updating without auto-increment
    }
}
