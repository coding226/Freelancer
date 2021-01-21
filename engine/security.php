<?php
if(!defined('TOP_STARTED')) exit('Site security activated !');
function validate($validate)
{
	require LANG_PATH;
	
	global $error_message, $error;
	if ($validate == 'login')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$username = safeInput($_POST['username']);
			$password = safeInput($_POST['password']);
		
			$maintenanceCheck = fetchArray("SELECT rank FROM php_users WHERE name = :name AND password = :pass AND rank = 1", array(':name' => $username, ':pass' => passEncode($password)));
			if (!maintenance || ($maintenanceCheck && maintenance))
			{
				if (IoE($username, $password))
				{
					$check = numRows("SELECT COUNT(*) FROM php_users WHERE name = :name AND password = :pass", array(':name' => $username, ':pass' => passEncode($password)));
					if ($check == 1)
					{
						$banCheck = fetchArray("SELECT banReason FROM php_users WHERE name = :name AND password = :pass AND isBanned = 1", array(':name' => $username, ':pass' => passEncode($password)));
						if (!$banCheck)
						{
							# ok
						}
						else
						{
							set_error($txt['banText'].$banCheck['banReason'], true);
						}
					}
					else
					{
						set_error($txt['txt172'], true);
					}
				}
				else
				{
					set_error($txt['txt172'], true);
				}
			}
			else
			{
				set_error($txt['txt231'], true);
			}

				
			if (!$error)
			{
				$user_id = fetchArray("SELECT id FROM php_users WHERE name = :name AND password = :pass LIMIT 1", array(':name' => $username, ':pass' => passEncode($password)));
				writeSession('logged', 1);
				writeSession('user_id', safeInput($user_id['id']));
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				process_error('login_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'register')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$username = safeInput($_POST['username']);
			$email = safeInput($_POST['email']);
			$password = safeInput($_POST['password']);
			$password_confirm = safeInput($_POST['cn-password']);
			$agreement = safeInput($_POST['agreement']);

			$user_ip = safeInput(realIP());
			if(safeInput(strtolower($_POST['captcha'])) != safeInput(strtolower($_SESSION['digit'])))
			{
				writeSession('register_error', "<div class='ui error message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt173']."</div>
					<p>".$txt['txt174']."</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				# ok
			}

			if (IoE($username, $email, $password, $password_confirm))
			{
				if (IoE($agreement))
				{
					if (ident($password, $password_confirm) == true)
					{
						if ((strlen($password) >= 6) AND (strlen($password) <= 20))
						{
							$name_duplicate = numRows("SELECT COUNT(*) FROM php_users WHERE name = :name", array(':name' => $username));
							if ($name_duplicate == 0)
							{
								if ((strlen($username) >= 2) AND (strlen($username) <= 16))
								{
									$email_duplicate = numRows("SELECT COUNT(*) FROM php_users WHERE email = :email", array(':email' => $email));
									if ($email_duplicate == 0)
									{
										if ((strlen($email) >= 10) AND (strlen($email) <= 100))
										{
											# ok
										}
										else
										{
											set_error($txt['txt175'], true);
										}
									}
									else
									{
										set_error($txt['txt176'], true);
									}
								}
								else
								{
									set_error($txt['txt177'], true);
								}
							}
							else
							{
								set_error($txt['txt178'], true);
							}
						}
						else
						{
							set_error($txt['txt179'], true);
						}
					}
					else
					{
						set_error($txt['txt180'], true);
					}
				}
				else
				{
					set_error($txt['txt181'], true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
				query("INSERT INTO php_users (name, password, email, date) VALUES (:name, :pw, :email, :date)", array(':name' => $username, ':pw' => passEncode($password), ':email' => $email, ':date' => date('Y-m-d H:i')));
				writeSession('register_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt183']."</p>
				</div>");
				redirect(SITE_LINK.'/login');
			}
			else
			{
				process_error('register_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'recover-password')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$email = safeInput($_POST['email']);
		
			if (IoE($email))
			{
				$check = numRows("SELECT COUNT(1) FROM php_users WHERE email = :email", array(':email' => $email));
				if ($check == 1)
				{
					# ok
				}
				else
				{
					set_error($txt['txt184'], true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
				$settings = fetchArray("SELECT name, id, email FROM php_users WHERE email = :email LIMIT 1", array(':email' => $email));
				$user_name = safeInput($settings['name']);
				$user_id = safeInput($settings['id']);
				$to = safeInput($settings['email']);
				$plus_date = strtotime("+ 1 hours");
				$recovery_date = safeInput(date("Y-m-d H:i", $plus_date));
				$token = safeInput(makeTokenCode(50));
				$token_date = safeInput(date('Y-m-d H:i'));
				$subject = SITE_name.$txt['txt185'];
				$message = $txt['txt186'].$token;
				$headers = "From: ".SITE_name." <".SITE_email.">\r\n";
				$headers .= "Organization: ".SITE_name."\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=UTF-8\r\n";
				$headers .= "X-Priority: 1\r\n";
				$headers .= "X-Mailer: PHP". phpversion() ."\r\n";
				$headers .= "Reply-To: ".SITE_email."\r\n";
				$headers .= "Return-Path: ".SITE_email."\r\n";
				$headers .= "CC: ".$to."\r\n";
				$q = query("UPDATE php_users SET recovery_id = :token, recovery_date = :date WHERE id = :id", array(':token' => $token, ':date' => $recovery_date, ':id' => $user_id));				
				writeSession('recover_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt187']."</p>
				</div>");

				if (mailingOption == 1) {
					advancedEmailer($to, $subject, $message);
					redirect(safeInput($_SERVER['HTTP_REFERER']));
				} else {
					mail($to, $subject, '<body>'.$message.'</body>', $headers);
				}

				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				process_error('recover_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'recover-password-step2')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$password = safeInput($_POST['password']);
			$password_confirm = safeInput($_POST['password-confirm']);
			$recovery_id = safeInput($_POST['recovery-id']);
		
			if (IoE($password, $password_confirm, $recovery_id))
			{
				if (ident($password, $password_confirm) == true)
				{
					if ((strlen($password) >= 6) AND (strlen($password) <= 20))
					{
						# ok
					}
					else
					{
						set_error($txt['txt179'], true);
					}
				}
				else
				{
					set_error($txt['txt180'], true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
				query("
					UPDATE 
						php_users 
					SET 
						password = :pw, 
						recovery_id = '', 
						recovery_date = '' 
					WHERE 
						recovery_id = :id 
					LIMIT 1", 
					array(':pw' => passEncode($password), ':id' => $recovery_id));				
				writeSession('recover_two_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt188']."</p>
				</div>");
				redirect(SITE_LINK.'/login');
			}
			else
			{
				process_error('recover_two_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'contacts')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$name = safeInput($_POST['name']);
			$email = safeInput($_POST['email']);
			$message = safeInput($_POST['message']);

			$user_ip = safeInput(realIP());
			if (safeInput(strtolower($_POST['captcha'])) != safeInput(strtolower($_SESSION['digit'])))
			{
				writeSession('contacts_error', "<div class='ui error message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt173']."</div>
					<p>".$txt['txt174']."</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				# ok
			}

			if (IoE($name, $email, $message))
			{
				if ((strlen($message) >= 30) AND (strlen($message) <= 2000))
				{
					# ok
				}
				else
				{
					set_error($txt['txt189'], true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
				$to = SITE_email;
				$subject = SITE_name." | New Message";
				$headers = "From: ".$email." <".$email.">\r\n";
				$headers .= "Organization: ".SITE_name."\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=UTF-8\r\n";
				$headers .= "X-Priority: 1\r\n";
				$headers .= "X-Mailer: PHP". phpversion() ."\r\n";
				$headers .= "Reply-To: ".$email."\r\n";
				$headers .= "Return-Path: ".$email."\r\n";
				$headers .= "Return-Path: ".$email."\r\n";
				$headers .= "Return-Path: ".$email."\r\n";
				$headers .= "CC: ".$to."\r\n";
				$message .= $email;
				$message .= "<h1>".$name."</h1>";
				
				
				writeSession('contacts_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt190']."</p>
				</div>");

				if (mailingOption == 1) {
					advancedEmailer($to, $subject, $message, $email, $name);
				} else {
					mail($to, $subject, '<body>'.$message.'</body>', $headers, $email);
				}

				redirect(SITE_LINK.'/contacts');
			}
			else
			{
				process_error('contacts_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'vote')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$server_id = safeInput($_POST['id']);
			$username = safeInput($_POST['username']);							

			$user_ip = safeInput(realIP());
			$date = safeInput(date("Y-m-d H:i"));
			if (safeInput(strtolower($_POST['captcha'])) != safeInput(strtolower($_SESSION['digit'])))
			{
				writeSession('vote_error', "<div class='ui error message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt173']."</div>
					<p>".$txt['txt174']."</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				# ok
			}

			if (IoE($username))
			{
				if (!checkVotes($server_id)) 
				{
					# ok
				}
				else
				{
					set_error($txt['txt191'], true);
				}
			}
			else
			{
				set_error($txt['txt192'], true);
			}
					
			if (!$error)
			{
				$nextvote = date("Y-m-d H:i", strtotime('+'.voteHours.' hours'));
				query("UPDATE php_servers SET votes = votes + 1 WHERE id = :id LIMIT 1", array(':id' => $server_id));
				query("INSERT INTO php_votes (server, username, IP, date) VALUES (:id, :username, :ip, :next)", array(':id' => $server_id, ':username' => $username, ':ip' => $user_ip, ':next' => $nextvote));
				$votifier_info = fetchArray("SELECT votifier_host, votifier_port, votifier_key FROM php_servers WHERE id = :id LIMIT 1", array(':id' => $server_id));
				$votifier_host = safeInput($votifier_info['votifier_host']);
				$votifier_port = safeInput($votifier_info['votifier_port']);
				$votifier_key = safeInput($votifier_info['votifier_key']);
				$votifier = Votifier($votifier_key, $votifier_host, $votifier_port, $username);
				if($votifier == true) {
					writeSession('vote_success', "
					<div class='ui success message'>
						<i class='close icon'></i>
						<div class='header'>".$txt['txt118']."</div>
						<p>".$txt['txt193']."</p>
					</div>");
				} else {
					writeSession('vote_success', "
					<div class='ui success message'>
						<i class='close icon'></i>
						<div class='header'>".$txt['txt118']."</div>
						<p>".$txt['txt194']."</p>
					</div>");
				}
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				process_error('vote_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'add')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			// Required Fields
			$name = safeInput($_POST['name']);
			$host = safeInput($_POST['host']);
			$port = safeInput($_POST['port']);
			$country = safeInput($_POST['country']);

			// Additional Fields
			$website = safeInput($_POST['website']);

			$votifierStatus = safeInput(isset($_POST['votifier-status'])) ? safeInput($_POST['votifier-status']) : '';
			$votifierHost = safeInput(isset($_POST['votifier-host'])) ? safeInput($_POST['votifier-host']) : '';
			$votifierPort = safeInput(isset($_POST['votifier-port'])) ? safeInput($_POST['votifier-port']) : '';
			$votifierKey = safeInput(isset($_POST['votifier-key'])) ? safeInput($_POST['votifier-key']) : '';

			$banner = $_FILES['banner']['tmp_name'];
			$banner_size = $_FILES['banner']['size'];

			$description = safeInput(HTMLPurified($_POST['description']));
			$shortDescription = safeInput(TXTonly($_POST['short-description']));
			$version = safeInput($_POST['version']);
			$types = safeInput($_POST['types']);
			
			if ($types) {
				$types_check = explode(',', $_POST['types']);
			} else {
				$types_check = '';
			}

			$user_ip = safeInput(realIP());
			if (safeInput(strtolower($_POST['captcha'])) != safeInput(strtolower($_SESSION['digit'])))
			{
				writeSession('add_error', "<div class='ui error message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt173']."</div>
					<p>".$txt['txt174']."</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				# ok
			}

			if(!empty($types) && is_array($types_check)) {
				if (count($types_check) <= 5)
				{
					foreach($types_check as $value) {
						if ((strlen($value) >= 2) AND (strlen($value) <= 20))
						{
							# ok
						} 
						else
						{
							set_error($txt['txt195'], true);
						}
					}
				}
				else
				{
					set_error($txt['txt196'], true);
				}
			} else {
				# ok
			}

			if (IoE($name, $country))
			{
				if ((strlen($name) >= 2) AND (strlen($name) <= 40))
				{
					if (!$description || ($description && ((strlen($description) >= 100) AND (strlen($description) <= 1500))))
					{
						if (!$shortDescription || ($shortDescription && ((strlen($shortDescription) >= 20) AND (strlen($shortDescription) <= 300))))
						{
							if(getStatus($host, $port))
							{
								if (!$website || ($website && isUrl($website)))
								{
									if (!$version || ($version && ((strlen($version) >= 2) AND (strlen($version) <= 20))))
									{
										# ok
									}
									else
									{
										set_error($txt['txt197'], true);
									}
								}
								else
								{
									set_error($txt['txt198'], true);
								}
							}
							else
							{
								set_error($txt['txt199'], false);
							}
						}
						else
						{
							set_error($txt['txt200'], true);
						}
					}
					else
					{
						set_error($txt['txt201'], true);
					}

				}
				else
				{
					set_error($txt['txt202'], true);
				}
			}
			else
			{
				set_error($txt['txt203'], true);
			}

			if ($banner) 
			{
				if ($banner_size != 0) 
				{
					$verifyimg = getimagesize($_FILES['banner']['tmp_name']);
					if (($_FILES['banner']['type'] == "image/gif")
					|| ($_FILES['banner']['type'] == "image/jpeg")
					|| ($_FILES['banner']['type'] == "image/jpg")
					|| ($_FILES['banner']['type'] == "image/png")
					|| ($verifyimg['mime'] == "image/gif")
					|| ($verifyimg['mime'] == "image/jpeg")
					|| ($verifyimg['mime'] == "image/jpg")
					|| ($verifyimg['mime'] == "image/png"))
					{
						$pattern = "#^(image/)[^\s\n<]+$#i";
						if(preg_match($pattern, $verifyimg['mime']))
						{
							if ($banner_size < 2000000)
							{
								if (!empty($verifyimg) || ($verifyimg[0] !== 0) || ($verifyimg[1] !== 0))
								{
									$maxWidth = 468;
									$maxHeight = 60;
									list($width, $height) = $verifyimg;
									if ($width === $maxWidth || $height === $maxHeight) 
									{
										$banner_upload = file_get_contents($banner);
										# ok
									}
									else
									{
										set_error($txt['txt204'], true);
									}
								}
								else
								{
									set_error($txt['txt205'], true);
								}
							}
							else
							{
								set_error($txt['txt206'], true);
							}
						}
						else
						{
							set_error($txt['txt207'], true);
						}
					}
					else
					{
						set_error($txt['txt207'], true);
					}
				}
				else 
				{
					set_error($txt['txt205'], true);
				}
			}
			else 
			{
				$banner_upload = '';
				# ok
			}
			
			if ($votifierStatus == 1) {
				if (IoE($votifierHost, $votifierPort, $votifierKey))
				{
					if (isNumber($votifierPort))
					{
						# ok
					}
					else
					{
						set_error($txt['txt208'], true);
					}
				}
				else
				{
					set_error($txt['txt209'], true);
				}
			} else {
				# ok
			}

			if (!$error)
			{
				query("
				INSERT INTO 
					php_servers 
					(owner, name, seo_name, host, port, website, description, short_description, status, version, types, country, uptime, banner, bannerCode, votifier_status, votifier_host, votifier_port, votifier_key, `date`) 
				VALUES 
					(:owner, :name, :seo_name, :host, :port, :website, :description, :short_description, :status, :version, :types, :country, :uptime, :banner, :bannerCode, :votifier_status, :votifier_host, :votifier_port, :votifier_key, :date)", 
				array(
					':owner' => safeInput(readSession('user_id')), 
					':name' => $name, 
					':seo_name' => SEO_link($name), 
					':host' => $host,
					':port' => $port, 
					':website' => $website, 
					':description' => $description, 
					':short_description' => $shortDescription,
					':status' => 1, 
					':version' => $version, 
					':types' => $types, 
					':country' => $country,
					':uptime' => 100,
					':banner' => $banner_upload,
					':bannerCode' => makeCode(5),
					':votifier_status' => $votifierStatus,
					':votifier_host' => $votifierHost,
					':votifier_port' => $votifierPort,
					':votifier_key' => $votifierKey,
					':date' => date('Y-m-d H:i')));

				writeSession('add_success', "
				<div class='ui success message' style='margin: 0 0 1em 0!important'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt210']."</p>
				</div>");
				redirect(SITE_LINK.'/cp');
			}
			else
			{
				process_error('add_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'edit')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			// Required Fields
			$id = safeInput($_POST['id']);
			$name = safeInput($_POST['name']);
			$host = safeInput($_POST['host']);
			$port = safeInput($_POST['port']);
			$country = safeInput($_POST['country']);

			// Additional Fields
			$admin = safeInput($_POST['admin']);
			$website = safeInput($_POST['website']);

			$votifierStatus = safeInput(isset($_POST['votifier-status'])) ? safeInput($_POST['votifier-status']) : '';
			$votifierHost = safeInput(isset($_POST['votifier-host'])) ? safeInput($_POST['votifier-host']) : '';
			$votifierPort = safeInput(isset($_POST['votifier-port'])) ? safeInput($_POST['votifier-port']) : '';
			$votifierKey = safeInput(isset($_POST['votifier-key'])) ? safeInput($_POST['votifier-key']) : '';

			$banner = $_FILES['banner']['tmp_name'];
			$banner_size = $_FILES['banner']['size'];

			$icon = $_FILES['icon']['tmp_name'];
			$icon_size = $_FILES['icon']['size'];

			$description = safeInput(HTMLPurified($_POST['description']));
			$shortDescription = safeInput(TXTonly($_POST['short-description']));
			$version = safeInput($_POST['version']);
			$types = safeInput($_POST['types']);

			if ($types) {
				$types_check = explode(',', $_POST['types']);
			} else {
				$types_check = '';
			}

			if(!empty($types) && is_array($types_check)) {
				if (count($types_check) <= 5)
				{
					foreach($types_check as $value) {
						if ((strlen($value) >= 2) AND (strlen($value) <= 20))
						{
							# ok
						} 
						else
						{
							set_error($txt['txt195'], true);
						}
					}
				}
				else
				{
					set_error($txt['txt196'], true);
				}
			} else {
				# ok
			}

			if (IoE($name, $country, $id))
			{
				if ((strlen($name) >= 2) AND (strlen($name) <= 40))
				{
					if (!$description || ($description && ((strlen($description) >= 100) AND (strlen($description) <= 1500))))
					{
						if (!$shortDescription || ($shortDescription && ((strlen($shortDescription) >= 20) AND (strlen($shortDescription) <= 300))))
						{
							if(getStatus($host, $port))
							{
								if (!$website || ($website && isUrl($website)))
								{
									if (!$version || ($version && ((strlen($version) >= 2) AND (strlen($version) <= 20))))
									{

										# ok
									}
									else
									{
										set_error($txt['txt197'], true);
									}
								}
								else
								{
									set_error($txt['txt198'], true);
								}
							}
							else
							{
								set_error($txt['txt199'], false);
							}
						}
						else
						{
							set_error($txt['txt200'], true);
						}
					}
					else
					{
						set_error($txt['txt201'], true);
					}

				}
				else
				{
					set_error($txt['txt202'], true);
				}
			}
			else
			{
				set_error($txt['txt203'], true);
			}

			if ($icon) 
			{
				if ($icon_size != 0) 
				{
					$verifyimg = getimagesize($_FILES['icon']['tmp_name']);
					if (($_FILES['icon']['type'] == "image/gif")
					|| ($_FILES['icon']['type'] == "image/jpeg")
					|| ($_FILES['icon']['type'] == "image/jpg")
					|| ($_FILES['icon']['type'] == "image/png")
					|| ($verifyimg['mime'] == "image/gif")
					|| ($verifyimg['mime'] == "image/jpeg")
					|| ($verifyimg['mime'] == "image/jpg")
					|| ($verifyimg['mime'] == "image/png"))
					{
						$pattern = "#^(image/)[^\s\n<]+$#i";
						if(preg_match($pattern, $verifyimg['mime']))
						{
							if ($icon_size < 2000000)
							{
								if (!empty($verifyimg) || ($verifyimg[0] !== 0) || ($verifyimg[1] !== 0))
								{
									$maxWidth = 64;
									$maxHeight = 64;
									list($width, $height) = $verifyimg;
									if ($width === $maxWidth || $height === $maxHeight) 
									{
										$icon_stat = TRUE;
										$icon_upload = file_get_contents($icon);
										# ok
									}
									else
									{
										set_error($txt['txt234'], true);
									}
								}
								else
								{
									set_error($txt['txt235'], true);
								}
							}
							else
							{
								set_error($txt['txt236'], true);
							}
						}
						else
						{
							set_error($txt['txt237'], true);
						}
					}
					else
					{
						set_error($txt['txt237'], true);
					}
				}
				else 
				{
					set_error($txt['txt235'], true);
				}
			}
			else 
			{
				$icon_stat = FALSE;
				$icon_upload = '';
				# ok
			}

			if ($banner) 
			{
				if ($banner_size != 0) 
				{
					$verifyimg = getimagesize($_FILES['banner']['tmp_name']);
					if (($_FILES['banner']['type'] == "image/gif")
					|| ($_FILES['banner']['type'] == "image/jpeg")
					|| ($_FILES['banner']['type'] == "image/jpg")
					|| ($_FILES['banner']['type'] == "image/png")
					|| ($verifyimg['mime'] == "image/gif")
					|| ($verifyimg['mime'] == "image/jpeg")
					|| ($verifyimg['mime'] == "image/jpg")
					|| ($verifyimg['mime'] == "image/png"))
					{
						$pattern = "#^(image/)[^\s\n<]+$#i";
						if(preg_match($pattern, $verifyimg['mime']))
						{
							if ($banner_size < 2000000)
							{
								if (!empty($verifyimg) || ($verifyimg[0] !== 0) || ($verifyimg[1] !== 0))
								{
									$maxWidth = 468;
									$maxHeight = 60;
									list($width, $height) = $verifyimg;
									if ($width === $maxWidth || $height === $maxHeight) 
									{
										$banner_stat = TRUE;
										$banner_upload = file_get_contents($banner);
										# ok
									}
									else
									{
										set_error($txt['txt204'], true);
									}
								}
								else
								{
									set_error($txt['txt205'], true);
								}
							}
							else
							{
								set_error($txt['txt206'], true);
							}
						}
						else
						{
							set_error($txt['txt207'], true);
						}
					}
					else
					{
						set_error($txt['txt207'], true);
					}
				}
				else 
				{
					set_error($txt['txt205'], true);
				}
			}
			else 
			{
				$banner_stat = FALSE;
				$banner_upload = '';
				# ok
			}

			if ($votifierStatus == 1) {
				if (IoE($votifierHost, $votifierPort, $votifierKey))
				{
					if (isNumber($votifierPort))
					{
						# ok
					}
					else
					{
						set_error($txt['txt208'], true);
					}
				}
				else
				{
					set_error($txt['txt209'], true);
				}
			} else {
				# ok
			}

			if (!$error)
			{
				if ($admin) {
					if ($banner_stat) {
						$q1 = query("
						UPDATE
							php_servers
						SET
							banner = :banner, 
							bannerCode = :bannerCode
						WHERE 
							id = :id 
						LIMIT 1", 
						array(
							':banner' => $banner_upload, 
							':bannerCode' => makeCode(5), 
							':id' => $id));
					} else {
						# ok
					}

					if ($icon_stat) {
						$q3 = query("
						UPDATE
							php_servers
						SET
							icon = :icon, 
							iconCode = :iconCode
						WHERE 
							id = :id 
						LIMIT 1", 
						array(
							':icon' => $icon_upload, 
							':iconCode' => makeCode(5), 
							':id' => $id));
					} else {
						# ok
					}

					$q2 = query("
					UPDATE
						php_servers
					SET
						name = :name, 
						seo_name = :seo_name, 
						host = :host, 
						port = :port, 
						website = :website, 
						description = :description, 
						short_description = :short_description, 
						status = :status, 
						version = :version, 
						types = :types, 
						country = :country, 
						votifier_status = :votifier_status, 
						votifier_host = :votifier_host, 
						votifier_port = :votifier_port, 
						votifier_key = :votifier_key
					WHERE
						id = :id
					LIMIT 1", 
					array(
						':name' => $name, 
						':seo_name' => SEO_link($name), 
						':host' => $host,
						':port' => $port, 
						':website' => $website, 
						':description' => $description, 
						':short_description' => $shortDescription,
						':status' => 1, 
						':version' => $version, 
						':types' => $types, 
						':country' => $country,
						':votifier_status' => $votifierStatus,
						':votifier_host' => $votifierHost,
						':votifier_port' => $votifierPort,
						':votifier_key' => $votifierKey,
						':id' => $id));
					writeSession('admin_servers_success', "
					<div class='ui success message' style='margin: 0 0 1em 0!important'>
						<i class='close icon'></i>
						<div class='header'>".$txt['txt118']."</div>
						<p>".$txt['txt211']."</p>
					</div>");
					redirect($txt['SITE_LINK'].'/cp/admin/servers');
				} else {
					if ($banner_stat) {
						$q1 = query("
						UPDATE
							php_servers
						SET
							banner = :banner, 
							bannerCode = :bannerCode
						WHERE 
							id = :id 
						AND 
							owner = :owner 
						LIMIT 1", 
						array(
							':banner' => $banner_upload, 
							':bannerCode' => makeCode(5), 
							':id' => $id, 
							':owner' => safeInput(readSession('user_id'))));
					} else {
						# ok
					}

					if ($icon_stat) {
						$q3 = query("
						UPDATE
							php_servers
						SET
							icon = :icon, 
							iconCode = :iconCode
						WHERE 
							id = :id 
						LIMIT 1", 
						array(
							':icon' => $icon_upload, 
							':iconCode' => makeCode(5), 
							':id' => $id));
					} else {
						# ok
					}

					$q2 = query("
					UPDATE
						php_servers
					SET
						name = :name, 
						seo_name = :seo_name, 
						host = :host, 
						port = :port, 
						website = :website, 
						description = :description, 
						short_description = :short_description, 
						status = :status, 
						version = :version, 
						types = :types, 
						country = :country, 
						votifier_status = :votifier_status, 
						votifier_host = :votifier_host, 
						votifier_port = :votifier_port, 
						votifier_key = :votifier_key
					WHERE
						id = :id
					AND
						owner = :owner
					LIMIT 1", 
					array(
						':name' => $name, 
						':seo_name' => SEO_link($name), 
						':host' => $host,
						':port' => $port, 
						':website' => $website, 
						':description' => $description, 
						':short_description' => $shortDescription,
						':status' => 1, 
						':version' => $version, 
						':types' => $types, 
						':country' => $country,
						':votifier_status' => $votifierStatus,
						':votifier_host' => $votifierHost,
						':votifier_port' => $votifierPort,
						':votifier_key' => $votifierKey,
						':id' => $id, 
						':owner' => safeInput(readSession('user_id'))));
					writeSession('edit_success', "
					<div class='ui success message' style='margin: 0 0 1em 0!important'>
						<i class='close icon'></i>
						<div class='header'>".$txt['txt118']."</div>
						<p>".$txt['txt211']."</p>
					</div>");
					redirect($txt['SITE_LINK'].'/cp');
				}
			}
			else
			{
				if ($admin) {
					process_error('admin_servers_error', $txt['SITE_LINK'].'/cp/admin/servers');
				} else {
					process_error('edit_error', safeInput($_SERVER['HTTP_REFERER']));
				}
			}
		}
	}
	elseif ($validate == 'account_username')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$password = safeInput($_POST['password']);
			$username = safeInput($_POST['username']);

			
			if (IoE($password, $username))
			{
				if (numRows("SELECT COUNT(*) FROM php_users WHERE id = :id AND password = :pw", array(':id' => safeInput(readSession('user_id')), ':pw' => passEncode($password))) == 1)
				{
					$name_duplicate = numRows("SELECT COUNT(*) FROM php_users WHERE name = :name", array(':name' => $username));
					if ($name_duplicate == 0)
					{
						if ((strlen($username) >= 2) AND (strlen($username) <= 16))
						{
							# ok
						}
						else
						{
							set_error($txt['txt177'], true);
						}
					}
					else
					{
						set_error($txt['txt178'], true);
					}
				}
				else
				{
					set_error($txt['txt212'], true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
				query("UPDATE php_users SET name = :name WHERE id = :id AND password = :pw LIMIT 1", array(':name' => $username, ':id' => safeInput(readSession('user_id')), ':pw' => passEncode($password)));
				writeSession('account_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt213']."</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				process_error('account_error', safeInput($_SERVER['HTTP_REFERER']));
			}
			
		}
	}
	elseif ($validate == 'account_email')
	{
		if (isset($_POST['submit_two']) && requestMethod('POST'))
		{
			$password = safeInput($_POST['password']);
			$email = safeInput($_POST['email']);

			
				if (IoE($password, $email))
				{
					if (numRows("SELECT COUNT(*) FROM php_users WHERE id = :id AND password = :pw", array(':id' => safeInput(readSession('user_id')), ':pw' => passEncode($password))) == 1)
					{
						$email_duplicate = numRows("SELECT COUNT(*) FROM php_users WHERE email = :email", array(':email' => $email));
						if ($email_duplicate == 0)
						{
							if ((strlen($email) >= 10) AND (strlen($email) <= 100))
							{
								# ok
							}
							else
							{
								set_error($txt['txt175'], true);
							}
						}
						else
						{
							set_error($txt['txt176'], true);
						}
					}
					else
					{
						set_error($txt['txt212'], true);
					}
				}
				else
				{
					set_error($txt['txt182'], true);
				}
					
				if (!$error)
				{
					query("UPDATE php_users SET email = :email WHERE id = :id AND password = :pw LIMIT 1", array(':email' => $email, ':id' => safeInput(readSession('user_id')), ':pw' => passEncode($password)));
					writeSession('account_success', "
					<div class='ui success message'>
						<i class='close icon'></i>
						<div class='header'>".$txt['txt118']."</div>
						<p>".$txt['txt214']."</p>
					</div>");
					redirect(safeInput($_SERVER['HTTP_REFERER']));
				}
				else
				{
					process_error('account_error', safeInput($_SERVER['HTTP_REFERER']));
				}
			
		}
	}
	elseif ($validate == 'account_password')
	{
		if (isset($_POST['submit_three']) && requestMethod('POST'))
		{
			$password = safeInput($_POST['password']);
			$new_password = safeInput($_POST['new_password']);
			$c_new_password = safeInput($_POST['c_new_password']);

			
				if (IoE($password, $new_password, $c_new_password))
				{
					if (numRows("SELECT COUNT(*) FROM php_users WHERE id = :id AND password = :pw", array(':id' => safeInput(readSession('user_id')), ':pw' => passEncode($password))) == 1)
					{
						if (ident($new_password, $c_new_password) == true)
						{
							if ((strlen($new_password) >= 6) AND (strlen($new_password) <= 20))
							{
								# ok
							}
							else
							{
								set_error($txt['txt179'], true);
							}
						}
						else
						{
							set_error($txt['txt180'], true);
						}
					}
					else
					{
						set_error($txt['txt212'], true);
					}
				}
				else
				{
					set_error($txt['txt182'], true);
				}
					
				if (!$error)
				{
					query("UPDATE php_users SET password = :new_pw WHERE id = :id AND password = :pw LIMIT 1", array(':new_pw' => passEncode($new_password), ':id' => safeInput(readSession('user_id')), ':pw' => passEncode($password)));
					writeSession('account_success', "
					<div class='ui success message'>
						<i class='close icon'></i>
						<div class='header'>".$txt['txt118']."</div>
						<p>".$txt['txt215']."</p>
					</div>");
					redirect(safeInput($_SERVER['HTTP_REFERER']));
				}
				else
				{
					process_error('account_error', safeInput($_SERVER['HTTP_REFERER']));
				}
			
		}
	}
	elseif ($validate == 'sponsored_buy')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$server = safeInput($_POST['server']);
			$periodCheck = safeInput($_POST['period']);
			$coupon = safeInput($_POST['coupon']);

			$couponCheck = fetchArray("SELECT * FROM php_coupons WHERE coupon = :coupon AND finish > :finish LIMIT 1", array(':coupon' => $coupon, ':finish' => date("Y-m-d H:i")));
			$discount  = $couponCheck['discount'];

			$info = fetchArray("SELECT price FROM php_sponsoredoptions WHERE id = :period LIMIT 1", array(':period' => $periodCheck));
			$price = $info['price'];

			$secondInfo = fetchArray("SELECT period FROM php_sponsoredoptions WHERE price = :price LIMIT 1", array(':price' => $price));
			$period = $secondInfo['period'];

			if (IoE($server, $period, $price))
			{
				if (numRows("SELECT COUNT(*) FROM php_sponsored WHERE start <= :start AND finish > :finish", array(':start' => date("Y-m-d H:i"), ':finish' => date("Y-m-d H:i"))) < sponsored_setti_two)
				{
					if (!$coupon || ($coupon && $couponCheck))
					{
						# ok
					}
					else
					{
						set_error($txt['txt240'], true);
					}
				}
				else
				{
					set_error($txt['txt216'], true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}

			if (!$error)
			{
				if (!$coupon)
				{
					$updated_price = $price;
				} else {
					$updated_price = round($price - (($price / 100) * $discount));
				}

				header('Location: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business='.PAYPAL_MERCH_ID.'&item_name='.$period.' Days Sponsored Status&custom='.$server.'&amount='.$updated_price.'&item_number='.$period.'&currency_code='.defaultCurrency.'&no_shipping=1&no_note=1&cancel_return='.$txt['SITE_LINK'].'/sponsored&notify_url='.SITE_domain.'/ipn&return='.$txt['SITE_LINK'].'/sponsored/status=thankyou&button_subtype=services');
			}
			else
			{
				process_error('sponsored_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'sponsored_pay')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$totalPay = safeInput($_POST['price']);
			
			$payCheck = 0;
			$allinfo = getArray('SELECT amount FROM php_bids WHERE auction = :id AND user = :user AND paid = 0 ORDER BY amount DESC LIMIT '.sponsored_setti_two, array(':id' => auction_id, ':user' => safeInput(readSession('user_id'))));
			if ($allinfo)
			{
				foreach($allinfo as $key => $info)
				{
					$amount = safeInput($info['amount']);
					$payCheck += $amount;
				}
			}

			if (IoE($totalPay, $payCheck))
			{
				if ($totalPay == $payCheck)
				{
					# ok
				}
				else
				{
					set_error($txt['txt217'].' $'.$payCheck, true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
  				$auction_info = fetchArray('SELECT * FROM php_auction WHERE id = :id LIMIT 1', array(':id' => auction_id));
				$period = $auction_info['period'];
				header('Location: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business='.PAYPAL_MERCH_ID.'&item_name='.$period.' Days Sponsored Status&custom='.safeInput(readSession('user_id')).'&amount='.$totalPay.'&item_number='.$period.'&currency_code='.defaultCurrency.'&no_shipping=1&no_note=1&cancel_return='.$txt['SITE_LINK'].'/sponsored&notify_url='.SITE_domain.'/ipn&return='.$txt['SITE_LINK'].'/sponsored/status=thankyou&button_subtype=services');
			}
			else
			{
				process_error('sponsored_pay_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'sponsored_bid')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$server = safeInput($_POST['server']);
			$bid = safeInput($_POST['bid']);

			$auction_info = fetchArray('SELECT start, finish, minBid, period FROM php_auction WHERE id = :id LIMIT 1', array(':id' => auction_id));
			$today = date("Y-m-d H:i");
			$start = $auction_info['start'];
			$finish = $auction_info['finish'];
			$minBid = $auction_info['minBid'];
			$period = $auction_info['period'];

  			if ($today < $start) {
  				$formStatus = false;
  			} elseif ($today >= $start && $today < $finish) {
  				$formStatus = true;
  			} else {
  				$formStatus = false;
  			}

  			if ($totalBids/sponsored_setti_two >= 1) {
  				if (round($totalBids/sponsored_setti_two) < 2) {
  					$newCount = 2;
  				} else {
  					$newCount = round($totalBids/sponsored_setti_two);
  				}
  				$calcuMinBid = $newCount * $minBid;
  			} else {
  				$calcuMinBid = $minBid;
  			}

			if (IoE($server, $bid))
			{
				if ($bid >= $calcuMinBid)
				{
					if ($formStatus)
					{
						# ok
					}
					else
					{
						set_error($txt['txt218'], true);
					}
				}
				else
				{
					set_error($txt['txt219'].' $'.$calcuMinBid, true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
				if(numRows('SELECT COUNT(*) FROM php_bids WHERE auction = :id AND server = :server', array(':id' => auction_id, ':server' => $server)) != 0) {
					query('
					UPDATE
						php_bids
					SET 
						amount = :amount 
					WHERE 
						auction = :id AND server = :server', 
						array(':amount' => $bid, ':id' => auction_id, ':server' => $server));
				} else {
					query('
					INSERT INTO 
						php_bids 
						(auction, server, user, amount)
					VALUES
						(:auction, :server, :user, :amount)',
						array(':auction' => auction_id, ':server' => $server, ':user' => safeInput(readSession('user_id')), ':amount' => $bid));
				}
				writeSession('sponsored_bid_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt220']."</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				process_error('sponsored_bid_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'admin_general')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$maintenance = safeInput($_POST['maintenance']);
			$link = safeInput($_POST['link']);
			$name = safeInput($_POST['name']);
			$email = safeInput($_POST['email']);
			$defaultLanguage = safeInput($_POST['defaultLanguage']);
			$defaultDesign = safeInput($_POST['defaultDesign']);
			$ppMerch = safeInput($_POST['ppMerch']);
			$goTrack = safeInput($_POST['goTrack']);
			$goVerifi = safeInput($_POST['goVerifi']);
			$googleAdClient = safeInput($_POST['googleAdClient']);
			$googleAdSlot = safeInput($_POST['googleAdSlot']);
			$facebook = safeInput($_POST['facebook']);
			$twitter = safeInput($_POST['twitter']);
			$google = safeInput($_POST['google']);
			$instagram = safeInput($_POST['instagram']);
			$youtube = safeInput($_POST['youtube']);
			$vk = safeInput($_POST['vk']);
			$serversPerPage = safeInput($_POST['serversPerPage']);
			$shareThis = safeInput($_POST['shareThis']);
			$defaultCurrency = safeInput($_POST['defaultCurrency']);
			$logoDisplay = safeInput($_POST['logoDisplay']);
			$voteHours = safeInput($_POST['voteHours']);

			$mailingOption = safeInput($_POST['mailingOption']);
			$smtp_host = safeInput($_POST['smtp_host']);
			$smtp_port = safeInput($_POST['smtp_port']);
			$smtp_username = safeInput($_POST['smtp_username']);
			$smtp_password = safeInput($_POST['smtp_password']);

			if ($mailingOption) {
				if (IoE($smtp_host, $smtp_port, $smtp_username, $smtp_password))
				{
					# ok
				}
				else
				{
					set_error('Don\'t leave empty required fields in SMTP section', true);
				}
			} else {
				# ok
			}

			if (IoE($link, $name, $voteHours, $email, $defaultLanguage, $defaultDesign, $serversPerPage))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty required fields', true);
			}
				
			if (!$error)
			{
				$q1 = query('UPDATE php_languages SET isDefault = 0');
				$q2 = query('UPDATE php_languages SET isDefault = 1 WHERE id = :id LIMIT 1', array(':id' => $defaultLanguage));

				$q3 = query('UPDATE php_designs SET isDefault = 0');
				$q4 = query('UPDATE php_designs SET isDefault = 1 WHERE id = :id LIMIT 1', array(':id' => $defaultDesign));

				$q5 = query('
				UPDATE
					php_settings
				SET
					name = :name,
					link = :link,
					email = :email,
					logoDisplay = :logoDisplay,
					voteHours = :voteHours,
					pp_merchID = :ppMerch,
					googleTrackingID = :goTrack,
					google_verification = :goVerifi,
					googleAdClient = :googleAdClient,
					googleAdSlot = :googleAdSlot,
					facebookLink = :facebook,
					twitterLink = :twitter,
					googleLink = :google,
					instagramLink = :instagram,
					youtubeLink = :youtube,
					vkLink = :vk,
					serversPerPage = :serversPerPage,
					shareThis = :shareThis,
					defaultCurrency = :defaultCurrency,
					mailingOption = :mailingOption,
					smtp_host = :smtp_host,
					smtp_port = :smtp_port,
					smtp_username = :smtp_username,
					smtp_password = :smtp_password,
					maintenance = :maintenance', 
				array(
					':name' => $name, 
					':link' => $link, 
					':email' => $email,
					':logoDisplay' => $logoDisplay,
					':voteHours' => $voteHours,
					':ppMerch' => $ppMerch, 
					':goTrack' => $goTrack, 
					':goVerifi' => $goVerifi, 
					':googleAdClient' => $googleAdClient, 
					':googleAdSlot' => $googleAdSlot, 
					':facebook' => $facebook, 
					':twitter' => $twitter,
					':google' => $google,
					':instagram' => $instagram,
					':youtube' => $youtube,
					':vk' => $vk,
					':serversPerPage' => $serversPerPage,
					':shareThis' => $shareThis,
					':defaultCurrency' => $defaultCurrency,
					':mailingOption' => $mailingOption,
					':smtp_host' => $smtp_host,
					':smtp_port' => $smtp_port,
					':smtp_username' => $smtp_username,
					':smtp_password' => $smtp_password,
					':maintenance' => $maintenance));

				writeSession('admin_general_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Settings updated!</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
			}
			else
			{
				process_error('admin_general_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
		if (isset($_POST['resetVotes']) && requestMethod('POST'))
		{
			query('UPDATE php_servers SET votes = 0');
			writeSession('admin_general_success', "
			<div class='ui success message'>
				<i class='close icon'></i>
				<div class='header'>Success</div>
				<p>Votes successfully reseted!</p>
			</div>");
			redirect(safeInput($_SERVER['HTTP_REFERER']));
		}
	}
	elseif ($validate == 'admin_add_language')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$name = safeInput($_POST['name']);
			$code = safeInput($_POST['code']);
			$flagCode = safeInput($_POST['flagCode']);
			$default = safeInput($_POST['default']);

			if (IoE($name, $code, $flagCode))
			{
				$file = 'engine/languages/DO_NOT_DELETE.php';
				$newfile = 'engine/languages/'.$code.'.php';
				if(copy($file, $newfile))
				{
					# ok
				}
				else
				{
					set_error('Couldn\'t create language file! Try to CHMOD your languages directory', true);
				}
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				if ($default == 1) {
					query('UPDATE php_languages SET isDefault = 0');
				}
				query('
				INSERT INTO 
					php_languages 
					(name, code, flagCode, isDefault) 
				VALUES 
					(:name, :code, :flagCode, :isDefault)', 
				array(
					':name' => $name,
					':code' => $code,
					':flagCode' => $flagCode,
					':isDefault' => $default));	

				writeSession('admin_language_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Language successfully added. Translation file [/engine/languages/".$code.".php]</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/languages');
			}
			else
			{
				process_error('admin_language_error', $txt['SITE_LINK'].'/cp/admin/languages');
			}
		}
	}
	elseif ($validate == 'admin_edit_language')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$oldName = safeInput($_POST['oldName']);
			$name = safeInput($_POST['name']);
			$code = safeInput($_POST['code']);
			$flagCode = safeInput($_POST['flagCode']);
			$default = safeInput($_POST['default']);

			if (IoE($id, $oldName, $name, $code, $flagCode))
			{
				$file = 'engine/languages/'.$oldName.'.php';
				$newfile = 'engine/languages/'.$code.'.php';
				if(rename($file, $newfile))
				{
					# ok
				}
				else
				{
					set_error('Couldn\'t create language file! Try to CHMOD your languages directory', true);
				}
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				if ($default == 1) {
					query('UPDATE php_languages SET isDefault = 0');
				}
				query('
				UPDATE
					php_languages
				SET 
					name = :name, 
					code = :code, 
					flagCode = :flagCode, 
					isDefault = :isDefault 
				WHERE
					id = :id
				LIMIT 1', 
				array(
					':name' => $name,
					':code' => $code,
					':flagCode' => $flagCode,
					':isDefault' => $default,
					':id' => $id));	

				writeSession('admin_language_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Language successfully updated. Translation file [/engine/languages/".$code.".php]</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/languages');
			}
			else
			{
				process_error('admin_language_error', $txt['SITE_LINK'].'/cp/admin/languages');
			}
		}
	}
	elseif ($validate == 'admin_add_design')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$name = safeInput($_POST['name']);
			$mainColor = safeInput(str_replace('#', '', $_POST['mainColor']));
			$textColor = safeInput(str_replace('#', '', $_POST['textColor']));
			$secondaryColor = safeInput(str_replace('#', '', $_POST['secondaryColor']));
			$bgColor = safeInput(str_replace('#', '', $_POST['bgColor']));
			$menu = safeInput($_POST['menu']);
			$tables = safeInput($_POST['tables']);
			$logoAlign = safeInput($_POST['logoAlign']);
			$showStats = safeInput($_POST['showStats']);
			$statPosition = safeInput($_POST['statPosition']);
			$isDefault = safeInput($_POST['default']);
			$gradient = safeInput($_POST['gradient']);
			$bgImage = safeInput($_POST['bgImage']);

			if (IoE($name, $mainColor, $secondaryColor, $bgColor))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				if ($isDefault == 1) {
					query('UPDATE php_designs SET isDefault = 0');
				}
				query('
				INSERT INTO 
					php_designs 
					(name, gradient, bgImage, main_color, text_color, secondary_color, bg_color, menu, tables, logoAlign, showStats, statPosition, isDefault) 
				VALUES 
					(:name, :gradient, :bgImage, :mainColor, :textColor, :secondaryColor, :bgColor, :menu, :tables, :logoAlign, :showStats, :statPosition, :isDefault)', 
				array(
					':name' => $name,
					':gradient' => $gradient,
					':bgImage' => $bgImage,
					':mainColor' => $mainColor,
					':textColor' => $textColor,
					':secondaryColor' => $secondaryColor,
					':bgColor' => $bgColor,
					':menu' => $menu,
					':tables' => $tables,
					':logoAlign' => $logoAlign,
					':showStats' => $showStats,
					':statPosition' => $statPosition,
					':isDefault' => $isDefault));	

				writeSession('admin_design_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Design successfully added</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/designs');
			}
			else
			{
				process_error('admin_design_error', $txt['SITE_LINK'].'/cp/admin/designs');
			}
		}
	}
	elseif ($validate == 'admin_edit_design')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$name = safeInput($_POST['name']);
			$mainColor = safeInput(str_replace('#', '', $_POST['mainColor']));
			$textColor = safeInput(str_replace('#', '', $_POST['textColor']));
			$secondaryColor = safeInput(str_replace('#', '', $_POST['secondaryColor']));
			$bgColor = safeInput(str_replace('#', '', $_POST['bgColor']));
			$menu = safeInput($_POST['menu']);
			$tables = safeInput($_POST['tables']);
			$logoAlign = safeInput($_POST['logoAlign']);
			$showStats = safeInput($_POST['showStats']);
			$statPosition = safeInput($_POST['statPosition']);
			$isDefault = safeInput($_POST['default']);
			$gradient = safeInput($_POST['gradient']);
			$bgImage = safeInput($_POST['bgImage']);

			if (IoE($name, $mainColor, $secondaryColor, $bgColor))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				if ($isDefault == 1) {
					query('UPDATE php_designs SET isDefault = 0');
				}
				query('
				UPDATE
					php_designs 
				SET 
					name = :name, 
					gradient = :gradient, 
					bgImage = :bgImage, 
					main_color = :mainColor, 
					text_color = :textColor, 
					secondary_color = :secondaryColor, 
					bg_color = :bgColor, 
					menu = :menu, 
					tables = :tables, 
					logoAlign = :logoAlign, 
					showStats = :showStats,
					statPosition = :statPosition, 
					isDefault = :isDefault
				WHERE
					id = :id
				LIMIT 1', 
				array(
					':name' => $name,
					':gradient' => $gradient,
					':bgImage' => $bgImage,
					':mainColor' => $mainColor,
					':textColor' => $textColor,
					':secondaryColor' => $secondaryColor,
					':bgColor' => $bgColor,
					':menu' => $menu,
					':tables' => $tables,
					':logoAlign' => $logoAlign,
					':showStats' => $showStats,
					':statPosition' => $statPosition,
					':isDefault' => $isDefault,
					':id' => $id));	

				writeSession('admin_design_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Design successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/designs');
			}
			else
			{
				process_error('admin_design_error', $txt['SITE_LINK'].'/cp/admin/designs');
			}
		}
	}
	elseif ($validate == 'admin_add_auction')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$start = safeInput($_POST['start']);
			$finish = safeInput($_POST['finish']);
			$deadline = safeInput($_POST['deadline']);
			$minBid = safeInput($_POST['minBid']);
			$period = safeInput($_POST['period']);

			if (IoE($start, $finish, $deadline, $minBid, $period))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				INSERT INTO 
					php_auction 
					(start, finish, deadline, minBid, period) 
				VALUES 
					(:start, :finish, :deadline, :minBid, :period)', 
				array(
					':start' => $start,
					':finish' => $finish,
					':deadline' => $deadline,
					':minBid' => $minBid,
					':period' => $period));	

				writeSession('admin_sponsored_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Auction successfully added</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_edit_auction')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$start = safeInput($_POST['start']);
			$finish = safeInput($_POST['finish']);
			$deadline = safeInput($_POST['deadline']);
			$minBid = safeInput($_POST['minBid']);
			$period = safeInput($_POST['period']);

			if (IoE($start, $finish, $deadline, $minBid, $period))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				UPDATE
					php_auction
				SET 
					start = :start, 
					finish = :finish, 
					deadline = :deadline, 
					minBid = :minBid, 
					period = :period
				WHERE
					id = :id
				LIMIT 1', 
				array(
					':start' => $start,
					':finish' => $finish,
					':deadline' => $deadline,
					':minBid' => $minBid,
					':period' => $period,
					':id' => $id));	

				writeSession('admin_language_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Auction successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_add_coupon')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$coupon = safeInput($_POST['coupon']);
			$finish = safeInput($_POST['finish']);
			$discount = safeInput($_POST['discount']);

			if (IoE($coupon, $finish, $discount))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				INSERT INTO 
					php_coupons 
					(coupon, finish, discount) 
				VALUES 
					(:coupon, :finish, :discount)', 
				array(
					':coupon' => $coupon,
					':finish' => $finish,
					':discount' => $discount));	

				writeSession('admin_sponsored_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Coupon successfully added</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_edit_coupon')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$coupon = safeInput($_POST['coupon']);
			$finish = safeInput($_POST['finish']);
			$discount = safeInput($_POST['discount']);

			if (IoE($coupon, $finish, $discount))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				UPDATE
					php_coupons
				SET 
					coupon = :coupon, 
					finish = :finish,
					discount = :discount 
				WHERE
					id = :id
				LIMIT 1', 
				array(
					':coupon' => $coupon,
					':finish' => $finish,
					':discount' => $discount,
					':id' => $id));	

				writeSession('admin_sponsored_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Coupon successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_add_sponsorstatus')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$server = safeInput($_POST['server']);
			$position = safeInput($_POST['position']);
			$start = safeInput($_POST['start']);
			$finish = safeInput($_POST['finish']);

			if (IoE($server, $position, $start, $finish))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				INSERT INTO 
					php_sponsored 
					(server, position, start, finish) 
				VALUES 
					(:server, :position, :start, :finish)', 
				array(
					':server' => $server,
					':position' => $position,
					':start' => $start,
					':finish' => $finish));	

				writeSession('admin_sponsored_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Server was given sponsored status successfully</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_edit_sponsorstatus')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$server = safeInput($_POST['server']);
			$position = safeInput($_POST['position']);
			$start = safeInput($_POST['start']);
			$finish = safeInput($_POST['finish']);

			if (IoE($server, $position, $start, $finish))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				UPDATE
					php_sponsored
				SET 
					server = :server, 
					position = :position,
					start = :start, 
					finish = :finish
				WHERE
					server = :id
				LIMIT 1', 
				array(
					':server' => $server,
					':position' => $position,
					':start' => $start,
					':finish' => $finish,
					':id' => $id));	

				writeSession('admin_sponsored_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Status successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_add_option')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$price = safeInput($_POST['price']);
			$period = safeInput($_POST['period']);

			if (IoE($price, $period))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				INSERT INTO 
					php_sponsoredoptions 
					(price, period) 
				VALUES 
					(:price, :period)', 
				array(
					':price' => $price,
					':period' => $period));	

				writeSession('admin_sponsored_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Option successfully added</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_edit_option')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$price = safeInput($_POST['price']);
			$period = safeInput($_POST['period']);

			if (IoE($price, $period))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				UPDATE
					php_sponsoredoptions
				SET 
					price = :price, 
					period = :period
				WHERE
					id = :id
				LIMIT 1', 
				array(
					':price' => $price,
					':period' => $period,
					':id' => $id));	

				writeSession('admin_sponsored_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Option successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_sponsored')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$sponsored = safeInput($_POST['sponsored']);
			$sponsoredamount = safeInput($_POST['sponsoredamount']);

			if (IoE($sponsoredamount))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				UPDATE
					php_settings
				SET 
					sponsored = :sponsored, 
					sponsoredamount = :sponsoredamount', 
				array(
					':sponsored' => $sponsored,
					':sponsoredamount' => $sponsoredamount));	

				writeSession('admin_sponsored_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Settings successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
			}
			else
			{
				process_error('admin_sponsored_error', $txt['SITE_LINK'].'/cp/admin/sponsored');
			}
		}
	}
	elseif ($validate == 'admin_edit_user')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$name = safeInput($_POST['name']);
			$email = safeInput($_POST['email']);
			$isBanned = safeInput($_POST['isBanned']);
			$banReason = safeInput($_POST['banReason']);

			if (IoE($name, $email))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				UPDATE
					php_users
				SET 
					name = :name, 
					email = :email,
					isBanned = :isBanned,
					banReason = :banReason
				WHERE
					id = :id
				LIMIT 1', 
				array(
					':name' => $name,
					':email' => $email,
					':isBanned' => $isBanned,
					':banReason' => $banReason,
					':id' => $id));	

				writeSession('admin_users_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>User successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/users');
			}
			else
			{
				process_error('admin_users_error', $txt['SITE_LINK'].'/cp/admin/users');
			}
		}
	}
	elseif ($validate == 'admin_add_version')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$name = safeInput($_POST['name']);

			if (IoE($name))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				INSERT INTO 
					php_versions 
					(name) 
				VALUES 
					(:name)', 
				array(':name' => $name));	

				writeSession('admin_versions_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Version successfully added</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/versions');
			}
			else
			{
				process_error('admin_versions_error', $txt['SITE_LINK'].'/cp/admin/versions');
			}
		}
	}
	elseif ($validate == 'admin_edit_version')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$name = safeInput($_POST['name']);

			if (IoE($name))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				UPDATE
					php_versions
				SET 
					name = :name
				WHERE
					id = :id
				LIMIT 1', 
				array(
					':name' => $name,
					':id' => $id));	

				writeSession('admin_versions_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Version successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/versions');
			}
			else
			{
				process_error('admin_versions_error', $txt['SITE_LINK'].'/cp/admin/versions');
			}
		}
	}
	elseif ($validate == 'admin_add_type')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$name = safeInput($_POST['name']);

			if (IoE($name))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				INSERT INTO 
					php_types 
					(name) 
				VALUES 
					(:name)', 
				array(':name' => $name));	

				writeSession('admin_types_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Type successfully added</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/types');
			}
			else
			{
				process_error('admin_types_error', $txt['SITE_LINK'].'/cp/admin/types');
			}
		}
	}
	elseif ($validate == 'admin_edit_type')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$id = safeInput($_POST['id']);
			$name = safeInput($_POST['name']);

			if (IoE($name))
			{
				# ok
			}
			else
			{
				set_error('Don\'t leave empty fields', true);
			}
				
			if (!$error)
			{
				query('
				UPDATE
					php_types
				SET 
					name = :name
				WHERE
					id = :id
				LIMIT 1', 
				array(
					':name' => $name,
					':id' => $id));	

				writeSession('admin_types_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>Success</div>
					<p>Type successfully updated</p>
				</div>");
				redirect($txt['SITE_LINK'].'/cp/admin/types');
			}
			else
			{
				process_error('admin_types_error', $txt['SITE_LINK'].'/cp/admin/types');
			}
		}
	}
	elseif ($validate == 'status_checker')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$host = safeInput($_POST['host']);
			$port = safeInput($_POST['port']);

			if (IoE($host, $port))
			{
				if(getStatus($host, $port))
				{
					# ok
				}
				else
				{
					set_error($txt['txt3']." - ".$txt['txt6'], true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
  				writeSession('status_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt3']." - ".$txt['txt7']."</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
  			}
			else
			{
				process_error('status_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
	elseif ($validate == 'votifier_tester')
	{
		if (isset($_POST['submit']) && requestMethod('POST'))
		{
			$host = safeInput($_POST['host']);
			$port = safeInput($_POST['port']);
			$key = safeInput($_POST['key']);
			$username = safeInput($_POST['username']);

			if (IoE($host, $port, $key, $username))
			{
				if(Votifier($key, $host, $port, $username))
				{
					# ok
				}
				else
				{
					set_error($txt['txt104']." - ".$txt['txt6'], true);
				}
			}
			else
			{
				set_error($txt['txt182'], true);
			}
				
			if (!$error)
			{
  				writeSession('votifier_success', "
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'>".$txt['txt118']."</div>
					<p>".$txt['txt104']." - ".$txt['txt7']."</p>
				</div>");
				redirect(safeInput($_SERVER['HTTP_REFERER']));
  			}
			else
			{
				process_error('votifier_error', safeInput($_SERVER['HTTP_REFERER']));
			}
		}
	}
}
?>