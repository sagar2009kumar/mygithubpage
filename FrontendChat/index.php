<?php $id = intval($_GET['id']); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type = "text/javascript">

/**** counter variable to make the div all across the chatboxes ****/
var counter = 0;

/**** id variable to be accesssed all the time ****/
var id = <?php echo $id ?>;

/**** receiver name to be used all the time ****/
var recv = '';

/**** product id used for the messages ****/
var productId = 0;

/**** searched product id for the messages ****/
var currProductId = 0;

/**** is the product selected is right ****/
var isProductRight = false;

/**** search product image path ****/
var searchProductImageUrl = '';

function getJsonFromUrl(url) {
	
	// Only a template from getting the json from url using get method
	
	/**** preparing the XMLHttpRequest ****/
	if (window.XMLHttpRequest) {
		/**** code for IE7+, Firefox, Chrome, Opera, Safari ****/
		xmlhttp=new XMLHttpRequest();
	} else { 
		/**** code for IE6, IE5 ****/
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	/**** if the request is  processed ****/
	xmlhttp.onreadystatechange = function() {
		
		/**** if the request got success by indicating the status of 200 ****/
		if (this.readyState==4 && this.status==200) {
			/**** receiving the request response from the server as json format ****/
			alert(this.responseText);
		}
	}
	
	/**** send the xmlhttp requests ****/
	xmlhttp.open("GET",url,true);
	xmlhttp.send();
}

/**** function to reload the chat ****/

function reloadChat() {
	
	$("#chat-body-messages").html("");
	
	var url = "http://127.0.0.1/soko/index.php/sokochat/?service=getallmessages&id="+id;
	var response = '';
	
	/**** preparing the XMLHttpRequest ****/
	if (window.XMLHttpRequest) {
		/**** code for IE7+, Firefox, Chrome, Opera, Safari ****/
		xmlhttp=new XMLHttpRequest();
	} else { 
		/**** code for IE6, IE5 ****/
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	/**** if the request is  processed ****/
	xmlhttp.onreadystatechange = function() {
		
		/**** if the request got success by indicating the status of 200 ****/
		if (this.readyState==4 && this.status==200) {
			/**** receiving the request response from the server as json format ****/
			response =  this.responseText;
			setChatBody(response);
		}
	}
	
	/**** send the xmlhttp requests ****/
	xmlhttp.open("GET",url,true);
	xmlhttp.send();
}

/**** function to set the search product description ****/

function setSearchProductDescription(response) {
	
	/**** receiving the request response from the server as json format ****/
	var res = JSON.parse(response);
	
	if(res["status"]==1) {
		/**** if the request response with success then display the name ****/
		$('#description').html("");
		$('#description').append(res["product_name"]);
		// result of the image 
		searchProductImagePath = res["image_path"];
		searchProductImagePath = "http://sokolin.ebizontech.biz/media/catalog/product/5/9/59739_zoom.jpg"; 
		currProductId = productId;
		isProductRight = true;
	}else {
		/**** if the request response is false then display message ****/
		$('#description').html("");
		isProductRight = false;
		currProductId = '';
		$('#description').append(res["message"]);
	}
}

/**** function to query the product item ****/

function queryProductIdImage() {
	
	/**** search url for the query of image ****/
	var searchUrl = "http://127.0.0.1/soko/index.php/sokochat/?service=query&productid="+productId;
	
	/**** preparing the XMLHttpRequest ****/
	if (window.XMLHttpRequest) {
		/**** code for IE7+, Firefox, Chrome, Opera, Safari ****/
		xmlhttp=new XMLHttpRequest();
	} else { 
		/**** code for IE6, IE5 ****/
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	/**** if the request is  processed ****/
	xmlhttp.onreadystatechange=function() {
		
		/**** if the request got success by indicating the status of 200 ****/
		if (this.readyState==4 && this.status==200) {
			
			setSearchProductDescription(this.responseText);
			
		}
		
	}
	
	/**** send the xmlhttp requests ****/
	xmlhttp.open("GET",searchUrl,true);
	xmlhttp.send();
}

/**** if the sender is admin and message is text ****/

function addTextToAdmin(message, time) {
	
	if((/\S/.test(message))) {
		/**** converting the message to base64 ****/
		message = atob(message);
		
		/**** add text to the admin ****/
		counter++;
		var temp = "single-message"+counter;
		var temp2 = '#single-message'+counter;
		
		/**** appending the text to the div in the chat-body ****/
		$('#chat-body-messages').append($('<div class = "single_message" id ='+temp+'>'));
		$(temp2).append($('<div class = "single_message_text admin">').append(message));
		$(temp2).append($('</div>'));
		$(temp2).append($('<div class = "single_message_time">').append(time).append('</div>'));
	}
	
}

/**** if the sender is admin and message is image ****/

function addImageToAdmin(imagePath, time) {
	
	/**** add image to the admin ****/
	if(imagePath!='') {
		/**** appending the image to the div if present ****/
		counter++;
		temp = "single-message"+counter;
		temp2 = '#single-message'+counter;
		$('#chat-body-messages').append($('<div class = "single_message" id ='+temp+'>'));
		$(temp2).append($('<div class = " imgbox imgadmin">').append('<img src = '+imagePath+' class = "setImageSize">'));
		$(temp2).append($('</div>'));
		$(temp2).append($('<div class = "single_message_time">').append(time).append('</div>'));
	}
	
}

/**** add message to the admin ****/

function addMessageToAdmin(imagePath, message, time) {
	
	addTextToAdmin(message, time);
	addImageToAdmin(imagePath,time);
	
}

/**** if the sender is client and message is text ****/

function addTextToClient(message, time) {
	
	if((/\S/.test(message))){
		/**** converting the message to base64 ****/
		message = atob(message);
		
		/**** add text to the client ****/
		counter++;
		var temp = "single-message"+counter;
		var temp2 = '#single-message'+counter;
		
		/**** appending the text to the div in the chat-body ****/
		$('#chat-body-messages').append($('<div class = "single_message" id ='+temp+'>'));
		$(temp2).append($('<div class = "single_message_text client">').append(message));
		$(temp2).append($('</div>'));
		$(temp2).append($('<div class = "single_message_time">').append(time).append('</div>'));
	}
	
}

/**** if the sender is client and message is image ****/

function addImageToClient(imagePath, time) {
	
	/**** add image to the client ****/
	if(imagePath!='') {
		/**** appending the image to the div if present ****/
		counter++;
		temp = "single-message"+counter;
		temp2 = '#single-message'+counter;
		$('#chat-body-messages').append($('<div class = "single_message" id ='+temp+'>'));
		$(temp2).append($('<div class = " imgbox imgclient">').append('<img src = '+imagePath+' class = "setImageSize">'));
		$(temp2).append($('</div>'));
		$(temp2).append($('<div class = "single_message_time">').append(time).append('</div>'));
	}
	
}

/**** add message to the client ****/

function addMessageToClient(imagePath, message, time) {
	
	addTextToClient(message, time);
	addImageToClient(imagePath, time);
	
}

/**** function to set the data to the chat-body div ****/

function setTemplateData(templateData) {
	
	if(templateData) {
		var msgarr = templateData;
		for(var eachMsg in msgarr) {
			var senderIsAdmin = false, message = '', imagePath = '', time = '';
			message = msgarr[eachMsg]['message'];
			if(msgarr[eachMsg]['sender'] == 'admin') senderIsAdmin = true;
			if(msgarr[eachMsg]['imagepath']!='') imagePath = msgarr[eachMsg]['imagepath'];
			var dateString = msgarr[eachMsg]['created_at'].split(' '); time = dateString[1];
			// Remaining ::: to set the time to u.s time
			if(senderIsAdmin) { 
				addMessageToAdmin(imagePath, message, time);
			}else {
				addMessageToClient(imagePath, message, time);
			}
		}	
	}
}

/**** setting the chat body of the response ****/

function setChatBody(response) {
	
	/**** receiving the request response from the server as json format ****/
	var res = JSON.parse(response);
	if(res["status"] == 1) {
	
		/**** setting the name of the customer ****/
		recv = res["customerName"];
		
		/**** getting the messages with the help of templateData variable ****/
		var templateData = res["message"];
		setTemplateData(templateData);
		
		/**** set the scrollbar to the bottom of the screen ****/
		var objDiv = document.getElementById("chat_body");
		objDiv.scrollTop = objDiv.scrollHeight;
		
	} else {
		
		alert('Maybe Invalid id \n error in getting messages'+res["messages"]);
		
	}
}

function hitApiForTextUpdate(message) {
	
	/**** api for the message update ****/
	var url = 'http://127.0.0.1/soko/index.php/sokochat/?service=update&id='+id+'&sender=admin&receiver='+recv+'&productid='+productId+'&message='+message;
	
	/**** hit the ajax for the information update ****/
	$.ajax({
		'url': url, 
		//~ 'type': 'POST',
		//~ 'dataType': 'json', 
		//~ 'data': {itemid: xx}, 
		'success': function(data) {
		  // what happens if the request was completed properly
		},
		'error': function(data) {
		  // what happens if the request fails.
		}
	});
	
}

function sendText() {
	
	/**** getting the message value ****/
	var message = $('#message').val();
	
	/**** converting the message to base64 ****/
	message = btoa(message);
	
	/**** getting the date and time ****/
	var time = new Date().toLocaleString();
	var timearr = time.split(' ');
	time = timearr[1];
	
	/**** check whether the message contains the data or not ****/
	if((/\S/.test(message))){
		/**** adding the message to the div ****/
		addTextToAdmin(message, time); productId='';
		/**** hit the api for updating in the message ****/
		hitApiForTextUpdate(message);
		/**** setting the data to nothing. ****/
		$('#message').val('');
	}
	
	/**** setting the scrollbar to bottom ****/
	var objDiv = document.getElementById("chat_body");
	objDiv.scrollTop = objDiv.scrollHeight;
	
}

/**** function to hit the api for sending the product image ****/ 
function hitApiForSendProductImage() {
	
	var url = 'http://127.0.0.1/soko/index.php/sokochat/?service=update&id='+id+'&sender=admin&receiver='+recv+'&productid='+currProductId+'&message=';
	
	$.ajax({
		'url': url, 
		'type': 'GET',
		//~ 'dataType': 'json', 
		//~ 'data': {itemid: xx}, 
		'success': function(data) {
		  // what happens if the request was completed properly
		},
		'error': function(data) {
		  // what happens if the request fails.
		}
	});
}

/**** function to add the searched image ****/
function addImageForSearchedProduct() {
	/**** getting the date and time ****/
	var time = new Date().toLocaleString();
	var timearr = time.split(' ');
	time = timearr[1];
	
	addImageToAdmin(searchProductImagePath, time);
	
}

/**** function to send the searched product image ****/

function sendSearchProductImage() {
	
	if(isProductRight) {
		hitApiForSendProductImage();
		addImageForSearchedProduct();
		
		/**** set the scrollbar to the bottom of the screen ****/
		var objDiv = document.getElementById("chat_body");
		objDiv.scrollTop = objDiv.scrollHeight;
	}
}

$(function() {
	
	//~ var timeout = setInterval(reloadChat, 500);
	/**** for reloading the chat system but actually it don't do that  ****/ 
	reloadChat();
	
	/**** getting the image of the product and show in the search ****/
	var searchKey = document.getElementById("search");
	var timeout = null;
	
	searchKey.onkeyup = function (e) {
		clearTimeout(timeout);
		timeout = setTimeout(function () {
			productId = searchKey.value;
			if (productId.length==0) { 
			   document.getElementById("search").innerHTML="";
			   $('#description').html("");
			   isProductRight = false;
			   currProductId = '';
			   productId = '';
			   return;
			}
			queryProductIdImage();
	  }, 1000);
	};
	
})
	

</script>



<!DOCTYPE html>
<html>
	<head>
		<title>Sokolin Chat </title>
		<link rel = "stylesheet" href = "chat.css">
		<link rel="shortcut icon" href="http://sokolin.ebizontech.biz/skin/frontend/sokolin/granada/favicon.ico" type="image/x-icon">
		<link rel="icon" href="http://sokolin.ebizontech.biz/skin/frontend/sokolin/granada/favicon.ico" type="image/x-icon">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	</head>
	<body>
		<div class = "split left" >
			<div class = "top-bar">
				<div class = "chatsystem_headline">Admin Chatsystem </div>
				<div class = "search_request_box" > 
					<div class = "request_logo">
						Requests
					</div>
				</div>
				<div class = "original_requests">
					<ul id = "dynamicaddedlist">
						
					</ul>
				</div>
			</div>
			
		</div>
		<div class = "split right" >
			<div class = "top-bar"></div>
			<div class = "chat-body" id = "chat_body">
				<ul id = "chat-body-messages">
					
				</ul>
			</div>
		</div>
	</body>
	<footer>
		<div class = "split_footer left_footer" >
			<div class = "bottom-bar" >
				<input type="text" placeholder='search id...' name="search" class = "search_box" id = "search">
				<input type = "button" value = "send" id = "sendMe" class = "search_button" onclick = "sendSearchProductImage()" >
				<div id = "description" class = "product_description" ></div>
			</div>
			
		</div>
		<div class = "split_footer right_footer" >
			<div class = "bottom-bar">
				<div class = "write-text-box">
					<textarea class = "text-box"placeholder = 'write something here ... ' name = "message" id = "message"></textarea>
	<!--
					<input type = "file" name = "file" class = "file_upload" id = "file_upload">
	-->
				</div>
				<div>
					<input type = "button" value = "submit" id = "clickMe" onclick="sendText()" class = "clickMe">
				</div>
			</div>
		</div>
	</footer>
</html>
