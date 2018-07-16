<?php

class Mofluid_Chatsystem_Model_Mysql4_Msgadmin extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct() {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('mofluid_chatsystem/msgadmin','id');
        //~ $this->_init('chatsystem/msgjson','customer_id');
        //~ $this->_init('chatsystem/msgtext','customer_id');
    }
}

?>
