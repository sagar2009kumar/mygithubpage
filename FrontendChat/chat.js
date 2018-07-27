/*
$('#clickMe').click(function() {
    $('#dynamicaddedlist').append($('<li class = "original_request_number> <div class = "original_requests_number_single_div"> Hello </div> </li>').text('sds'));
});*/

$(document).ready(function() {
	
	var h = findGetParameter('id');
	getData(h);
	
	$('#clickMe').click(function() {
		var counter = 1;
		//~ $('#ticketsDemosss').append($('<li>').text('sds'));
		$('#dynamicaddedlist').append($('<div class = "original_requests_number_single_div">').append('Request # ').append(counter).append('</div>'));
		counter++;
	});
	
	
	$('#submitMe').click(function() {
		// chat single message;
		
	});
	
	
})

function findGetParameter(parameterName) {
	var result = null,
		tmp = [];
	var items = location.search.substr(1).split("&");
	for (var index = 0; index < items.length; index++) {
		tmp = items[index].split("=");
		if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
	}
	return result;
}

function createSingleMessage() {
	data = "Hello";
	$('#chat-body-messages').append($('<div class = "single_message">'));
	$('#chat-body-messages').append($('<div class = "single_message_text">').text(data).append('</div>'));
	$('#chat-body-messages').append($('<div class = "single_message_time">time</div>'));
	$('#chat-body-messages').append($('</div>'));
}

function getAjax() {
	
	var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	xhr.onreadystatechange = function() {
		if (xhr.readyState>3 && xhr.status==200) {
			document.getElementById('chat-body-messages').innerHTML= xhr
	};
	//~ document.getElementById('chat-body-messages').value = xhr.send();
	xhr.open('GET', "http://127.0.0.1/soko/index.php/sokochat/?service=getallmessages&id=1", true);
	xhr.send();
	}
}



function getData(str) {
	
	if (str=="") {
		document.getElementById("chat-body-messages").innerHTML="";
		return;
	} 
	  
	var xmlhttp = "";
  
	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else { // code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
  
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("chat-body-messages").innerHTML=xmlhttp.responseText;
		}
	}
	
	xmlhttp.open("GET","index.php?id="+str,true);
	xmlhttp.send();
}

/*
 * $script = "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js\" type = \"text/javascript\"></script>
		<script type = \"text/javascript\">
			$('#chat-body-messages').append($('<div class = \"single_message\">'));
			$('#chat-body-messages').append($('<div class = \"single_message_text\">Hello	</div>'));
			$('#chat-body-messages').append($('<div class = \"single_message_time\">time</div>'));
			$('#chat-body-messages').append($('</div>'));
		 </script>";
		 * /
		 * 
		 * */


