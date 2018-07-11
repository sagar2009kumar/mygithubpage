<?php

class Mofluid_Chatsystem_Model_Mysql4_Msgtext extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct() {    
        // Note that the web_id refers to the key field in your database table.
        //~ $this->_init('mofluid_chatsystem/msgadmin','customer_id');
        //~ $this->_init('mofluid_chatsystem/msgjson','id');
        $this->_init('mofluid_chatsystem/msgtext','id');
        //~ $this->_init('chatsystem/msgtext','customer_id');
    }
}

?>
