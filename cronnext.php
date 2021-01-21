<?php	
	require_once('core.php');
	global $db;
	$json = array();	
	$servers_data = getArray("SELECT id, players_online, votes, uptime FROM php_servers ORDER BY votes DESC, status DESC, players_online DESC, name ASC");
	$i = 0;
	if ($servers_data) {
		foreach($servers_data as $key => $data)
		{
			$i += 1;
			// Check if .json file exists
			if (file_exists(dirname(__FILE__).'/serversData/'.$data['id'].'.json')) {
				// Open & Decode json file & its data
				$file = file_get_contents(dirname(__FILE__).'/serversData/'.$data['id'].'.json');
				$json = json_decode($file, true);
				// Push New Data to "Rows" array
				array_push(
					$json['rows'], 
					array(
						'c' =>
						array( 
							array('v' => date('M d'), 'f' => null),
							array('v' => $data['votes'], 'f' => null), 
							array('v' => $i, 'f' => null), 
							array('v' => $data['players_online'], 'f' => null),
							array('v' => round($data['uptime']), 'f' => null)
						)
					)
				);
				// Save New formated .json file
				if ($json) {
					file_put_contents(dirname(__FILE__).'/serversData/'.$data['id'].'.json', json_encode($json));
				} else {
					echo 'Add Servers First to query statistics!';
				}
				
			} else {
				// Create Data File if doesn't exist
				$oldjson = dirname(__FILE__).'/cron/DO_NOT_DELETE.json';
				$newjson = dirname(__FILE__).'/serversData/'.$data['id'].'.json';
				copy($oldjson, $newjson);
				
				// Open & Decode json file & its data
				$file = file_get_contents(dirname(__FILE__).'/serversData/'.$data['id'].'.json');
				$json = json_decode($file, true);
				
				// Push New Data to "Rows" array
				array_push(
					$json['rows'], 
					array(
						'c' =>
						array( 
							array('v' => date('M d'), 'f' => null),
							array('v' => $data['votes'], 'f' => null), 
							array('v' => $i, 'f' => null), 
							array('v' => $data['players_online'], 'f' => null),
							array('v' => round($data['uptime']), 'f' => null)
						)
					)
				);
				// Save New formated .json file
				if ($json) {
					file_put_contents(dirname(__FILE__).'/serversData/'.$data['id'].'.json', json_encode($json));
				} else {
					echo 'Add Servers First to query statistics!';
				}
			}
		}
		echo "Executed successfully!";
	}
