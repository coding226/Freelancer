<?php
define("TOP_STARTED", true);
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
require_once('../config.php');
require_once('../engine/mysql.php');

function SafeInput($value)
{
	return htmlspecialchars(strip_tags($value));
}

## Gets ##
$id = SafeInput($_GET['id']);
$color = SafeInput($_GET['color']);

$info = fetchArray('SELECT name, host, votes, players_total, players_online, status FROM php_servers WHERE id = :id LIMIT 1', array(':id' => $id));
$name = html_entity_decode($info['name']);
$host = htmlentities($info['host']);
$votes = htmlentities($info['votes']);
$players_total = htmlentities($info['players_total']);
$players_online = htmlentities($info['players_online']);
if(htmlentities($info['status']) == 1) {
	$status = 'Online';
} else {
	$status = 'Offline';
}

$finalImage = imagecreatetruecolor(468, 60);
imagesavealpha($finalImage, true);
imagealphablending($finalImage, true);

function hex2rgb($hex) {
	$hex = str_replace("#", "", $hex);
	if(strlen($hex) == 3) {
		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	}
	$rgb = array($r, $g, $b);
	return implode(", ", $rgb);
}

$bg = hex2rgb($color);
$get_background_color = explode(", ", $bg);
$x = $get_background_color[0];
$y = $get_background_color[1];
$z = $get_background_color[2];

$border = hex2rgb('000000');
$get_border_color = explode(", ", $border);
$xx = $get_border_color[0];
$yy = $get_border_color[1];
$zz = $get_border_color[2];

$font = hex2rgb('FFFFFF');
$get_font_color = explode(", ", $font);
$xxx = $get_font_color[0];
$yyy = $get_font_color[1];
$zzz = $get_font_color[2];

$shadow = hex2rgb('000000');
$get_shadow_color = explode(", ", $shadow);
$xxxx = $get_shadow_color[0];
$yyyy = $get_shadow_color[1];
$zzzz = $get_shadow_color[2];

$bg_color = imageColorAllocate($finalImage, $x, $y, $z);
$border_color = imagecolorallocatealpha($finalImage, $xx, $yy, $zz, 85);
$font_color = imageColorAllocate($finalImage, $xxx, $yyy, $zzz);
$shadow_color = imageColorAllocate($finalImage, $xxxx, $yyyy, $zzzz);

imagefill($finalImage, 0, 0, $bg_color);

$gradient2 = imagecreatefrompng("phpImages/gradient2.png");
imagecopyresized($finalImage, $gradient2, 0, 0, 0, 0, 468, 60, 468, 60);

$gradient = imagecreatefrompng("phpImages/gradient.png");
imagecopyresized($finalImage, $gradient, 0, 0, 0, 0, 468, 60, 468, 60);

$x = 0;
$y = 0;
$w = imagesx($finalImage) - 1;
$h = imagesy($finalImage) - 1;

imagettftext($finalImage, 15, 0, 15, 25, $shadow_color, 'phpImages/RobotoCondensed-Regular.ttf', $name);
imagettftext($finalImage, 15, 0, 14, 24, $font_color, 'phpImages/RobotoCondensed-Regular.ttf', $name);

imagettftext($finalImage, 11, 0, 45, 50, $shadow_color, 'phpImages/RobotoCondensed-Regular.ttf', 'IP: '.$host.'  |  Votes: '.$votes.'  |  Players: '.$players_online.'/'.$players_total);
imagettftext($finalImage, 11, 0, 44, 49, $font_color, 'phpImages/RobotoCondensed-Regular.ttf', 'IP: '.$host.'  |  Votes: '.$votes.'  |  Players: '.$players_online.'/'.$players_total);

imagettftext($finalImage, 11, 0, 400, 40, $shadow_color, 'phpImages/RobotoCondensed-Regular.ttf', $status);
imagettftext($finalImage, 11, 0, 399, 39, $font_color, 'phpImages/RobotoCondensed-Regular.ttf', $status);

imageline($finalImage, $x, $y, $x, $y+$h, $border_color);
imageline($finalImage, $x, $y, $x+$w, $y, $border_color);
imageline($finalImage, $x+$w, $y, $x+$w, $y+$h, $border_color);
imageline($finalImage, $x, $y+$h, $x+$w, $y+$h, $border_color);

header('X-Content-Type-Options: nosniff');
header("Cache-Control: no-cache");
header("Expires: ".gmdate("D, d M Y H:i:s", strtotime(date('Y-m-d H:i:s'))-10)." GMT");
header('Content-type: image/png');
imagepng($finalImage);