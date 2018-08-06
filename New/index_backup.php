<?php $id = intval($_GET['chatid']); ?>

<html>
	<head>
		<title>Sokolin Chat </title>
		<link rel = "stylesheet" href = "chat.css">
		<link rel="shortcut icon" href="http://sokolin.ebizontech.biz/skin/frontend/sokolin/granada/favicon.ico" type="image/x-icon">
		<link rel="icon" href="http://sokolin.ebizontech.biz/skin/frontend/sokolin/granada/favicon.ico" type="image/x-icon">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type = "text/javascript">

// http links used in the programs //

/**** Admin Image ****/
var adminImage = "http://sokolin.ebizontech.biz/skin/frontend/base/default/images/media/sokolin-logo.jpg";
var altAdminImage = "Sokolin Fine and Rare Wines | sokolin.com";

/**** For getting messages ****/
var MessageLinkTemplate = "http://sokolin.ebizontech.biz/index.php/mofluid119/?service=getallmessages&chatid=30";

/**** For updating messages ****/
var updateMessageLinkTemplate = "http://sokolin.ebizontech.biz/index.php/mofluid119?service=update&chatid=30&sender=ram&receiver=admin&productid=18799&chatmessage=c2hlIGlzIGJvcmluZw==";

/**** For creating New Request ****/
var createMessageLinkTemplate = "http://sokolin.ebizontech.biz/index.php/mofluid119?service=createnewrequest&customerid=55&sender=ram&receiver=admin&chatmessage=c2hlIGlzIGJvcmluZw==";

/**** For querying product id ****/
var queryProductIdLinkTemplate = "http://sokolin.ebizontech.biz/index.php/mofluid119?service=query&productid=18769";

/**** Customer Image ****/
var customerImage = "http://sokolin.ebizontech.biz/skin/frontend/base/default/images/media/sokolin-logo.jpg";
var altcustomerImage = "Sokolin Fine and Rare Wines | sokolin.com";

var MessageLinkDynamic = "http://sokolin.ebizontech.biz/index.php/mofluid119/?service=getallmessages&chatid=";
var queryProductIdLinkDynamic = "http://sokolin.ebizontech.biz/index.php/mofluid119?service=query&productid=";

MessageLinkDynamic = "http://127.0.0.1/magento19/index.php/mofluid119/?service=getallmessages&chatid=";

// variables used in the program //

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

/**** variable customerid to get all the requests ****/
var customerId = 0;

/**** variable product description to describe product desc ****/
var prodDesc = '';

/**** variable for holding the product name ****/
var productName = '';

/**** variable to hold the path of the sent file ****/
var filePath = '';

// Template used in the program IMPORTANT :: NOT USED ANYWHERE // 

// for getting the message from url //

function getJsonFromUrl(url) {
	
	// Only a template for getting the json from url using get method
	
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

// hitting the ajax //

function hitAjax(AnyUrl) {
	
	/**** Making form data to hold the file ****/
	/**** here the attribute is "file" for file****/
	/**** You can make "video" for video ****/
	/**** e.g. fd.append("video", file) ****/
	/**** here file is the file which is loaded by button ****/
	
	var fd =  new FormData();
	fd.append("file", file);
	
	$.ajax({
		  url: AnyUrl,
		  type: "POST",
		  data: fd,
		  processData: false,  // tell jQuery not to process the data
		  contentType: false   // tell jQuery not to set contentType
		}).done(function( data ) {
			console.log("Output file. ");
		});	
}

// for updating the id when something is put into it //

function onKeyUP() {
	
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
	
}

// End of the template //

// start of the main program // 

$(function() {
	
	setChat();
	
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

// end main program //

// starts helper function //

function getDateAndTime() {
	
	// currently returning only time not date
	var time = new Date().toLocaleString();
	var timearr = time.split(' ');
	time = timearr[1];
	
	return time;
}

function scrollDivToBottom(div_id) {
	
	div_id = 'chat_body';
	
	/**** set the scrollbar to the bottom of the screen ****/
	var objDiv = document.getElementById(div_id);
	objDiv.scrollTop = objDiv.scrollHeight;
	
}

function setDataToDivInnerHTML(div_id, value) {
	
	div_id = 'cust_name';
	
	document.getElementById(div_id).innerHTML = value;
	
}

// ends helper function

// starts functions used in program //

/**** function to set the chat_body div ****/

function setDataToChatBodyDiv(msgArr) {
	
		var message = '', time = '', imagePath = '', videoPath = '', prodPath = '', prodDesc = '', prodId = '', prodName = '', senderIsAdmin = false;
		
		message = msgArr['message'];
		time = getDateAndTime();
		imagePath = msgArr['imagepath'];
		videoPath = msgArr['videopath'];
		prodId = msgArr['productid'];
		prodName = msgArr['productname'];
		prodDesc = msgArr['productdescription'];
		prodPath = msgArr['prodpath'];
		senderIsAdmin = (msgArr['sender'] == 'admin') ? true : false;
		
		prodPath = "http://127.0.0.1/magento19/media//Chat/cust_55/req_1/5b65e935df59c.jpg";
		
		// Remaining ::: to set the time to u.s time
			if(prodId!='') {
				//if product id is present then sender is definitely admin IMPORTANT
				addProductImageToAdmin(prodPath, productName, prodDesc, time);
			}else if(senderIsAdmin) { 
				if(videoPath!='')
					addVideoToAdmin(videoPath ,time);
				addMessageToAdmin(imagePath, message, time);
			}else {
				if(videoPath!='')
					addVideoToClient(videoPath, time);
				addMessageToClient(imagePath, message, time);
			}
		
}

/**** function to set the data to the chat-body div ****/

function setTemplateData(templateData) {
	
	if(templateData) {
		var msgarr = templateData;
		var cnt = 0;
		for(var eachMsg in msgarr) {
			setDataToChatBodyDiv(msgarr[eachMsg]);
		}	
	}else {
		alert('Message not retrieved');
	}
}

/**** setting the chat body of the response ****/

function setChatBody(response) {
	
	/**** receiving the request response from the server as json format ****/
	var res = JSON.parse(response);
	if(res["status"] == 1) {
	
		/**** setting the name of the customer ****/
		recv = res["customerName"];
		
		/**** setting the name of the customer ****/
		setDataToDivInnerHTML('cust_name', recv);
		
		/**** setting the customer id ****/
		customerId = res["customerId"];
		
		/**** getting the messages with the help of templateData variable ****/
		var templateData = res["message"];
		setTemplateData(templateData);
		
		/**** set the scrollbar to the bottom of the screen ****/
		scrollDivToBottom('chat_body');
		
	} else {
		
		alert('Maybe Invalid id \n error in getting messages'+res["messages"]);
		
	}
}

/**** function to reload the chat ****/

function setChat() {
	
	/**** for reloading the chat system but actually it don't do that  ****/ 
	/**** var timeout = setInterval(setChat, 500); ****/
	
	$("#chat-body-messages").html("");
	
	var url = MessageLinkDynamic+id;
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

/********************************** SET CHAT ENDS HERE ******************************************************/

/********************************** API's HIT BY ADMIN STARTS Here ***********************************************/

/**** function to set the search product description ****/

function setSearchProductDescription(response) {
	
	/**** receiving the request response from the server as json format ****/
	var res = JSON.parse(response);
	
	if(res["status"]==1) {
		
		/**** if the request response with success then display the name ****/
		$('#description').html(""); productName = res["product_name"];
		$('#description').append(productName);
		
		// set the attribute for the image
		searchProductImagePath = res["image_path"];
		searchProductImagePath = "http://sokolin.ebizontech.biz/media/catalog/product/5/9/59739_zoom.jpg"; 
		currProductId = productId;
		prodDesc = res["product_description"];
		isProductRight = true;
		
	}else {
		/**** if the request response is false then display message ****/
		$('#description').html(""); isProductRight = false; currProductId = '';
		$('#description').append(res["message"]);
	}
	
}

/**** function to query the product item ****/

function queryProductIdImage() {
	
	/**** search url for the query of image ****/
	var searchUrl = queryProductIdLinkDynamic+productId;
	
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

/**** function to hit the api for sending the product image ****/ 
function hitApiForSendProductImage(productDescription) {
	
	var url = 'http://sokolin.ebizontech.biz/index.php/mofluid119/?service=update&chatid='+id+'&sender=admin&receiver='+recv+'&productid='+currProductId+'&chatmessage=';
	
	$.ajax({
		'url': url, 
		'type': 'GET',
		'success': function(data) {
		  // what happens if the request was completed properly
		  console.log('Yippee !! Product Image has been sent');
		},
		'error': function(data) {
		  // what happens if the request fails.
		  console.log('Oops !! Image not sent.');
		}
	});
}

/**** function to trim the text for the searched product ****/
function trimTextForSearchedProduct() {
	
	var trimmedDesc = prodDesc;
	
	/**** Trimming the data ****/
	if(trimmedDesc.length > 195){
		trimmedDesc = prodDesc.substring(0, 195);
		trimmedDesc += ' ...';
	}
	
	return trimmedDesc;
	
}

/**** function to send the searched product image ****/

function sendSearchProductImage() {
	
	if(isProductRight) {
		
		var tempDesc = btoa(trimTextForSearchedProduct());
		hitApiForSendProductImage(tempDesc);
		// comment out.
		addImageForSearchedProduct(tempDesc);
		
		scrollDivToBottom('chat_body');
	}
	
}


function hitApiForTextUpdate(message) {
	
	/**** api for the message update ****/
	var url = 'http://sokolin.ebizontech.biz/index.php/mofluid119/?service=update&chatid='+id+'&sender=admin&receiver='+recv+'&productid='+productId+'&chatmessage='+message;
	
	/**** hit the ajax for the information update ****/
	$.ajax({
		'url': url, 
		'success': function(data) {
		  // what happens if the request was completed properly
		  console.log('Yipee !! Message Sent');
		},
		'error': function(data) {
		  // what happens if the request fails.
		  console.log('Oops !! Message Not sent');
		}
	});
	
}

function sendText() {
	
	/**** getting the message value ****/
	var message = $('#message').val();
	
	/**** converting the message to base64 ****/
	message = btoa(message);
	
	/**** getting the date and time ****/
	var time = getDateAndTime();
	
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
	scrollDivToBottom('chat_body');
	
}

/**** function to send the file to the server ****/
function hitApiForSendFile() {

	var fileurl = 'http://127.0.0.1/soko/index.php/mofluid119/?service=update&chatid='+id+'&sender=admin&receiver='+recv+'&productid=&chatmessage=';
	var file = document.getElementById("file_to_send").files[0];
	if(file) {
		
		/**** getting the date and time ****/
		var time = getDateAndTime();
		addImageToAdmin(filePath, time);
		var fd =  new FormData();
		fd.append("file", file);
		
		$.ajax({
			  url: fileurl,
			  type: "POST",
			  data: fd,
			  processData: false,  // tell jQuery not to process the data
			  contentType: false   // tell jQuery not to set contentType
		}).done(function( data ) {
			console.log("File submitted. ");
		});
		
		/**** set the scrollbar to the bottom of the screen ****/
		scrollDivToBottom('chat_body');
		document.getElementById("file_to_send").value = '';
	}
}

/**** function to send the file reader ****/
function setFileReader() {
	
	var file = document.getElementById("file_to_send").files[0];
	
	/**** set the reader variable ****/
	var reader =  new FileReader();
	reader.readAsDataURL(file);
	var file    = document.getElementById("file_to_send").files[0];
	var reader  = new FileReader();
	
	if (file) {
		reader.readAsDataURL(file);
	}
	
	reader.addEventListener("load", function () {
		filePath =reader.result;
	}, false);
	
}

/************************************* API's ends here ******************************************************/

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
		$(temp2).append($('<div class = "blank_box_text_admin"> &nbsp</div> '));
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
		var toDisplayImage = '<div class = "image_plus_desc"><div class = "imgadmin"><img src = '+imagePath+' class = "setImageSize"></div>';
		temp = "single-message"+counter;
		temp2 = '#single-message'+counter;
		$('#chat-body-messages').append($('<div class = "single_message" id ='+temp+'>'));
		$(temp2).append($('<div class = "blank_box"> &nbsp</div> '));
		$(temp2).append($(toDisplayImage));
		$(temp2).append($('<div class = "single_message_time">').append(time).append('</div>'));
	}
	
}

/**** add message to the admin ****/

function addMessageToAdmin(imagePath, message, time) {
	
	addImageToAdmin(imagePath,time);
	addTextToAdmin(message, time);
	
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

/**** add video to the client ****/

function addVideoToClient(videoPath, time) {
	
	
	
}

/**** add video to the admin ****/

function addVideoToAdmin(videoPath, time) {
	
	/**** add image to the admin ****/
	if(videoPath!='') {
		/**** appending the image to the div if present ****/
		console.log(videoPath);
		counter++;
		var toDisplayImage = '<div class = "image_plus_desc"><div class = "imgadmin"><video height="94" width = "126" controls ><source src ='+videoPath+' type = "video/mp4" ></video></div>';
		temp = "single-message"+counter;
		temp2 = '#single-message'+counter;
		$('#chat-body-messages').append($('<div class = "single_message" id ='+temp+'>'));
		$(temp2).append($('<div class = "blank_box"> &nbsp</div> '));
		$(temp2).append($(toDisplayImage));
		$(temp2).append($('<div class = "single_message_time">').append(time).append('</div>'));
	}
}

/**** add product image to admin ****/

function addProductImageToAdmin(imagePath, productName, message, time) {
	
	message = atob(message);
	
	/**** add image to the admin ****/
	if(imagePath!='') {
		/**** appending the image to the div if present ****/
		counter++;
		productName = '<strong>'+productName+'</strong> <br> <br>';
		var toDisplayProduct = '<div class = "image_plus_desc"><div class = " imgbox imgproductadmin"><img src = '+imagePath+' class = "setImageSize"></div><div class = "imgproduct_desc">'+productName+message+'</div>';
		temp = "single-message"+counter;
		temp2 = '#single-message'+counter;
		$('#chat-body-messages').append($('<div class = "single_message" id ='+temp+'>'));
		$(temp2).append($('<div class = "blank_box"> &nbsp</div> '));
		$(temp2).append($(toDisplayProduct));
		$(temp2).append($('<div class = "single_message_time">').append(time).append('</div>'));
	}

}
	

/**** function to add the searched image ****/
function addImageForSearchedProduct(message) {
	/**** getting the date and time ****/
	var time = getDateAndTime();
	
	addProductImageToAdmin(searchProductImagePath, productName , message, time);
	
}


/****

http://127.0.0.1/magento19/index.php/mofluid119?service=createnewrequest&customerid=55&sender=ram&receiver=admin&chatmessage=c2hlIGlzIGJvcmluZw==

http://127.0.0.1/magento19/index.php/mofluid119/?service=getallmessages&chatid=1

http://127.0.0.1/magento19/index.php/mofluid119?service=update&chatid=30&sender=ram&receiver=admin&productid=18799&chatmessage=c2hlIGlzIGJvcmluZw==

****/
	

</script>
	</head>
	<body>
		<div class="header">
			<div class = "customerName"><h1 id = "cust_name"></h1></div>
			<div><h1>Admin Chatsystem</h1></div>
		</div>
		<div class="main-content">
			
			<div class = "split right" id = "chat_body" >
				<div class = "chat-body" >
					<ul id = "chat-body-messages">
						
					</ul>
				</div>
			</div>
		</div>
		<div class="footer">
			<div class = "split_footer left_footer" >
				<div class = "bottom-bar" >
					<input type = "file" name = "file" id = "file_to_send" onchange = "setFileReader()"> 
					<input type = "button" name = "submit" value = "submit" onclick = "hitApiForSendFile()">
					
						<input type="text" placeholder='search product id ...' name="search" class = "search_box" id = "search" >
					<input type = "button" value = "upload" id = "sendMe" class = "search_button" onclick = "sendSearchProductImage()" >
				</div>
			</div>
			<div class = "split_footer right_footer" >
				<div class = "bottom-bar">
					<div class = "write-text-box">
						<div class = "blank_box"> &nbsp</div> 
						<textarea class = "text-box"placeholder = 'write your text here ... ' name = "message" id = "message"></textarea>
					</div>
					<div>
						<input type = "button" value = "submit" id = "clickMe" onclick="sendText()" class = "clickMe">
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
