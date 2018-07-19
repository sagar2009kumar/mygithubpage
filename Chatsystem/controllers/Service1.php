<?php

class Service {
	
	public function create_new_request($customerid, $message, $sender, $receiver, $file) {
		
		try {
			
			// remove this this is not part of code
			$customername = "JUNK";
			
			$requestid = 1;
			
			/**** incrementing the total request ****/
			$reqmodel = Mage::getModel('mofluid_chatsystem/requestcounter')->load($customerid);
			if($reqmodel->getId()) {
				$requestid = $reqmodel->getRequestId()+1;
				$reqmodel->setRequestId($requestid);
				$reqmodel->save();
			}else {
				$reqmodel = Mage::getModel('mofluid_chatsystem/requestcounter');
				$reqmodel->setId($customerid);
				$reqmodel->setCustomerId($customerid);
				$reqmodel->setRequestId(1);
				$reqmodel->save();
			}
			
			/**** uploading the image and getting image path ****/
			$imgpath = '';
			
			if($file) {
				$uploadres = $this->uploadImage($customerid, $requestid, $file);
				if($uploadres["status"] == 0) {return $uploadres;};
				$imgpath = $uploadres["imgpath"];
			}else {
				$imgpath = null;
			}
			
			/**** creating the json format to save in the table ****/
			
			$messagearr = array("sender"=>$sender, "receiver"=>$receiver, "imagepath"=>$imgpath ,
								"updated_at"=>Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'),
								"message"=>$message);
			$messagejson = json_encode($messagearr);
			$messagejson = '['.$messagejson.']';
			
			/**** saving the data in admin table ****/
			$adminmodel = Mage::getModel('mofluid_chatsystem/msgadmin');
			$adminmodel->setCustomerId($customerid);
			$adminmodel->setRequestId($requestid);
			$adminmodel->setCreatedAt(Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));
			$adminmodel->setUpdatedAt(Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));
			$adminmodel->setMessage($messagejson);
			$adminmodel->setMessageCount(1);
			$adminmodel->setCustomerName($customername); // remaining to set the customername
			$adminmodel->save();
			
			$id = $adminmodel->getId();
			
			$res["status"] = 1;
			$res["message"] = "success";
			$res["id"] = $id;
		}catch(Exception $e) {
			$res["status"] = 0;
			$res["message"] = "Error in creating new request".$e->getMessage();
		}
		return $res;
	}
	
	public function update_existing_request($id, $message, $sender, $receiver, $file) {
		
		try {
			/**** uploading the image and getting image path ****/
			$adminmodel = Mage::getModel('mofluid_chatsystem/msgadmin')->load($id);
			$requestid = $adminmodel->getRequestId();
			$customerid = $adminmodel->getCustomerId();
			$prevMsg = $adminmodel->getMessage();
			
			// replacing the last character of the string
			$prevMsg = substr($prevMsg, 0, -1);
			
			$imgpath = '';
			
			if($file) {
				$uploadres = $this->uploadImage($customerid, $requestid, $file);
				if($uploadres["status"] == 0) {return $uploadres;};
				$imgpath = $uploadres["imgpath"];
			}else {
				$imgpath = null;
			}
			
			/**** creating the json format to save in the table ****/
			
			$messagearr = array("sender"=>$sender, "receiver"=>$receiver, "imagepath"=>$imgpath ,
								"updated_at"=>Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'),
								"message"=>$message);
								
			$message = $prevMsg.','.json_encode($messagearr).']';
			
			/**** saving the data in admin table ****/
			
			$adminmodel->setMessage($message);
			$adminmodel->setUpdatedAt(Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));
			$adminmodel->save();
			
			$id = $adminmodel->getId();
			
			$res["status"] = 1;
			$res["message"] = "success";
			$res["id"] = $id;
		}catch(Exception $e) {
			$res["status"] = 0;
			$res["message"] = "Error in updating request".$e->getMessage();
		}
		return $res;
		
	}
	
	public function get_all_requests($customerid) {
		try {
			$customerid = ''.$customerid.'';
			$requests = array();
			
			/**** getting the collection of id, requestid and customerid ****/
			$collection = Mage::getModel('mofluid_chatsystem/msgadmin')->getCollection()->addFieldToSelect(array('id','customer_id','request_id')); 
			
			/*** filtering the collection first by customerid and then by requestid ****/
			$collection = $collection->addFieldToFilter('customer_id', array('eq'=>$customerid)); 
			
			/**** getting all the requests_id and their corresponding id ****/
			foreach($collection as $col) {
				$requests[$col->getRequestId()] =  $col->getId();
			}
			
			$res["requests_id => id "] = $requests;
			$res["customer_id"] = $customerid;
			$res["message"] = "success";
			$res["status"] = 1;
		}catch(Exception $e) {
			$res["status"] = 0;
			$res["message"] = "Failed to list <br>".$e->getMessage();
		}
		return $res;
	}
		
	
	public function get_all_message($id) {
		try {
			$adminmodel = Mage::getModel('mofluid_chatsystem/msgadmin')->load($id);
			$res["message"] = json_decode($adminmodel->getMessage());
			$res["requestid"] = $adminmodel->getRequestId();
			$res["created at"] = $adminmodel->getCreatedAt();
			$res["updated_at"] = $adminmodel->getUpdatedAt();
			$res["message count"] = $adminmodel->getMessageCount();
			$res["customer name"] = $adminmodel->getCustomerName();
			$res["id"] = $id;
			$res["status"] = 1;
		}catch(Exception $e) {
			$res["message"] = "Failed in get_all message <br> ". $e->getMessage();
			$res["status"] = 0;
		}
		return $res;
	}

	
	public function processDir($customerid, $requestid) {
		
		/**** For processing the directory ****/
		
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
	
	public function uploadImage($customerid, $requestid, $file) {
		
		$res = array();
			
		/**** getting the file parameters ****/
		$fileName = $_FILES['file']['name'];
		$fileTmpName = $_FILES['file']['tmp_name'];
		$fileSize = $_FILES['file']['size'];
		$fileError = $_FILES['file']['error'];
		$fileType = $_FILES['file']['type'];
		
		/**** separate the file by to get extension ****/
		$fileExt = explode('.', $fileName);
		$fileActualExt = strtolower(end($fileExt));
		
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
		return $res;
	}
	
	/**** specifically of no use till now ****/
	
	public function getcustpkid($customerid, $requestid) {
		try {
			
			/**** getting the id of the given customerid and requestid ****/
			
			$customerid = ''.$customerid.'';
			$requestid = ''.$requestid.'';
			
			/**** getting the collection of id, requestid and customerid ****/
			$collection = Mage::getModel('mofluid_chatsystem/msgadmin')->getCollection()->addFieldToSelect(array('id','customer_id','request_id'));  
			
			/*** filtering the collection first by customerid and then by requestid ****/
			$collection = $collection->addFieldToFilter('customer_id', array('eq'=>$customerid));
			$collection = $collection->addFieldToFilter('request_id', array('eq'=>$requestid));
			
			/**** Finally getting the id, it will be unique understand ****/
			$res["id"] = $collection->getId();
			$res["message"] = "success";
			$res["status"] = 1;
		}catch(Exception $e) {
			$res["status"] = 0;
			$res["message"] = "Failed to list <br>".$e->getMessage();
		}
		return $res;
	}
	

}
?>
