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
	
	
	public function createNewPostAction() {
		
		$blogpost = Mage::getModel('weblog/blogpost');
		$blogpost->setTitle('Code Post!');
		$blogpost->setPost('This post is created from code');
		$blogpost->save();
		
		echo 'post with ID '.$blogpost->getId() .'created';
	}
	
	
	public function editFirstPostAction() {
		$blogpost = Mage::getModel('weblog/blogpost');
		$blogpost->load(1);
		$blogpost->setTitle("The first post!");
		$blogpost->save();
		echo 'post edited';
		
	}
	
	public function deleteFirstPostAction() {
		$blogpost = Mage::getModel('weblog/blogpost');
		$blogpost->load(1);
		$blogpost->delete();
		echo 'post removed';
	}
	
	public function showAllBlogPostsAction() {
		$posts = Mage::getModel('weblog/blogpost')->getCollection();
		foreach($posts as $blogpost) {
			echo '<h3>'.$blogpost->getTitle().'</h3>';
			echo nl2br($blogpost->getPost());
		}
	}

}

?>
