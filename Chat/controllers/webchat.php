<?php

/**** Remember to include the style file then only the style will be remebered ****/

class Service  {
	
	public function chat($id) {
		
		$url = Mage::getBaseUrl('media');
		$url = $url.'Chatsystem/mystyle.css';
		$url = "".$url."";
		
		
		?>
		
		<html lang=en  >
		<head>
			<meta charset=utf-8>
			<meta name=viewport content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
			<meta name="keywords" content="HTML, CSS, XML, XHTML, JavaScript">
			<meta name="description" content="Free Web tutorials on HTML and CSS">
			<meta name="author" content="Sagar Kumar">
			<title>Sokolin Chat</title>
			<link rel="icon" href="http://sokolin.ebizontech.biz/skin/frontend/sokolin/granada/favicon.ico" type="image/x-icon">
			<link rel="shortcut icon" href="http://sokolin.ebizontech.biz/skin/frontend/sokolin/granada/favicon.ico" type="image/x-icon">
			<link rel="stylesheet" type="text/css" href="<?php $url ?>">
		</head>
		<body>
			<h1> Hello how are you ??</h1>
			<div class = "header" >
				
			</div>
			<div class = "middle">
			
			</div>
			<div class = "footer">
			
			</div>
		</body>	
		</head>
		
		<?php
		
	}
	
}
