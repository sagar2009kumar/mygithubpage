<?php 
	
	define('HOST','localhost');
	define('USERNAME', 'root');
	define('PASSWORD','admin123');
	define('DB','magento19');

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
	
	public function processDir($customerid, $requestid) {
		$res = array();
		
		try {
			/**** getting variend object *****/
			$io = new Varien_Io_File();
			$basedir = Mage::getBaseDir('media');
			
			/**** Create Chatsystem dir ****/
			$chatbasedir = $basedir.'/Chatsystem';
			if (!$io->fileExists($chatbasedir, false)) {
				$io->mkdir($chatbasedir);
			}
			
			/**** Create Customerid dir ****/
			$idbasedir = $chatbasedir.'/cust_'.$customerid;
			if (!$io->fileExists($idbasedir, false)) {
				$io->mkdir($idbasedir);
			}
			
			/**** Create Request dir *****/
			$requestbasedir = $idbasedir.'/req_'.$requestid;
			if (!$io->fileExists($requestbasedir, false)) {
				$io->mkdir($requestbasedir);
			}
			
			/**** returning curr dir ****/
			$res["status"] = 1;
			$res["message"] = "success";
			$res["dir"] = $requestbasedir.'/';
		}catch(Exception $e) {
			/*** status = 0 for failure ***/
			$res["status"] = 0;
			$res["message"] = $e->getMessage();
		}
		
		return $res;
	}
	
	public function uploadImageAction() {
		
		$res = array();
		
		if(isset($_FILES['file'])) {
			
			/**** getting the post parameters ****/
			$customerid = $_POST['customerid'];
			$requestid = $_POST['requestid'];
			
			$file = $_FILES['file'];
			
			/**** getting the file parameters ****/
			$fileName = $_FILES['file']['name'];
			$fileTmpName = $_FILES['file']['tmp_name'];
			$fileSize = $_FILES['file']['size'];
			$fileError = $_FILES['file']['error'];
			$fileType = $_FILES['file']['type'];
			
			/**** separate the file by to get extension ****/
			$fileExt = explode('.', $fileName);
			$fileActualExt = strtolower(end($fileExt));
			
			/**** only these types are allowed ****/
			$allowed = array('jpg','jpeg','png','pdf');
			
			if(in_array($fileActualExt,$allowed)) {
				if($fileError == 0) {
					/**** getting the unique name for the customer ****/
					$fileNameNew = uniqid().".".$fileActualExt;
					
					/**** getting the name for the required directory ****/
					$tempDir = $this->processDir($customerid, $requestid);
					
					if($tempDir["status"] == 0 ) {
						$res["message"] = $tempDir["message"];
						$res["status"] = 0;
					}else {
						$fileDestination = $tempDir["dir"].$fileNameNew;
						/**** moving the required file to required folder ****/
						move_uploaded_file($fileTmpName, $fileDestination);
						$res["message"] = "success";
						$res["status"] = 1;
						$res["imgpath"] = $fileDestination;
					}
				}else {
					$res["message"] = "There was error uploading file Maybe file format not supported.";
					$res["status"] = 0;
				}
			}else {
				$res["message"] = "You cannot upload this type of file";
				$res["status"] = 0;
			}
		}else {
			$res["message"] = "File not uploaded";
			$res["status"] = 0;
		}
		return $res;
	}
	
	public function magicAction() {
		$res = array();
		$blogpost = Mage::getModel('mofluid_chatsystem/msgtext');
		$blogpost->load(1);
		$blogpost->setMessage('hello how are you');
		$blogpost->setSender('admin');
		$blogpost->setReceiver('ram');
		$blogpost->setCustomerId('14');
		$getImagePath = $this->uploadImageAction();
		if($getImagePath["status"] == 1) 
			$blogpost->setImage($getImagePath["imgpath"]);
		$blogpost->save();
		$blogpost->load(1);
		$path = $blogpost->getImage();
		$data = $blogpost->getData();
		var_dump($data);
		//~ $url = Mage::getBaseUrl('media');
		//~ $url = $url.'Chatsystem/'.'cust_56/'.'req_23/'.'5b4742ba2476a.jpg';
		//~ echo "<img src = ".$url.">";
	}
}

?>


