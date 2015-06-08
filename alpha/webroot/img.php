<?php 
	define('IMG_PATH',__DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR); 
	define('CACHE_PATH',__DIR__ . '/cache/'); 

	require('../src/CMyImage/CMyImage.php'); 	
	$image = new CMyImage($_GET); 
	
	#require('../src/CImage/CImage.php'); 	
	#$image = new CImage();
	