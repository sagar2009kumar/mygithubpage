
<?php

class Mofluid_Chatsystem_Model_Mysql4_Requestcounter_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mofluid_chatsystem/requestcounter');
    }
}
