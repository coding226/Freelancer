<?php
require_once 'core.php';
$site_info = fetchArray('SELECT * FROM php_settings LIMIT 1');
define("SITE_name", $site_info['name']);
define("SITE_email", $site_info['email']);
define("SITE_domain", $site_info['link']);
define("sponsored_setti", $site_info['sponsored']);
define("googleTrackingID", $site_info['googleTrackingID']);
define("google_verification", $site_info['google_verification']);
define("googleAdClient", $site_info['googleAdClient']);
define("googleAdSlot", $site_info['googleAdSlot']);
define("facebookLink", $site_info['facebookLink']);
define("twitterLink", $site_info['twitterLink']);
define("googleLink", $site_info['googleLink']);
define("instagramLink", $site_info['instagramLink']);
define("youtubeLink", $site_info['youtubeLink']);
define("vkLink", $site_info['vkLink']);
define("serversPerPage", $site_info['serversPerPage']);
define("shareThis", $site_info['shareThis']);
define("defaultCurrency", $site_info['defaultCurrency']);
define("voteHours", $site_info['voteHours']);
define("logoDisplay", $site_info['logoDisplay']);
define("maintenance", $site_info['maintenance']);

// SMTP Settings
define("mailingOption", $site_info['mailingOption']);
define("smtp_host", $site_info['smtp_host']);
define("smtp_port", $site_info['smtp_port']);
define("smtp_username", $site_info['smtp_username']);
define("smtp_password", $site_info['smtp_password']);

if (sponsored_setti) {
  $today = date("Y-m-d H:i");
  $auction_info = fetchArray('SELECT id FROM php_auction WHERE start < :start AND deadline > :finish LIMIT 1', array(':start' => $today, ':finish' => $today));
  define("auction_id", $auction_info['id']);
  define("sponsored_setti_two", $site_info['sponsoredamount']);
} else {
  define("sponsored_setti_two", $site_info['sponsoredamount']);
}

## PayPal Define ##
define("PAYPAL_MERCH_ID", $site_info['pp_merchID']);

// Design Selector START
$designCookie = safeInput(isset($_COOKIE['webDesign'])) ? safeInput($_COOKIE['webDesign']) : '';
if($designCookie) {
  $design_info = fetchArray('SELECT * FROM php_designs WHERE id = :id LIMIT 1', array(':id' => $designCookie));
  if ($design_info) {
    # ok
  } else {
    $design_info = fetchArray('SELECT * FROM php_designs WHERE isDefault = 1 LIMIT 1');  
  }
} else {
  $design_info = fetchArray('SELECT * FROM php_designs WHERE isDefault = 1 LIMIT 1');
}
define("SITE_bg_color", $design_info['bg_color']);
define("SITE_main_color", $design_info['main_color']);
define("SITE_secondary_color", $design_info['secondary_color']);
define("SITE_text_color", $design_info['text_color']);
define("showStats", $design_info['showStats']);
define("statPosition", $design_info['statPosition']);
define("gradient", $design_info['gradient']);
define("bgImage", $design_info['bgImage']);

if ($design_info['logoAlign'] == 2) {
  define("SITE_logo_align", 'center-element');
} elseif ($design_info['logoAlign'] == 3) {
  define("SITE_logo_align", 'right-element');
} else {
  define("SITE_logo_align", 'left-element');
}

if ($design_info['menu']) {
  define("setti_menu", 'fixed');
}
if ($design_info['tables'] == 1) {
  define("setti_tables", 'basic');
} elseif ($design_info['tables'] == 2) {
  define("setti_tables", 'striped');
} else {
  define("setti_tables", 'very basic');
}
// Design Selector END

$page = safeInput(isset($_GET['page'])) ? safeInput($_GET['page']) : '';

// Multi-Lang Part START
$lang_check = safeInput(isset($_GET['lang'])) ? safeInput($_GET['lang']) : ''; // Language input
$cookie_check = safeInput(isset($_COOKIE['defaultLang'])) ? safeInput($_COOKIE['defaultLang']) : ''; // Language cookie

if ($cookie_check) {
  $langCheck = fetchArray('SELECT id, code FROM php_languages WHERE id = :id AND isDefault = 0 LIMIT 1', array(':id' => $cookie_check));
  if ($langCheck) {
    if ($langCheck['code'] == $lang_check) {
      if (empty($lang_check) && !empty($cookie_check)) {
        redirect(SITE_domain.'/'.$langCheck['code'].$_SERVER['REQUEST_URI']);
      } else {
        define('LANG_PATH', dirname(__FILE__).'/engine/languages/'.$langCheck['code'].'.php');
      }
    } else {
      if (!$page) {
        redirect(SITE_domain.'/'.$langCheck['code'].'/');  
      } else {
        $url = preg_replace('/'.$lang_check.'\//s', $langCheck['code'].'/', $_SERVER['REQUEST_URI'], 1);
        $new_url = str_replace('//', '', $url);
        if ($lang_check) {
          redirect(SITE_domain.$new_url);  
        } else {
          redirect(SITE_domain.'/'.$new_url);  
        }
      }
    }
    define("SITE_LINK", $site_info['link'].'/'.$langCheck['code']);
  } else {
    setcookie('defaultLang', '', time()-777600000, '/');
    if (!$page) {
      redirect(SITE_domain.'/');  
    } else {
      $url = preg_replace('/'.$lang_check.'\//s', '', $_SERVER['REQUEST_URI'], 1);
      $new_url = str_replace('', '', $url);
      redirect(SITE_domain.'/'.$new_url);  
    }
    define("SITE_LINK", $site_info['link']);
  }
} else {
  # ok
}
if ($lang_check) {
  $langCheck = fetchArray('SELECT id, code, isDefault FROM php_languages WHERE code = :id LIMIT 1', array(':id' => $lang_check));
  if ($langCheck) {
    if (empty($cookie_check) && !empty($lang_check)) {
      setcookie('defaultLang', $langCheck['id'], time()+7776000, '/');
    }
    define('LANG_PATH', 'engine/languages/'.$langCheck['code'].'.php');
    if ($langCheck['isDefault'] == 1) {
      define("SITE_LINK", $site_info['link']);
    } else {
      define("SITE_LINK", $site_info['link'].'/'.$langCheck['code']);
    }
  } else {
    redirect(SITE_domain);
  }
} else {
  $langCheck = fetchArray('SELECT code FROM php_languages WHERE isDefault = 1 LIMIT 1');
  define('LANG_PATH', 'engine/languages/'.$langCheck['code'].'.php');
  define("SITE_LINK", $site_info['link']);
}

define('LANG_code', $langCheck['code']);

$langPg = fetchArray('SELECT code FROM php_languages WHERE code = :id AND isDefault = 0 LIMIT 1', array(':id' => $page));
if($langPg) {
  redirect(SITE_domain.'/'.$langPg['code'].'/');
} 
// Multi-Lang Part END

if (maintenance && !logged()) {
  get('login');
} else {
if(!$page) {
	get ('toplist');
}
elseif($page == 'versions') {
  get ('versions');
}
elseif($page == 'types') {
  get ('types');
}
elseif($page == 'status-checker') {
  get ('status-checker');
}
elseif($page == 'votifier-tester') {
  get ('votifier-tester');
}
elseif($page == 'server') {
	get ('server');
}
elseif($page == 'login') {
  if(logged()) {
    redirect($txt['SITE_LINK'].'/cp');
  } else {
    get('login');
  }
}
elseif($page == 'register') {
	if(logged()) {
    redirect($txt['SITE_LINK'].'/cp');
  } else {
    get('register');
  }
}
elseif($page == 'recover-password') {
  if(logged()) {
    redirect($txt['SITE_LINK'].'/cp');
  } else {
    get('recover-password');
  }
}
elseif($page == 'cp') {
  if(!logged()) {
    set_error('To access this page you need to be logged in', true);
    process_error('login_error', $txt['SITE_LINK'].'/login');
  } else {
    get('cp');
  }
}
elseif($page == 'contacts') {
  get ('contacts');
}

elseif($page=='news-what-are-the-best-hytale-servers')
{
   get('news-what-are-the-best-hytale-servers') ;
}
elseif($page=='news-types-of-hytale-servers')
{
   get('news-types-of-hytale-servers') ;
}
elseif($page == 'terms') {
  get ('terms');
}
elseif($page == 'privacy') {
  get ('privacy');
}
elseif($page == 'help') {
  get ('help');
  
}
elseif($page == 'news') {  
    get ('news');
    
}
elseif($page == 'servers-search') {
  $input = safeInput(isset($_GET['query'])) ? safeInput($_GET['query']) : '';
  $cat = safeInput(isset($_GET['cat'])) ? safeInput($_GET['cat']) : '';

  if ($cat == 2) {
    $query = "SELECT id, name, seo_name, short_description FROM php_servers WHERE LOWER(REPLACE(description, ' ', '')) LIKE ?";
  } elseif ($cat == 3) {
    $query = "SELECT id, name, seo_name, short_description FROM php_servers WHERE LOWER(REPLACE(host, ' ', '')) LIKE ?";
  } elseif ($cat == 4) {
    $query = "SELECT id, name, seo_name, short_description FROM php_servers WHERE LOWER(REPLACE(version, ' ', '')) LIKE ?";
  } elseif ($cat == 5) {
    $query = "SELECT id, name, seo_name, short_description FROM php_servers WHERE LOWER(REPLACE(types, ' ', '')) LIKE ?";
  } elseif ($cat == 6) {
    $query = "SELECT id, name, seo_name, short_description FROM php_servers WHERE LOWER(REPLACE(country, ' ', '')) LIKE ?";
  } else {
    $query = "SELECT id, name, seo_name, short_description FROM php_servers WHERE LOWER(REPLACE(name, ' ', '')) LIKE ?";
  }

  $return_arr = array();
  $allinfo = getArray($query, array('%'.strtolower($input).'%'));
  if ($allinfo)
  {
    foreach($allinfo as $key => $info)
    {
      $id = safeInput($info['id']);
      $seo_name = safeInput($info['seo_name']);

      $row_array['title'] = '<i class="server icon"></i> '.safeInput($info['name']).'<hr>';
      $row_array['url'] = '/'.$seo_name.'.'.$id;
      if ($info['short_description']) {
        $row_array['description'] = safeInput($info['short_description']);
      } else {
        $row_array['description'] = 'This servers doesn\'t have description...';
      }
      array_push($return_arr, $row_array); 
    }
  } else {
     $row_array['title'] = '';
    $row_array['description'] = '<i class="bolt icon"></i> No results found. Try again';
    array_push($return_arr, $row_array);
  }
  echo json_encode(array('results' => $return_arr), JSON_PRETTY_PRINT);
}
elseif($page === 'ipn') {
  header('HTTP/1.1 200 OK');
  define('SSL_P_URL', 'https://ipnpb.paypal.com/cgi-bin/webscr');
  define('SSL_SAND_URL', 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
  $paypal_url = ($_REQUEST['test_ipn'] == 1) ? SSL_SAND_URL : SSL_P_URL;
  $raw_post_data = file_get_contents('php://input');
  $raw_post_array = explode('&', $raw_post_data);
  $myPost = array();
  foreach ($raw_post_array as $keyval) {
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2)
    $myPost[$keyval[0]] = urldecode($keyval[1]);
  }
  $req = 'cmd=_notify-validate';
  if (function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
  }
  foreach ($myPost as $key => $value) {
    if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
      $value = urlencode(stripslashes($value));
    } else {
      $value = urlencode($value);
    }
    $req .= "&$key=$value";
  }
  $ch = curl_init($paypal_url);
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
  curl_setopt($ch, CURLOPT_CAINFO, 'engine/cert/cacert.pem');
  if ( !($res = curl_exec($ch)) ) {
    error_log("Got " . curl_error($ch) . " when processing IPN data".PHP_EOL, 3, 'LOGS/PAYMENTS.log');
    curl_close($ch);
    exit;
  }
  curl_close($ch);
  if (strcmp ($res, "VERIFIED") == 0) {
    $request = $_REQUEST;
    function issetCheck($post,$key) {
      if(isset($post[$key])) {
        $return = $post[$key];
      }
      else {
        $return = '';
      }
      return $return;
    }     
    error_log(date('[Y-m-d H:i]')."[IPN CALL]: Verified - PAYMENT PROCCESSED".PHP_EOL, 3, 'LOGS/PAYMENTS.log');   
    $server = issetCheck($request, 'custom');
    $item_name = issetCheck($request, 'item_name');
    $amount = issetCheck($request, 'item_number');
    $currency = issetCheck($request, 'mc_currency');
    $payer_email = issetCheck($request, 'payer_email');
    $first_name = issetCheck($request, 'first_name');
    $last_name = issetCheck($request, 'last_name');   
    $payment_status = issetCheck($request, 'payment_status');
    $price_get = issetCheck($request, 'payment_gross');
    $price_get = preg_replace('/\.00/', '', $price_get);
    $date = date('Y-m-d H:i');
    $finishDate = date('Y-m-d H:i', strtotime($date. ' + '.$amount.' days'));
    if ($payment_status === 'Completed') {
      if (sponsored_setti && auction_id) {
        $auction = safeInput(auction_id);

        $payCheck = 0;
        $allinfo = getArray('SELECT amount FROM php_bids WHERE auction = :id AND user = :user AND paid = 0 ORDER BY amount DESC LIMIT '.sponsored_setti_two, array(':id' => auction_id, ':user' => $server));
        if ($allinfo)
        {
          foreach($allinfo as $key => $info)
          {
            $amount = safeInput($info['amount']);
            $payCheck += $amount;
          }
        }
        if ($price_get == $payCheck) { 
          $allinfo = getArray('SELECT server FROM php_bids WHERE auction = :id AND user = :user AND paid = 0 ORDER BY amount DESC LIMIT '.sponsored_setti_two, array(':id' => auction_id, ':user' => $server));
          if ($allinfo)
          {
            foreach($allinfo as $key => $info)
            {
              $server = safeInput($info['server']);
              query("
              INSERT INTO
                php_sponsored
                (server, start, finish)
              VALUES
                (:server, :start, :finish)",
              array(':server' => $server, ':start' => $date, ':finish' => $finishDate));
            }
          }
          $q1 = query("
          UPDATE
            php_bids
          SET
            paid = '1'
          WHERE
            auction = :auction AND user = :user
          ORDER BY amount DESC LIMIT ".sponsored_setti_two,
          array(':auction' => $auction, ':user' => $server));
          exit;
        } else {
          exit;
        }
      } else {
        query("
        INSERT INTO
            php_sponsored
            (server, start, finish)
          VALUES
            (:server, :start, :finish)",
          array(':server' => $server, ':start' => $date, ':finish' => $finishDate));
          exit;
      }
    } else {
      error_log(date('[Y-m-d H:i] ')."[IPN CALL]:incomplete".PHP_EOL, 3, 'LOGS/PAYMENTS.log');
      exit;
    }
  } else if (strcmp ($res, "INVALID") == 0) {
    error_log(date('[Y-m-d H:i] ')."[IPN CALL]: False - INVERSTIGATE".PHP_EOL, 3, 'LOGS/PAYMENTS.log');
    echo "Your IP and other computer details were registered in our log file. So go take a coffee by the time we will investigate what you've tried to do here";
    exit;
  }
}
elseif($page === 'logout') {
  removeSession('logged');
  removeSession('user_id');
  redirect(safeInput(SITE_LINK));
}
elseif($page == 'sitemap') {
  header("Pragma-directive: no-cache");
  header("Cache-directive: no-cache");
  header("Cache-control: no-cache");
  header("Pragma: no-cache");
  header("Expires: 0");
	header('Content-type: application/xml');
  $date_modified = date('Y-m-d'); 
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?php echo SITE_domain; ?></loc>
    <priority>1.0</priority>
    <changefreq>daily</changefreq>
    <lastmod><?php echo $date_modified; ?></lastmod>
  </url>
  <url>
    <loc><?php echo SITE_domain; ?></loc>
    <priority>1.0</priority>
    <changefreq>daily</changefreq>
    <lastmod><?php echo $date_modified; ?></lastmod>
  </url>
  <?php
  if(numRows("SELECT COUNT(1) FROM php_versions") > 0) {
  ?>
  <url>
    <loc><?php echo SITE_domain; ?>/versions</loc>
    <priority>1.0</priority>
    <changefreq>daily</changefreq>
    <lastmod><?php echo $date_modified; ?></lastmod>
  </url>
  <?php
  }
  if(numRows("SELECT COUNT(1) FROM php_types") > 0) {
  ?>
  <url>
    <loc><?php echo SITE_domain; ?>/types</loc>
    <priority>1.0</priority>
    <changefreq>daily</changefreq>
    <lastmod><?php echo $date_modified; ?></lastmod>
  </url>
  <?php
  }
  ?>
  <url>
    <loc><?php echo SITE_domain; ?>/status-checker</loc>
    <priority>1.0</priority>
    <changefreq>daily</changefreq>
    <lastmod><?php echo $date_modified; ?></lastmod>
  </url>
  <url>
    <loc><?php echo SITE_domain; ?>/votifier-tester</loc>
    <priority>1.0</priority>
    <changefreq>daily</changefreq>
    <lastmod><?php echo $date_modified; ?></lastmod>
  </url>
</urlset>
<?php
}
else
{
  get ('not-found');
}
}
?>