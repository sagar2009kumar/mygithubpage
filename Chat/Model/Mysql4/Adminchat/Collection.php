
<?php

class Mofluid_Chat_Model_Mysql4_Adminchat_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mofluid_chat/adminchat');
    }
}
