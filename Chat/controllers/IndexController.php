<?php

include_once('webchat.php');

class Mofluid_Chat_IndexController extends Mage_Core_Controller_Front_Action {
	
	public function IndexAction() {
		
		$service = $this->getRequest()->getParam('service');
		
		if($service == 'chat'){
			$chatid = $this->getRequest()->getParam('id');
			$chat_service = new Service();
			$chat_service->chat($chatid);
		}
		
	}
	
}

?> 
