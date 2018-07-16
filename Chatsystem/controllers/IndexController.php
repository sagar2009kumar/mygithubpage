<?php 

include_once('Service1.php');

class Mofluid_Chatsystem_IndexController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		
		try {

			$service = $_GET["service"];
			$customerid = $_POST["customerid"];
			$requestid = $_POST["requestid"];
			$customername = $_POST["customername"];
			$message = $_POST["message"];
			$id = $_POST["id"];
			
			$chat_service = new Service();
			
			if($service == "createnewrequest") {
				
				$res = $chat_service->create_new_request($customerid, $message, $customername);
				echo $_GET["callback"].json_encode($res);
				
			}else if($service == "getcustpkid") {
				
				$res = $chat_service->getcustpkid($customerid, $requestid);
				echo $_GET["callback"].json_encode($res);
				
			}else if($service == "update") {
				
				$res = $chat_service->update_existing_request($id, $message);
				echo $_GET["callback"].json_encode($res);
				
			}elseif($service == "uploadImage") {
				
				if(isset($_FILES['file'])) {
					$file = $_FILES['file'];
					$res = $chat_service->uploadImage($customerid, $requestid, $file);
				}else {
					$res["message"] = "File not uploaded";
					$res["status"] = 0;
				}
				echo $_GET["callback"].json_encode($res);
				
			}else {
				
				echo "Nothing";
				
			}
			
		} catch (Exception $exc) {
			
			echo 'Exception : ' . $exc;
			
		}
	}
}

?>


