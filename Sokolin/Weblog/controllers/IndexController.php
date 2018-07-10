<?php 

class Sokolin_Weblog_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		
		echo "Hello World";

	}
	
	public function testAction() {
		
		$blogpost = Mage::getModel('weblog/blogpost');
		echo "Loading <br>";
		$blogpost->load(1);
		$data = $blogpost->getData();
		var_dump($data);
		
	}

}

?>
