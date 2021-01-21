<?php
	if(!defined('TOP_STARTED')) exit('Site security activated !');
	function passEncode($password)
	{
		$salt = '8weee2sd069kl97s4d6a5s1d5';
		$password = md5($password.$salt);
		return $password;
	}
	
	function logged()
	{	
		if(readSession('logged') || readSession('user_id'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function CallAPI($method, $url, $data = false)
	{
		$curl = curl_init();

		switch ($method)
		{
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);

				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, 1);
				break;
			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}

		// Optional Authentication:
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, "username:password");

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($curl);

		curl_close($curl);

		return $result;
	}

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

	function colorGen($color, $opacity=NULL, $color_placement=NULL) {
		if (!$color_placement) {
			$getColor = hex2rgb($color);
			$get_color = explode(", ", $getColor);
			$r = $get_color[0];
			$g = $get_color[1];
			$b = $get_color[2];
			if ($opacity == NULL) {
				return 'rgb('.$r.','.$g.','.$b.')';
			} else {
				if ($opacity > 1) {
					$opacity = '1';
				} else {
					$opacity = $opacity;
				}
				return 'rgba('.$r.','.$g.','.$b.','.$opacity.')';
			}
		}
	}
	
	function login_security()
	{	
		if(readSession('login_security'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function URLvalidation($url)
	{
		if(preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function symbolsValidation($value) 
	{
		if(preg_match("/^[A-Za-z0-9-._-]+$/", $value))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function emailValidation($value) 
	{	
		if(preg_match('/^[A-Za-z0-9.+_-]+@[A-Za-z0-9.+_-]+.[a-zA-Z]{2,4}$/', $value))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function HTMLPurified($input) 
	{
		require_once('htmlpurifier/library/HTMLPurifier.auto.php');
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.Doctype', 'XHTML 1.0 Strict');
		$config->set('HTML.Nofollow', true);
		$config->set('HTML.TargetBlank', true);
		$def = $config->getHTMLDefinition(true);
		$def->info_tag_transform['b'] = new HTMLPurifier_TagTransform_Simple('strong');
		$def->info_tag_transform['i'] = new HTMLPurifier_TagTransform_Simple('em');
		$def->addAttribute('a', 'target','Enum#_blank,_self,_target,_top');
		$purifier = new HTMLPurifier($config);
		$first_stage = $purifier->purify($input);
		return $first_stage;
	}
	
	function removeCookie($key)
	{
        if (isset($_COOKIE[$key])) {
			setcookie("$key", "", time()-3600, '/', SITE_domain);
        }
    } 

	## Purify Any HTML to TXT ##
	function TXTonly($input) 
	{
		require_once('htmlpurifier/library/HTMLPurifier.auto.php');
		$config = HTMLPurifier_Config::createDefault();
		$config->set('HTML.Allowed', '');
		$config->set('AutoFormat.RemoveEmpty', true);
		$config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
		$purifier = new HTMLPurifier($config);
		$first_stage = $purifier->purify($input);
		$last_stage = preg_replace('/\s+/', ' ', $first_stage);
		return $last_stage;
	}
									
	function rank($id)
	{
		$sql1 = "SET @rownum := 0";
		$sql2 = '
		SELECT 
			rank, id
		FROM (
			SELECT 
				@rownum := @rownum + 1 AS rank, id
			FROM 
				php_servers
			ORDER BY votes DESC, status DESC, players_online DESC, name ASC
		) as result WHERE id = :id';
		query($sql1);
		$result = fetchArray($sql2, array(':id' => $id));
		if (empty($result)) {
			return 1;
		} else {
			return $result['rank'];
		}
	}

	function SafeInput($value)
	{
		return htmlspecialchars(strip_tags($value));
	}
	
	function IoE()
	{
		foreach (func_get_args() as $input)
		{
			if (!empty($input) && isset($input))
			{
				$good++;
			}
			else
			{
				break;
			}
		}
		
		$allArgs = count(func_get_args());
		
		if ($allArgs == $good)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function makeCode($length) 
	{
		$aZ09 = array_merge(range(0, 9));
		$out ='';
		for($c=0;$c < $length;$c++) 
		{
		   $out .= $aZ09[mt_rand(0,count($aZ09)-1)];
		}
		return $out;
	}

	## Status Checker ##
	function getStatus($ip, $port) {
		$socket = @fsockopen($ip, $port, $errorNo, $errorStr, 5);
		if (!$socket) { 
			if (fsockopen('udp://'.$ip, $port, $errno, $errstr, 5)) 
			{ 
				return true; 
			} 
			else 
			{ 
				return false; 
			}
		} 
		else 
		{ 
			return true; 
		}
	}

	function isNumber($value) {
		if(preg_match('/^[0-9\.]+$/', $value))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isUrl($value) {
		if(preg_match('_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS', $value))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function ident($first_word, $second_word)
	{
		$rezult = strcmp($first_word, $second_word);
		if ($rezult == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function requestMethod($method)
	{
		$server_method = $_SERVER['REQUEST_METHOD'];
		if ($server_method == $method)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function set_error($error_msg = '', $error_status = true)
	{
		global $error_message, $error;
		$error = $error_status;
		if ($error)
		{
			$error_message = $error_msg;
		}
	}
	
	function process_error($error_session_name, $redirect_path, $type = 1)
	{	
		global $error_message;
		if ($type == 1)
		{
			writeSession($error_session_name, "
				<div class='ui error message'>
					<i class='close icon'></i>
					<div class='header'>Error</div>
					<p>". $error_message ."</p>
				</div>");
			redirect($redirect_path);
		}
		else
		{
			writeSession($error_session_name, "
				<div class='ui error message'>
					<i class='close icon'></i>
					<div class='header'>Error</div>
					<p>". $error_message ."</p>
				</div>");
			redirect($redirect_path);
		}
	}

	function makeTokenCode($length) 
	{
		$aZ09 = array_merge(range('a', 'z'), range(0, 9));
		$out ='';
		for($c=0;$c < $length;$c++) 
		{
		   $out .= $aZ09[mt_rand(0,count($aZ09)-1)];
		}
		return $out;
	}

	function realIP()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		  $ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
		  $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else {
		  $ip = $_SERVER['REMOTE_ADDR'];
		}
		return safeInput($ip);
	}
	
	function process_success($success_session_name, $redirect_path, $type = 1)
	{	
		global $success_message;
		
		if ($type == 1)
		{
			writeSession($success_session_name, "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>". $success_message ."</p>
				</div>");
			redirect($redirect_path);
		}
		else
		{
			writeSession($success_session_name, "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>". $success_message ."</p>
				</div>");
			redirect($redirect_path);
		}
	}

function SEO_link($str, $options = array()) {
	// Make sure string is in UTF-8 and strip invalid UTF-8 characters
	$str = mb_convert_encoding((string)$str, 'HTML-ENTITIES', 'utf-8');
	
	$defaults = array(
		'delimiter' => '-',
		'limit' => null,
		'lowercase' => true,
		'replacements' => array(),
		'transliterate' => true,
	);
	
	// Merge options
	$options = array_merge($defaults, $options);
	
	$char_map = array(
		// Latin
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
		'ß' => 'ss', 
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
		'ÿ' => 'y',
		// Latin symbols
		'©' => '(c)',
		// Greek
		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
		'Ϋ' => 'Y',
		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
		// Turkish
		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 
		// Russian
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
		'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
		'я' => 'ya',
		// Ukrainian
		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
		// Czech
		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
		'Ž' => 'Z', 
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z', 
		// Polish
		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
		'Ż' => 'Z', 
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z',
		// Latvian
		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
		'š' => 's', 'ū' => 'u', 'ž' => 'z',
		// Lithuanian 
		'ą' => 'a', 'Ą' => 'a', 'ė' => 'e', 'Ė' => 'e', 'ę' => 'e', 'Ę' => 'e', 'į' => 'i', 'Į' => 'i', 'ų' => 'u', 'Ų' => 'u'
	);
	
	// Make custom replacements
	$str = html_entity_decode(preg_replace(array_keys($options['replacements']), $options['replacements'], $str));
	
	// Transliterate characters to ASCII
	if ($options['transliterate']) {
		$str = str_replace(array_keys($char_map), $char_map, $str);
	}
	
	// Replace non-alphanumeric characters with our delimiter
	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
	
	// Remove duplicate delimiters
	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	
	// Truncate slug to max. characters
	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
	
	// Remove delimiter from ends
	$str = trim($str, $options['delimiter']);
	
	$eta = $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
	$final_stage = preg_replace("/([^a-zA-Z0-9-]+)/", '',$eta);
	return $final_stage;
}
function redirect($location, $script = false) 
{
	if (!$script) 
	{
		header("Location: ".str_replace("&amp;", "&", $location));
		exit;
	} 
	else 
	{
		echo "<script>document.location.href='".str_replace("&amp;", "&", $location)."'</script>\n";
		exit;
	}
}

function timeago($date) {
   $timestamp = strtotime($date);	
	   
   $strTime = array("second", "minute", "hour", "day", "month", "year");
   $length = array("60","60","24","30","12","10");

   $currentTime = time();
   if($currentTime >= $timestamp) {
		$diff     = time()- $timestamp;
		for($i = 0; $diff >= $length[$i] && $i < count($length)-1; $i++) {
			$diff = $diff / $length[$i];
		}
		$diff = round($diff);
		return $diff . " " . $strTime[$i] . "(s) ago ";
   }
}

############################### Hytale Part ###############################	
function Votifier($public_key, $server_ip, $server_port, $username)
{
//parse the public key (if you change anything here it won't work!)
$public_key = wordwrap($public_key, 65, "\n", true);
$public_key = <<<EOF
-----BEGIN PUBLIC KEY-----
$public_key
-----END PUBLIC KEY-----
EOF;
 
	//get user IP
	$address = realIP();
 
	//set voting time
	$timeStamp = time();
 
	//create basic required string for Votifier
	$string = "VOTE\/".SITE_name."\n$username\n$address\n$timeStamp\n";
 
	//fill blanks to make packet lenght 256
	$leftover = (256 - strlen($string)) / 2;
	while ($leftover > 0) {
		$string.= "\x0";
		$leftover--;
	}
 
	//encrypt string before send
	openssl_public_encrypt($string,$crypted,$public_key);
 
	//try to connect to server
	$socket = fsockopen($server_ip, $server_port, $errno, $errstr, 3);
	if ($socket)
	{
		fwrite($socket, $crypted); //on success send encrypted packet to server
		return true;
	}
	else
	{
		return false; //on fail return false
	}
}

function essentialOne ($value = NULL, $title = NULL) {
	$page = safeInput(isset($_GET['page'])) ? safeInput($_GET['page']) : '';
	require LANG_PATH;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo LANG_code; ?>">
<head>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script> <script> (adsbygoogle = window.adsbygoogle || []).push({ google_ad_client: "ca-pub-7851564318958057", enable_page_level_ads: true }); </script>
    <script async custom-element="amp-auto-ads"
        src="https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js">
</script>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" id="viewport" content="width=device-width, initial-scale=1" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="theme-color" content="#<?php echo SITE_main_color; ?>">

	<title><?php echo $title; ?> | <?php echo $txt['SEO_title']; ?></title>
    <meta name="description" content="<?php echo $txt['SEO_description']; ?>">
    <meta name="keywords" content="<?php echo $txt['SEO_keywords']; ?>">
    <?php if (google_verification) { ?>
    <meta name="google-site-verification" content="<?php echo google_verification; ?>" />
    <?php } if (googleTrackingID) { ?>
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	  ga('create', '<?php echo googleTrackingID; ?>', 'auto');
	  ga('send', 'pageview');
	</script>
	<?php
	}
    ?>
	<link rel="canonical" href="<?php if($page) { $extend = $_SERVER['REQUEST_URI']; } else { $extend = str_replace('/', '', $_SERVER['REQUEST_URI']); } $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://'.$_SERVER['HTTP_HOST'].$extend; echo $actual_link; ?>" hreflang="<?php echo LANG_code; ?>">
	<?php
	$allinfo = getArray("SELECT isDefault, code FROM php_languages WHERE code != :code", array(':code' => LANG_code));
  	if ($allinfo)
  	{
   		foreach($allinfo as $key => $info)
    	{
    		$langCode = safeInput($info['code']);
    		$url = preg_replace('%'.LANG_code.'/%', '/', $_SERVER['REQUEST_URI']);
        	$new_url = str_replace('//', '', $url);
        	if($page) {
	    		if (safeInput($info['isDefault']) == 1) {
	    			$link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST']."/".$new_url;
	    		} else {
	    			$link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST']."/".$langCode.$new_url;
	    		}
	    	} else {
				if (safeInput($info['isDefault']) == 1) {
	    			$link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
	    		} else {
	    			$link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST']."/".$langCode."/";
	    		}
	    	}
?>
	<link rel="alternate" href="<?php echo $link; ?>" hreflang="<?php echo $langCode; ?>">
<?php
    	}
  	}
	?>
	<link rel="shortcut icon" href="<?php echo SITE_domain; ?>/favicon.ico">
	<link href="/CSS/minified.css?v03" rel="stylesheet">
	<style>
	#serverPage p{overflow-wrap: break-word;word-wrap: break-word;-ms-word-break: break-all;word-break: break-all;word-break: break-word;-ms-hyphens: auto;-moz-hyphens: auto;-webkit-hyphens: auto;hyphens: auto;}.default-color,.or:before,.ui.button.submit {color: #<?php echo SITE_text_color; ?>!important;background: #<?php echo SITE_main_color; ?>!important;opacity: .99}.insta-search .button {color:#<?php echo SITE_text_color; ?>!important;background: #<?php echo SITE_main_color; ?>!important;}.default-color a, .default-color .item, .server-ip {color: #<?php echo SITE_text_color; ?>!important}.server-ip {background: #<?php echo SITE_main_color; ?>}.copy-action, .copy-action button, nav .ui.button, #server .ui.label {color: #<?php echo SITE_text_color; ?>;background: <?php echo colorGen(SITE_secondary_color); ?>}.ui.segment.list{padding-top:1rem;}#server .ui.label {margin: .25em .5em .25em 0;}.copy-action:hover, nav .ui.button:hover, .button.active, .setti .ui.active.button, .right.menu .ui.button:focus, .setti .ui.button:focus, .insta-search .button:hover {color: #<?php echo SITE_text_color; ?>!important; background: <?php echo colorGen(SITE_secondary_color, .6); ?>!important;transition: all .5s;}.insta-search .button:hover, .button.active {background: <?php echo colorGen(SITE_secondary_color, .8); ?>!important;}.ui.button.submit:hover, #server .ui.label:hover {transition: all .5s;opacity: .9;}.tabular.menu.default-color .active.item, .tabular.menu.default-color .item:hover {border-bottom: 5px solid <?php echo colorGen(SITE_secondary_color); ?>;}body, body.pushable, body.pushable>.pusher {<?php if (gradient && bgImage) { ?>background: url(/CSS/backgrounds/<?php echo bgImage; ?>.png?1) 50% 0 no-repeat, url(/CSS/textures/<?php echo gradient; ?>.png) 0 0 repeat;background-size: 1694px auto, auto auto!important;<?php } elseif (!gradient && bgImage) { ?>background: url(/CSS/backgrounds/<?php echo bgImage; ?>.png?1) 50% 0 no-repeat;background-size: 1694px auto!important;<?php } elseif (gradient && !bgImage) { ?>background:url(/CSS/textures/<?php echo gradient; ?>.png) 0 0 repeat;background-size:auto auto!important;<?php } else {  } ?>background-color:#<?php echo SITE_bg_color; ?>!important;}.ui.nag{border-radius:0;display:block;position:fixed;top:auto;bottom:0;opacity:.9;}.line.social a{color:#000000;font-size:1.8em;}.line.social a:hover{opacity:.8;}.siteLogo{width:100%;max-height:300px;height:auto; }.mobile_ad {max-width:728px;width: 100%;min-height:90px;max-height: 120px;overflow:hidden;text-align: center; margin: 0 auto;}.adsbygoogle{display: block;}#serverStats .ltr,#serverStats svg,#serverStats{width:100%;height:auto}
	</style>
</head>
<body>
    <amp-auto-ads type="adsense"
              data-ad-client="ca-pub-7851564318958057">
</amp-auto-ads>
<!--googleoff: all-->
<div class="ui left vertical inverted menu sidebar">
    <a class="active item" href="<?php echo $txt['SITE_LINK']; ?>"><i class="home icon"></i> <?php echo $txt['txt221']; ?></a>
  	<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/contacts"><i class="envelope icon"></i> <?php echo $txt['txt171']; ?></a>
  	<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/sponsored"><i class="star icon"></i> <?php echo $txt['txt87']; ?></a>
  	<?php
	if(logged()) {
	?>
  	<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/cp"><i class="user icon"></i> <?php echo $txt['txt222']; ?></a>
  	<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/logout"><i class="sign-out icon"></i> <?php echo $txt['txt223']; ?></a>
  	<?php
	} else {
	?>
	<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/register"><i class="user plus icon"></i> <?php echo $txt['txt71']; ?></a>
  	<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/login"><i class="key icon"></i> <?php echo $txt['txt67']; ?></a>
	<?php
	}

	$langs_check = numRows("SELECT COUNT(*) FROM php_languages");
	$designs_check = numRows("SELECT COUNT(*) FROM php_designs");
	if ($langs_check > 1 || $designs_check > 1) {
	?>
	<div class="item setti">
	  	<div class="ui top floating dropdown icon button">
			Settings <i class="cog icon"></i>
			<div class="menu">
				<?php
		    	// Default Language Selector
		    	if ($langs_check > 1) {
		 			$sql = "SELECT id, flagCode, name, isDefault FROM php_languages ORDER BY isDefault DESC";
		  			$allinfo = getArray($sql);
					if ($allinfo)
					{
				?>
				<div class="item">
					<i class="world icon"></i>
            		<i class="dropdown icon"></i>
            		<span class="text"><?php echo $txt['txt226']; ?></span>
				 	<div class="menu defaultLang">
					<?php
					$langCookie = safeInput(isset($_COOKIE['defaultLang'])) ? safeInput($_COOKIE['defaultLang']) : '';
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
      					$code = $info['flagCode'];
      					$name = $info['name'];
      					$isDefault = $info['isDefault'];

						if ($langCookie == $id || ($isDefault && !$langCookie)) {
      						$class = ' active';
      					} else {
      						$class = '';
      					}
      					echo '<div class="item'.$class.'" data-value="'.$id.'"><i class="'.$code.' flag"></i> '.$name.'</div>';
				    }
				    ?>
				    </div>
				</div>
				<?php
				    }
				}
				// Website Template Selector
				if ($designs_check > 1) {
				    $sql = "SELECT id, name, isDefault FROM php_designs ORDER BY name ASC";
	  				$allinfo = getArray($sql);
					if ($allinfo)
					{
				?>
				<div class="item">
	            	<i class="dropdown icon"></i>
	            	<span class="text"><i class="tint icon"></i> <?php echo $txt['txt225']; ?></span>
					<div class="menu webDesign">
					<?php
					$designCookie = safeInput(isset($_COOKIE['webDesign'])) ? safeInput($_COOKIE['webDesign']) : '';
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
	      				$name = $info['name'];
	      				$isDefault = $info['isDefault'];

      					if ($designCookie == $id || ($isDefault && !$designCookie)) {
      						$class = ' active';
      						$ico = '<i class="check square icon"></i> ';
      					} else {
      						$class = '';
      						$ico = '<i class="check square outline icon"></i> ';
      					}
						echo '<div class="item'.$class.'" data-value="'.$id.'">'.$ico.$name.'</div>';
			        }
		        	?>
				    </div>
				</div>
				<?php
					}
				}
				?>          				 
	    	</div>
	  	</div>
  	</div>
  	<?php
  	}
  	?>
</div>
<!--googleon: all-->
<div class="pusher">
<header>
	<nav class="ui top <?php echo setti_menu; ?> menu default-color" itemscope itemtype="http://www.schema.org/SiteNavigationElement">
		<a class="active item toggle-sidebar" href="#" title="Toggle Sidebar"><i class="bars icon"></i></a>
		<div class="ui container">
	  		<a class="active item" href="<?php echo $txt['SITE_LINK']; ?>" itemprop="url"><i class="home icon"></i> <span itemprop="name"><?php echo $txt['txt221']; ?></span></a>
	  		<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/contacts" itemprop="url"><i class="envelope icon"></i> <span itemprop="name"><?php echo $txt['txt171']; ?></span></a>
	  		<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/sponsored" itemprop="url"><i class="star icon"></i> <span itemprop="name"><?php echo $txt['txt87']; ?></span></a>
	  		<div class="right menu">
		  		<div class="nav-right-menu">
		  			<div class="ui buttons">
		  				<?php
		  				if(logged()) {
		  				?>
						<a href="<?php echo $txt['SITE_LINK']; ?>/cp" class="ui button" itemprop="url">
							<i class="user icon"></i> <span itemprop="name"><?php echo $txt['txt222']; ?></span>
						</a>
					  	<div class="or"></div>
					  	<a href="<?php echo $txt['SITE_LINK']; ?>/logout" class="ui button" itemprop="url">
							<i class="sign-out icon"></i> <span itemprop="name"><?php echo $txt['txt223']; ?></span>
						</a>
		  				<?php
		  				} else {
		  				?>
		  				<a href="<?php echo $txt['SITE_LINK']; ?>/register" class="ui button" itemprop="url">
							<i class="user plus icon"></i> <span itemprop="name"><?php echo $txt['txt71']; ?></span>
						</a>
					  	<div class="or"></div>
					  	<a href="<?php echo $txt['SITE_LINK']; ?>/login" class="ui button" itemprop="url">
							<i class="key icon"></i> <span itemprop="name"><?php echo $txt['txt67']; ?></span>
						</a>
						<?php
		  				}
		  				?>
					</div>
		  		</div>
		  	</div>
		  	<?php
			$langs_check = numRows("SELECT COUNT(*) FROM php_languages");
			$designs_check = numRows("SELECT COUNT(*) FROM php_designs");
			if ($langs_check > 1 || $designs_check > 1) {
		  	?>
		  	<div class="item setti">
			  	<div class="ui top right pointing dropdown icon button">
	    			<i class="wrench icon"></i>
	    			<div class="menu">
	    				<?php
	    				// Default Language Selector
	    				if ($langs_check > 1) {
	 						$sql = "SELECT id, flagCode, name, isDefault FROM php_languages ORDER BY isDefault DESC";
	  						$allinfo = getArray($sql);
							if ($allinfo)
							{
						?>
						<div class="item">
							<i class="flag icon"></i>
            				<i class="dropdown icon"></i>
            				<span class="text"><?php echo $txt['txt226']; ?></span>
				            <div class="menu defaultLang">
							<?php
							$langCookie = safeInput(isset($_COOKIE['defaultLang'])) ? safeInput($_COOKIE['defaultLang']) : '';
						    foreach($allinfo as $key => $info)
						    {
						    	$id = $info['id'];
      							$code = $info['flagCode'];
      							$name = $info['name'];
      							$isDefault = $info['isDefault'];

								if ($langCookie == $id || ($isDefault && !$langCookie)) {
      								$class = ' active';
      							} else {
      								$class = '';
      							}
      							echo '<div class="item'.$class.'" data-value="'.$id.'"><i class="'.$code.' flag"></i> '.$name.'</div>';
				        	}
				        	?>
				            </div>
				        </div>
				        <?php
				        	}
				    	}
						// Website Template Selector
						if ($designs_check > 1) {
					        $sql = "SELECT id, name, isDefault FROM php_designs ORDER BY name ASC";
	  						$allinfo = getArray($sql);
							if ($allinfo)
							{
						?>
						<div class="item">
	            			<i class="dropdown icon"></i>
	            			<span class="text"><i class="tint icon"></i> <?php echo $txt['txt225']; ?></span>
					        <div class="menu webDesign">
							<?php
							$designCookie = safeInput(isset($_COOKIE['webDesign'])) ? safeInput($_COOKIE['webDesign']) : '';
						    foreach($allinfo as $key => $info)
						    {
							   	$id = $info['id'];
	      						$name = $info['name'];
	      						$isDefault = $info['isDefault'];

      							if ($designCookie == $id || ($isDefault && !$designCookie)) {
      								$class = ' active';
      								$ico = '<i class="check square icon"></i> ';
      							} else {
      								$class = '';
      								$ico = '<i class="check square outline icon"></i> ';
      							}

      							echo '<div class="item'.$class.'" data-value="'.$id.'">'.$ico.$name.'</div>';
				        	}
				        	?>
				            </div>
				        </div>
					    <?php
					        }
				    	}
				        ?>          				 
	    			</div>
	  			</div>
  			</div>
  			<?php
  			}
  			?>
		</div>
	</nav>
</header>
<section class="ui container under-menu <?php if(setti_menu == 'fixed') { ?>fixed-margin<?php } ?>" itemscope itemtype="http://schema.org/ItemList">
	<link itemprop="itemListOrder" href="https://schema.org/ItemListOrderDescending">
	<div class="<?php echo SITE_logo_align; ?>">
		<a href="<?php echo $txt['SITE_LINK']; ?>" class="site_name">
		<?php
		if (!logoDisplay) {
		?>
		<h1><?php echo SITE_name; ?></h1>
		<?php
		} else {
		?>
		<h1 data-label="Site Logo"><img src="<?php echo SITE_LINK; ?>/CSS/logo.png" alt="<?php echo SITE_name; ?> Logo" class="siteLogo" title="<?php echo SITE_name; ?> Logo"></h1>
		<?php
		}
		?>
		</a>
		<?php
		if (showStats == 1 && !statPosition) {
		$servers = numRows("SELECT COUNT(*) FROM php_servers");
		$users = numRows("SELECT COUNT(*) FROM php_users");
		$players = getTotal('players_online', 'php_servers');
		?>
		<div class="ui three mini statistics" id="statistics">
	  		<div class="statistic">
	    		<div class="value">
	      			<i class="server icon"></i> <?php echo number_format($servers, 0, '.', ' '); ?>
	    		</div>
	    		<div class="label">
	      			<?php echo $txt['txt227']; ?>
	    		</div>
	  		</div>
	  		<div class="statistic">
	    		<div class="value">
	      			<i class="user plus icon"></i> <?php echo number_format($users, 0, '.', ' '); ?>
	    		</div>
	    		<div class="label">
	      			<?php echo $txt['txt228']; ?>
	    		</div>
	  		</div>
	  		<div class="statistic">
	    		<div class="value">
	      			<i class="users icon"></i> <?php echo number_format($players, 0, '.', ' '); ?>
	    		</div>
	    		<div class="label">
	      			<?php echo $txt['txt229']; ?>
	   			</div>
	  		</div>
		</div>
		<?php
		}
		?>
	</div>
	<?php
	uniSession('general_error');

	if($value == 'list') {
	?>
	<div class="top-article">
		<article>
		<?php echo preg_replace("/\r\n|\r|\n/",'<br/>', $txt['SEO_article']); ?>
		</article>
		<hr>
		<?php
		if (googleAdClient && googleAdSlot) {
		?>
		<div class="mobile_ad">
			<ins class="adsbygoogle" style="height:90px" data-ad-client="<?php echo googleAdClient; ?>" data-ad-slot="<?php echo googleAdSlot; ?>" data-ad-format="auto" data-full-width-responsive="true"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		
		</div>
		<?php
		}
		?>
	</div>
	<?php
	} else {
	?>
	<div class="fancy-wrap">
		<div class="mobile_ad">
		<?php
		if (googleAdClient && googleAdSlot) {
		?>
		<ins class="adsbygoogle" style="height:90px" data-ad-client="<?php echo googleAdClient; ?>" data-ad-slot="<?php echo googleAdSlot; ?>" data-ad-format="auto" data-full-width-responsive="true"></ins>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
		<?php
		}
		?>
		</div>
	</div>
<?php
	}
}
function truncate($text, $chars = 120) {
    if (strlen($text) <= $chars) {
        return $text;
    }
    $text = $text." ";
    $text = substr($text,0,$chars);
    $text = substr($text,0,strrpos($text,' '));
    $text = $text."...";
    return $text;
}

function checkVotes($id)
{
	$sql = "SELECT COUNT(*) AS Vcnt FROM php_votes WHERE IP = :ip AND date > :date AND server = :id";
	$data = fetchArray($sql, array(':ip' => realIP(), ':date' => date("Y-m-d H:i"), ':id' => safeInput($id)));
	$voted = $data['Vcnt'];
		
	if($voted > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}
?>
