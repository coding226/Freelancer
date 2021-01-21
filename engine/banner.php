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

$b_i = fetchArray("SELECT banner FROM php_servers WHERE id = :id AND bannerCode = :code LIMIT 1", array(':id' => safeInput($get_id), ':code' => safeInput($get_code)));
if ($b_i) {
	$db_img = $b_i['banner'];
	if (!empty($db_img))
	{
		header('Content-type: image/gif');
		print $db_img;
	}
	else
	{
		function LoadPNG($imgname)
		{
		    $im = @imagecreatefrompng($imgname);
		    if(!$im)
		    {
		        $im  = imagecreatetruecolor(150, 30);
		        $bgc = imagecolorallocate($im, 255, 255, 255);
		        $tc  = imagecolorallocate($im, 0, 0, 0);
		        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
		        imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
		    }
		    return $im;
		}
		header('Content-Type: image/gif');
		$input = array("nobanner.png", "nobanner2.png", "nobanner3.png", "nobanner4.png", "nobanner5.png", "nobanner6.png");
		$rand_keys = array_rand($input, 2);
		$img = LoadPNG('phpImages/'.$input[$rand_keys[0]]);
		imagepng($img);
		imagedestroy($img);
	}
} else {
	header("Location: ".str_replace("&amp;", "&", '/'));
	exit;
}