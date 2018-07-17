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
			$file = isset($_FILES['file']) ? $_FILES['file'] : null;
			$sender = $_POST["sender"];
			$receiver = $_POST["receiver"];
			
			$chat_service = new Service();
			
			if($service == "createnewrequest") {
				
				$res = $chat_service->create_new_request($customerid, $message, $sender, $receiver, $file);
				echo $_GET["callback"].json_encode($res);
				
			}elseif($service == "listreqid") {
				
				$res = $chat_service->get_all_requests($customerid);
				echo $_GET["callback"].json_encode($res);
				
			}elseif($service == "update") {
				
				$res = $chat_service->update_existing_request($id, $message, $sender, $receiver, $file);
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
				
			}elseif($service == "getallmessages") {
				
				$res = $chat_service->get_all_message($id);
				echo $_GET["callback"].json_encode($res);
				
			}
			else {
				
				echo "Nothing";
				
			}
			
		} catch (Exception $exc) {
			
			echo 'Exception : ' . $exc;
			
		}
	}
}

?>


