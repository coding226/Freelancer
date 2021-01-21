<?php
ob_start();
session_start();
error_reporting(E_ALL ^ E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
ini_set("default_charset", "UTF-8");
mb_internal_encoding("UTF-8");
mb_http_output('UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Connection: keep-alive');

if (filesize(dirname(__FILE__).'/config.php') == 0) {
	die("<div style='margin: 1.5em auto; max-width: 600px; text-align:center'><h1 style='margin-bottom: .5em'>Howdy here!</h1><p>To see your TOP list up and running you have to install it! Don't worry it will take only minute (literally)! Pffft, even baby could do it.<br><br>Click <a href='/install.php' style='color: blue; text-decoration: none'>HERE, Yes HERE.. What you waiting for??? Trust me... I wont hurt you</a></p></div>");	
} else {
	# ok
}

$baselvl = ""; $i = 0;
while (!file_exists(dirname(__FILE__)."/config.php")) 
{
	$baselvl .= "../"; $i++;
	if ($i == 5) 
	{ 
		die('<center>Config.php Doesn\'t exist!</center>'); 
	}
}
require $baselvl.'config.php';

define("TOP_STARTED", true);
define("TOP_CORE", dirname(__FILE__).'/engine/');
loadLib('mysql', 'functions', 'security', 'sessions', 'engine');

foreach ($_GET as $check_url) {
	if (!is_array($check_url)) {
		$check_url = str_replace("\"", "", $check_url);
		if ((preg_match("/<[^>]*script*\"?[^>]*>/i", $check_url)) || (preg_match("/<[^>]*object*\"?[^>]*>/i", $check_url)) ||
			(preg_match("/<[^>]*iframe*\"?[^>]*>/i", $check_url)) || (preg_match("/<[^>]*applet*\"?[^>]*>/i", $check_url)) ||
			(preg_match("/<[^>]*meta*\"?[^>]*>/i", $check_url)) || (preg_match("/<[^>]*style*\"?[^>]*>/i", $check_url)) ||
			(preg_match("/<[^>]*form*\"?[^>]*>/i", $check_url)) || (preg_match("/\([^>]*\"?[^)]*\)/i", $check_url)) ||
			(preg_match("/\"/i", $check_url))) {
		die ();
		}
	}
}
unset($check_url);

function loadLib()
{
  foreach (func_get_args() as $lib_name) 
	{
		$_lib_file = TOP_CORE ."/". $lib_name . ".php";
    if (file_exists($_lib_file)) 
		{
      require $_lib_file;
    } 
		else 
		{
      die("File - <b>" . $lib_name . ".php</b> not found!");
    }
  }
}
?>