<?php
	use xPaw\HytalePing;
	use xPaw\HytalePingException;
	// xPaw Querying
	require __DIR__ . '/cron/xPaw/src/HytalePing.php';
	require __DIR__ . '/cron/xPaw/src/HytalePingException.php';

//$filehandle = fopen('cron/lock.txt', "w+");
//if (flock($filehandle, LOCK_EX | LOCK_NB)) {		
	require_once dirname(__FILE__).'/core.php';
	require_once dirname(__FILE__).'/cron/GameQ/Autoloader.php';
	require_once dirname(__FILE__).'/cron/mcstat.php';
	require_once dirname(__FILE__).'/cron/mcformat.php';

	global $db;
	$servers = array();
	$serversTwo = array();
	$servers_data = getArray("SELECT id, host, port, uptime FROM php_servers");


	foreach($servers_data as $key => $data)
	{
		$uptime = $data['uptime'];
		$hostData = $data['host'];
		$port = $data['port'];

		if (preg_match("/^(\d[\d.]+):([0-9]+)\b/", $hostData, $matches)) {
			$hostNext = $matches[1];
		} else {
			$hostNext = $hostData;
		}

		if(filter_var($hostNext, FILTER_VALIDATE_IP)) {
			$host = $hostNext.':'.$port;
		} elseif ((gethostbyname($hostNext) != $hostNext) && (gethostbyname($hostNext) != gethostbyname(gethostname()))) {
			$host = gethostbyname($hostNext).':'.$port;
		} else {

			set_error_handler(function() { });
			$record = dns_get_record('_Hytale._tcp.' . $hostNext, DNS_SRV);
			if(isset($record[0]['target']))
			{
				$host = $record[0]['target'].':'.$port;
			} else {
				$host = '1.1.1.1:'.$port;
			}
			restore_error_handler();
		}
		
		if ($host) {
			array_push(
				$servers, 
				array(
					'id' => $data['id'], 
					'type' => 'Hytale', 
					'host' => $host
				)
			);
			array_push(
				$serversTwo, 
				array( 
					'ip' => $hostData,
					'port' => $data['port']
				)
			);
		} else {
			# ok
		}
	}
	
	// Set these settings by yourself. Different hosting might need different settings. Read more here - https://github.com/Austinb/GameQ

	$GameQ = new \GameQ\GameQ();
	$GameQ->addFilter('normalise'); 
	$GameQ->addFilter('secondstohuman');
	$GameQ->setOption('timeout', 10);
	//$GameQ->setOption('stream_timeout', 1500000);
	//$GameQ->setOption('write_wait', 0);
	
	    //print_r($pieces);
	    
		$GameQ->addServers($servers);
		$results = $GameQ->process();
		
		$i = -1;
		foreach ($results as $id => $ifo) {
		    $i++;
			$hostname = $ifo['gq_hostname'];
			$serverId = $servers[$i]['id'];
			if (!empty($hostname)) {
				query("UPDATE php_servers SET players_online = :online, players_total = :total, lastCheck = :last, status = '1' WHERE id = :id LIMIT 1", array(':online' => $ifo['gq_numplayers'], ':total' => $ifo['gq_maxplayers'], ':last' => date('Y-m-d H:i'),':id' => $serverId));	
				echo '-------------<br><font color="green">Success <strong>1st</strong> time</font> - '.$serversTwo[$i]['ip'].' | Players - '.$ifo['gq_numplayers'].'/'.$ifo['gq_maxplayers'].'<br>-------------<br>';
			} else {
				echo '-------------<br><font color="red">Error <strong>1st</strong> time</font> - '.$serversTwo[$i]['ip'].'<br>-------------<br>';
				$hostname = $serversTwo[$i]['ip'];
				$portGet = $serversTwo[$i]['port'];
				//if ($hostname) {
				    
					//print_r( $Query->Query() );
					try
					{
						$Query = new HytalePing( $hostname, $portGet, 5);
						//foreach ($Query->Query()  as $server) {
						$server = $Query->Query();
						$max =  $server['players']['max'];
						$online = $server['players']['online'];	
						
						query("UPDATE php_servers SET players_online = :online, players_total = :total, lastCheck = :last, status = '1' WHERE id = :id LIMIT 1", array(':online' => $online, ':total' => $max, ':last' => date('Y-m-d H:i'), ':id' => $serverId));
						echo '-------------<br><font color="green">Success <strong>2nd</strong> time</font> - '.$hostname.' | Players - '.$online.'/'.$max.'<br>-------------<br>';
							
						//}
					}
					catch( HytalePingException $e )
					{
						if ($uptime < 1) {
							# ok
						} else {
							query("UPDATE php_servers SET lastCheck = :last, uptime = (uptime - 0.1), players_online = '0', players_total = '0', status = '0' WHERE id = :id LIMIT 1", array(':last' => date('Y-m-d H:i'),':id' => $serverId));
						}
						echo '-------------<br><font color="red">Error <strong>2nd</strong> time</font> - '.$hostname.'<br>-------------<br>';
					}
				//}
			}
		}
	//flock($filehandle, LOCK_UN);  // don't forget to release the lock
//} else {
	//print 'Nope';
	//exit;
//}
//fclose($filehandle);
