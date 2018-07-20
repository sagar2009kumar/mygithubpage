<?php
class Mofluid_Chat_Block_Adminhtml_Adminchat
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. foo_bar/adminhtml_baz
        $this->_blockGroup = 'mofluid_chat';
        $this->_controller = 'adminhtml_adminchat';
        $this->_headerText = $this->__('Adminchat');
         
        parent::__construct();

        $this->_removeButton('add');
    }
}
