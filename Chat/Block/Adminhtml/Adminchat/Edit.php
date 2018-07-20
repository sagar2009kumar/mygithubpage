<?php
class Mofluid_Chat_Block_Adminhtml_Adminchat_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     */
    public function __construct()
    {  
        $this->_blockGroup = 'mofluid_chat';
        $this->_controller = 'adminhtml_adminchat';

        parent::__construct();
   
        //~ $this->_updateButton('save', 'label', $this->__('Save Adminchat'));
        //~ $this->_updateButton('delete', 'label', $this->__('Delete Adminchat'));
    }  
     
    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {  
        //~ if (Mage::registry('mofluid_chat')->getId()) {
            //~ return $this->__('Edit Adminchat');
        //~ }  
        //~ else {
            //~ return $this->__('New Adminchat');
        //~ }  
    }  
}
