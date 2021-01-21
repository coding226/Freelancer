<?php
define("TOP_STARTED", true);
require_once('../config.php');
require_once('../engine/mysql.php');

function caching_headers ($file, $timestamp) {
	$gmt_mtime = gmdate('r', $timestamp);
	header('ETag: "'.md5($timestamp.$file).'"');
	header('Last-Modified: '.$gmt_mtime);
	header('Cache-Control: public');

	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
		if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($timestamp.$file)) {
			header('HTTP/1.1 304 Not Modified');
			exit();
		}
	}
}
caching_headers ($_SERVER['SCRIPT_FILENAME'], filemtime($_SERVER['SCRIPT_FILENAME']));

function SafeInput($value)
{
	return htmlspecialchars(strip_tags($value));
}

$get_id = safeInput(isset($_GET['id'])) ? safeInput($_GET['id']) : '';
$get_code = safeInput(isset($_GET['code'])) ? safeInput($_GET['code']) : '';

$b_i = fetchArray("SELECT icon FROM php_servers WHERE id = :id AND iconCode = :code LIMIT 1", array(':id' => safeInput($get_id), ':code' => safeInput($get_code)));
if ($b_i) {
	$db_img = $b_i['icon'];
	if (!empty($db_img))
	{
		header('Content-type: image/png');
		print $db_img;
	}
	else
	{
		header('Content-type: image/png');
	}
} else {
	header("Location: ".str_replace("&amp;", "&", '/'));
	exit;
}