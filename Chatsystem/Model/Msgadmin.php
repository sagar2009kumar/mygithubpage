<?php

class Mofluid_Chatsystem_Model_Msgadmin extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mofluid_chatsystem/msgadmin');
        //~ $this->_init('chatsystem/msgjson');
        //~ $this->_init('chatsystem/msgtext');
    }
}

?>
