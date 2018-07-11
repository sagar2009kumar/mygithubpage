<?php

class Mofluid_Chatsystem_Model_Msgjson extends Mage_Core_Model_Abstract
{
    public function _construct() {    
        // Note that the web_id refers to the key field in your database table.
        //~ $this->_init('mofluid_chatsystem/msgadmin','customer_id');
        $this->_init('mofluid_chatsystem/msgjson');
        //~ $this->_init('chatsystem/msgtext','customer_id');
    }
}

?>
