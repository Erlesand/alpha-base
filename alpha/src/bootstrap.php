<?php
/**
 * Bootstrapping functions, essential and needed for Alpha to work together with some common helpers. 
 *
 */
 
/**
 * Default exception handler.
 *
 */
function myExceptionHandler($exception) {
	echo "Alpha: Uncaught exception: <p>" . $exception->getMessage() . "</p><pre>" . $exception->getTraceAsString(), "</pre>";
}
set_exception_handler('myExceptionHandler');
 
 
/**
 * Autoloader for classes.
 *
 */
function myAutoloader($class) {
	$path = ALPHA_INSTALL_PATH . "/src/{$class}/{$class}.php";
	if(is_file($path)) {
		include($path);
	}
	else {
		throw new Exception("Classfile '{$class}' does not exists.");
	}
}
spl_autoload_register('myAutoloader');

/**
 * Dump information stored in variables
 *
 */
function dump($array) {
 	echo "<pre>" . htmlentities(print_r($array, TRUE)) . "</pre>";
} 

function getCurrentUrl() {
	$url = "http";
	$url .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
	$url .= "://";
	$serverPort = ($_SERVER["SERVER_PORT"] == "80") ? '' :
	(($_SERVER["SERVER_PORT"] == 443 && @$_SERVER["HTTPS"] == "on") ? '' : ":{$_SERVER['SERVER_PORT']}");
	$url .= $_SERVER["SERVER_NAME"] . $serverPort . htmlspecialchars($_SERVER["REQUEST_URI"]);
	return $url;
}

function getQueryString($options, $prepend='?') {
  // parse query string into array
  $query = array();
  parse_str($_SERVER['QUERY_STRING'], $query);
 
  // Modify the existing query string with new options
  $query = array_merge($query, $options);
 
  // Return the modified querystring
  return $prepend . http_build_query($query);
}

/**
 * Create a slug of a string, to be used as url.
 *
 * @param string $str the string to format as slug.
 * @returns str the formatted slug. 
 */
function slugify($str) 
{
	$str = mb_strtolower(trim($str));
	$str = str_replace(array('å','ä','ö'), array('a','a','o'), $str);
	$str = preg_replace('/[^a-z0-9-]/', '-', $str);
	$str = trim(preg_replace('/-+/', '-', $str), '-');

	if(empty($str)) { return 'n-a'; }
	return $str;
}

/**
 * Create a link to the content, based on its type.
 *
 * @param object $content to link to.
 * @return string with url to display content.
 */
function getUrlToContent($content) 
{
	switch($content->type) 
	{
		case 'page': 
			return "page.php?url={$content->url}"; 
			break;
		case 'post': 
			return "blog.php?slug={$content->slug}"; 
			break;
		default: 
			return null; 
			break;
	}
}

function smartyPantsTypographer($text) {
	require_once(__DIR__ . '/smartypants.php');
	return SmartyPants($text);
}
