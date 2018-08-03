/**** function to set the division in the requests ****/

function addDivToRequest(requestId, Id) {
	
	if((/\S/.test(requestId))){
		
		/**** add text to the client ****/
		counter++;
		var temp = "single-message"+counter;
		var temp2 = '#single-message'+counter;
		
		/**** appending the text to the div in the chat-body ****/
		$('#request-body-div').append($('<div class = "single_message" id ='+temp+'>'));
		$(temp2).append($('<div class = "single_message_text client">').append(message));
		$(temp2).append($('</div>'));
		$(temp2).append($('<div class = "single_message_time">').append(time).append('</div>'));
	}
	
}

/**** function to set all the requests ****/

function setAllRequests(reqResponseData) {
	
	if(reqResponseData) {
		
		var res = JSON.parse(reqResponseData);
		
		if(res["status"] == 1) {
			
			/**** setting the requests of the array ****/
			requestArr = res["requests_id=>id"];
			
			for(var each in requestArr) {
				//alert(requestArr[each]["requests_id"]+requestArr[each]["id"]);
			}
			
		}else {
			alert("Error in getting all requests");
		}
	}
}

function gettingAllRequests() {
	
	var url = "http://127.0.0.1/soko/index.php/sokochat/?service=listreqid&customerid="+customerId;
	
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
			setAllRequests(this.responseText);
		}
	}
	
	/**** send the xmlhttp requests ****/
	xmlhttp.open("GET",url,true);
	xmlhttp.send();
}
