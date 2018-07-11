<?php 

class Mofluid_Chatsystem_IndexController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		
		echo "Hello World";
	}
	
	public function createTextAction() {
		
		$blogpost = Mage::getModel('mofluid_chatsystem/msgtext');
		$blogpost->load(1);
		$blogpost->setMessage('hello how are you');
		$blogpost->setSender('admin');
		$blogpost->setReceiver('ram');
		$blogpost->setCustomerId('14');
		$blogpost->setImage('123');
		$blogpost->save();
		echo "<br> Testing test <br> <br>";
		$blogpost->load(1);
		$data = $blogpost->getData();
		var_dump($data);
		
	}
	
	public function createJsonAction() {
		
		$blogpost = Mage::getModel('mofluid_chatsystem/msgjson');
		$blogpost->setMessage('hello how are you');
		$blogpost->setSender('admin');
		$blogpost->setReceiver('shyam');
		$blogpost->setCustomerId('14');
		$blogpost->save();
		
		$blogpost->load(1);
		echo "<br> Testing Json<br> <br>";
		$data = $blogpost->getData();
		var_dump($data);
		
	}
	
	public function createAdminAction() {
		
		$blogpost = Mage::getModel('mofluid_chatsystem/msgadmin');
		$blogpost->load(1);
		$blogpost->setMessage('hello how are you');
		$blogpost->setSender('admin');
		$blogpost->setReceiver('ram');
		$blogpost->setCustomerId('14');
		$blogpost->setAction('hola');
		$blogpost->setCreatedAt('today');
		$blogpost->save();
		echo "<br> Testing Admin <br> <br>";
		$blogpost->load(1);
		$data = $blogpost->getData();
		var_dump($data);
		
	}
	
	public function testAllAction() {
		
		
		$this->createJsonAction();
		$this->createAdminAction();
		$this->createTextAction();
		
	}
	
	
}

?>
