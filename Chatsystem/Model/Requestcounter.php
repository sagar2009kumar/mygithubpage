<?php

class Mofluid_Chatsystem_Model_Requestcounter extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mofluid_chatsystem/requestcounter');
    }
}
