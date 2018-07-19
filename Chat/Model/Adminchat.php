<?php

class Mofluid_Chat_Model_Adminchat extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mofluid_chat/adminchat');
    }
}
