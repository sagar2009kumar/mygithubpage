<?php

class Mofluid_Chat_Block_Adminhtml_Adminchat_Requestrender extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
	 public function render(Varien_Object $row)
    {
        $value = 'Request #'.$row->getData('request_id');

        return $value;
    }
}
