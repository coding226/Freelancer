<?php
if(!defined('TOP_STARTED')) exit('Site security activated !');
function get($get)
{

require LANG_PATH;

$page = safeInput(isset($_GET['page'])) ? safeInput($_GET['page']) : '';
$pg = safeInput(isset($_GET['pg'])) ? safeInput($_GET['pg']) : '';

if ($get == 'toplist') {
	// List Order Part START
	$orderCookie = safeInput(isset($_COOKIE['listOrder'])) ? safeInput($_COOKIE['listOrder']) : '';
	if (isset($orderCookie)) {
		if ($orderCookie == 1) {
			$order = 'name ASC, votes DESC, status DESC';
			$orderByText = 'Server name [A - Z]';
		} elseif ($orderCookie == 2) {
			$order = 'name DESC, votes DESC, status DESC';
			$orderByText = 'Server name [Z - A]';
		} elseif ($orderCookie == 3) {
			$order = 'players_online DESC, votes DESC, status DESC, name ASC';
			$orderByText = 'Players online [High - Low]';
		} elseif ($orderCookie == 4) {
			$order = 'players_online ASC, votes DESC, status DESC, name ASC';
			$orderByText = 'Players online [Low - High]';
		} elseif ($orderCookie == 6) {
			$order = 'votes ASC, status DESC, name ASC';
			$orderByText = 'Votes [Low - High]';
		} elseif ($orderCookie == 7) {
			$order = 'status DESC, votes DESC, status DESC, name ASC';
			$orderByText = 'Online Servers First';
		} elseif ($orderCookie == 8) {
			$order = 'status ASC, votes DESC, status DESC, name ASC';
			$orderByText = 'Offline Servers First';
		} elseif ($orderCookie == 9) {
			$order = 'date DESC, votes DESC, status DESC, name ASC';
			$orderByText = 'Date (New - Old)';
		} elseif ($orderCookie == 10) {
			$order = 'date ASC, votes DESC, status DESC, name ASC';
			$orderByText = 'Server name [A - Z]';
		} else {
			$order = 'votes DESC, status DESC, name ASC';
			$orderByText = 'Date (Old - New)';
		}
	} else {
		$order = 'votes DESC, status DESC, players_online DESC, name ASC';
		$orderByText = '';
	}
	// List Order Part END

	// Category START
	$category = safeInput(isset($_GET['category'])) ? safeInput($_GET['category']) : '';
	$query = safeInput(isset($_GET['query'])) ? safeInput($_GET['query']) : '';
	if ($category == 'versions') {
		$where = " WHERE LOWER(REPLACE(version, ' ', '')) LIKE :input";
		$input = '%'.strtolower($query).'%';
		$targetpage = $txt['SITE_LINK'].'/versions/'.$query;
		$versionName = fetchArray("SELECT name FROM php_versions WHERE LOWER(REPLACE(name, ' ', '')) LIKE :input", array(':input' => '%'.strtolower($query).'%'));
		$h2Title = 'Hytale '.$versionName['name'].' '.$txt['txt227'];
		$seoTitle = 'Hytale '.$versionName['name'].' '.$txt['txt227'];
	} elseif ($category == 'types') {
		$where = " WHERE LOWER(REPLACE(types, ' ', '')) LIKE :input";
		$input = '%'.strtolower($query).'%';
		$targetpage = $txt['SITE_LINK'].'/types/'.$query;
		$typeName = fetchArray("SELECT name FROM php_types WHERE LOWER(REPLACE(name, ' ', '')) LIKE :input LIMIT 1", array(':input' => '%'.strtolower($query).'%'));
		$h2Title = 'Hytale '.$typeName['name'].' '.$txt['txt227'];
		$seoTitle = 'Hytale '.$typeName['name'].' '.$txt['txt227'];
	} elseif ($category && ($category != 'types' || $category != 'versions')) {
		redirect($txt['SITE_LINK']);
	} else {
		$where = ' WHERE id > :input';
		$input = 0;
		$targetpage = $txt['SITE_LINK'];
		$h2Title = $txt['txt10'];
		$seoTitle = SITE_name;
	}
	// Category END

	essentialOne('list', $seoTitle);

	if ($pg < 2 && !$category) {
?>
	<div class="ui large header"><?php echo $txt['txt0']; ?></div>
  	<table class="ui <?php echo setti_tables; ?> table servers">
  		<thead>
    		<tr>
    			<th class="rank default-color"><i class="thumbs up outline icon"></i> <?php echo $txt['txt1']; ?></th>
    			<th class="name default-color"><i class="info circle icon"></i> <?php echo $txt['txt2']; ?></th>
    			<th class="server default-color"><i class="server icon"></i> <?php echo $txt['txt3']; ?></th>
     			<th class="players default-color"><i class="user icon"></i> <?php echo $txt['txt4']; ?></th>
     			<th class="status default-color"><i class="tachometer alternate icon"></i> <?php echo $txt['txt5']; ?></th>
  			</tr>
		</thead>
  		<tbody>
  		<?php
		$allinfo = getArray("SELECT server FROM php_sponsored WHERE start <= :start AND finish > :finish ORDER BY position DESC LIMIT ".sponsored_setti_two, array(':start' => date("Y-m-d H:i"), ':finish' => date("Y-m-d H:i")));
		if ($allinfo)
		{
			$i = 0;
			foreach($allinfo as $key => $sponsorInfo)
			{
				$id = safeInput($sponsorInfo['server']);

				$info = fetchArray("SELECT seo_name, name, players_online, players_total, host, status, short_description, bannerCode, iconCode FROM php_servers WHERE id = :id", array(':id' => $id));
				$seo_name = safeInput($info['seo_name']);
				$name = safeInput($info['name']);	
				$players_online = safeInput($info['players_online']);	
				$players_total = safeInput($info['players_total']);
				$host = safeInput($info['host']);
				$status = safeInput($info['status']);
				$bannerCode = safeInput($info['bannerCode']);
				$iconCode = safeInput($info['iconCode']);
				$short_description = safeInput($info['short_description']);
				if ($short_description) {
					$short_description = '<p class="description" itemprop="description">'.$short_description.'</p>';
				} else {
					$short_description = '';
				}

				if ($status == 0) {
					$status = '<span class="ui basic red button">'.$txt['txt6'].'</span>';
				} else {
					$status = '<span class="ui basic green button">'.$txt['txt7'].'</span>';
				}

				$i += 1;

				if (!$iconCode) {
					$rank = '<span class="ranking">'.$i.'</span>';
				} else {
					$rank = '<img class="lazyImg" src="/CSS/noBanner.png" data-src="'.SITE_domain.'/icon-'.$id.'-'.$iconCode.'.png" alt="'.$name.'" width="42" height="42" style="margin-left: .5em;">';
				}				
		?>
    		<tr>
      			<td class="rank">
                    <?php echo $rank; ?>
      			</td>
      			<td class="name">
  					<h3 class="server-name">
                		<a href="<?php echo $txt['SITE_LINK']; ?>/server-<?php echo $seo_name; ?>.<?php echo $id; ?>"><?php echo $name; ?></a>
            		</h3>
      			</td>
      			<td class="server">
      				<a href="<?php echo $txt['SITE_LINK']; ?>/server-<?php echo $seo_name; ?>.<?php echo $id; ?>">
  						<img class="lazyImg" src="/CSS/noBanner.png" data-src="<?php echo SITE_domain; ?>/banner-<?php echo $id; ?>-<?php echo $bannerCode; ?>.gif" alt="<?php echo $name; ?>" width="468" height="60">
  					</a>
  					<div class="server-ip">
  					
                		<p><i class="plug icon"></i>Hytale Not Released Yet</p>
		                <div class="copy-action" data-clipboard-text="<?php echo $host; ?>">
		                    <button class="copy-text"><i class="cut icon" aria-hidden="true"></i> <?php echo $txt['txt9']; ?></button>
		                    <button class="copy-success" aria-label="<?php echo $txt['txt9']; ?>"><i class="thumbs up outline icon" aria-hidden="true"></i></button>
		                </div>
            		</div>
            		<?php echo $short_description; ?>
      			</td>
      			<td class="players">
  					<?php echo $players_online; ?>/<?php echo $players_total; ?>
      			</td>
      			<td class="status">
  					<?php echo $status; ?>
      			</td>
   			</tr>
   		<?php
   			}
   		}
   		else {
   		?>
   			<tr>
   				<td colspan="5" class="ui center aligned server no-server">
   					<i class="ban icon"></i> <?php echo $txt['txt8']; ?>
   				</td>
   			</tr>
   		<?php
   		}
   		?>
  		</tbody>
	</table>
<?php
	}
?>
		<h1 class="ui large header"><?php echo $h2Title; ?></h1>
		<hr>
		<button id="toggleSearch" class="ui basic button toggle-search"><i class="sliders horizontal icon"></i> <?php echo $txt['txt11']; ?></button>
		<div class="ui fluid category search insta-search">
			<div class="ui right action left icon input">
				<i class="search icon"></i>
    			<input class="prompt" type="text" placeholder="<?php echo $txt['txt12']; ?>" aria-label="<?php echo $txt['txt12']; ?>" onfocus="insta_search()" onkeydown="insta_search()">
    			<select class="ui compact selection dropdown" id="search_cat" aria-label="<?php echo $txt['txt13']; ?>" onfocus="insta_search()" onchange="insta_search()">
    				<option value="" selected><?php echo $txt['txt13']; ?></option>
					<option value="1"><?php echo $txt['txt14']; ?></option>
				    <option value="2"><?php echo $txt['txt15']; ?></option>
				    <option value="3"><?php echo $txt['txt16']; ?></option>
				    <option value="4"><?php echo $txt['txt17']; ?></option>
				    <option value="5"><?php echo $txt['txt18']; ?></option>
				    <option value="6"><?php echo $txt['txt19']; ?></option>
    			</select>
  			</div>
  			<div class="ui labeled icon top right pointing dropdown button">
  				<i class="linkify icon"></i>
  				<span><?php echo $txt['txt20']; ?></span>
  				<div class="menu">
				    <?php if(numRows("SELECT COUNT(1) FROM php_versions") > 0) { ?><a class="item" href="<?php echo $txt['SITE_LINK']; ?>/versions" title="<?php echo $txt['txt20_1']; ?>"><i class="list alternate outline icon"></i> <?php echo $txt['txt20_1']; ?></a><?php } ?>
				     <?php if(numRows("SELECT COUNT(1) FROM php_types") > 0) { ?><a class="item" href="<?php echo $txt['SITE_LINK']; ?>/types" title="<?php echo $txt['txt20_2']; ?>"><i class="list alternate outline icon"></i> <?php echo $txt['txt20_2']; ?></a><?php } ?>
				    <a class="item" href="<?php echo $txt['SITE_LINK']; ?>/status-checker" title="<?php echo $txt['txt20_3']; ?>"><i class="toggle off icon"></i> <?php echo $txt['txt20_3']; ?></a>
				    <a class="item" href="<?php echo $txt['SITE_LINK']; ?>/votifier-tester" title="<?php echo $txt['txt20_4']; ?>"><i class="check square outline icon"></i> <?php echo $txt['txt20_4']; ?></a>
				   	<a class="item" href="<?php echo $txt['SITE_LINK']; ?>/cp/add" title="<?php echo $txt['txt86']; ?>"><i class="plus square outline icon"></i> <?php echo $txt['txt86']; ?></a>
  				</div>
			</div>
			<div class="ui labeled icon top right pointing dropdown button middle">
  				<i class="sliders horizontal icon"></i>
  				<span><?php echo $txt['txt21']; ?></span>
  				<div id="orderBy" class="menu">
				    <div class="item" data-value="1"><?php echo $txt['txt22']; ?></div>
				    <div class="item" data-value="2"><?php echo $txt['txt23']; ?></div>
				    <div class="item" data-value="3"><?php echo $txt['txt24']; ?></div>
				    <div class="item" data-value="4"><?php echo $txt['txt25']; ?></div>
				    <div class="item" data-value="5"><?php echo $txt['txt26']; ?></div>
				    <div class="item" data-value="6"><?php echo $txt['txt27']; ?></div>
				    <div class="item" data-value="7"><?php echo $txt['txt28']; ?></div>
				    <div class="item" data-value="8"><?php echo $txt['txt29']; ?></div>
				    <div class="item" data-value="9"><?php echo $txt['txt30']; ?></div>
				    <div class="item" data-value="10"><?php echo $txt['txt31']; ?></div>
  				</div>
			</div>
  			<div class="results"></div>
		</div>
		<?php 
		if ($orderCookie) {
		?>
		<div class="ui visible tiny icon message">
			<i id="resetOrder" class="trash alternate outline icon"></i>
  			<div class="content">
  				<i class="sort alphabet up icon"></i> <?php echo $txt['txt32']; ?> <strong><?php echo $orderByText; ?></strong>
  			</div>
		</div>
		<?php
		}
		?>
		<hr>
  		<table class="ui <?php echo setti_tables; ?> table servers">
    		<thead>
    		<tr>
    			<th class="rank default-color"><i class="thumbs up outline icon"></i> <?php echo $txt['txt1']; ?></th>
    			<th class="name default-color"><i class="info circle icon"></i> <?php echo $txt['txt2']; ?></th>
    			<th class="server default-color"><i class="server icon"></i> <?php echo $txt['txt3']; ?></th>
     			<th class="players default-color"><i class="user icon"></i> <?php echo $txt['txt4']; ?></th>
     			<th class="status default-color"><i class="tachometer alternate icon"></i> <?php echo $txt['txt5']; ?></th>
  			</tr>
		</thead>
  		<?php
  		// Pagination
  		$total_pages = numRows("SELECT COUNT(1) FROM php_servers ".$where, array(':input' => $input));
  		$limit = serversPerPage; 
		$stages = 1;
		if($pg) {
			$start = ($pg - 1) * $limit;
		} else {
			$start = 0;
		}
		if ($pg == 0) {
			$pg = 1;
		}
		$prev = $pg - 1;
		$next = $pg + 1;
		$lastpage = ceil($total_pages/$limit);
		if ($pg == 1) {
			$rel_prev = '';
		} else {
			if ($prev == 1) {
				$rel_prev = '<link rel="prev" href="'.$targetpage.'">';
			} else {
				$rel_prev = '<link rel="prev" href="'.$targetpage.'/pg.'.$prev.'">';
			}
		}
		if ($lastpage > $pg) {
			$rel_next = '<link rel="next" href="'.$targetpage.'/pg.'.$next.'">';
		} else {
			$rel_next = '';
		}
		$paginate = '';
		if($lastpage > 1) {
			if ($lastpage < 7 + ($stages * 2)) {
				for ($counter = 1; $counter <= $lastpage; $counter++) {
					if ($counter == $pg) {
						$paginate.= "<span class='item active' itemprop='url'>$counter</span>";
					} else {
						if ($counter == 1) {
							$paginate.= "<a class='item' itemprop='url name' href='$targetpage'>$counter</a>";
						} else {
							$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$counter'>$counter</a>";
						}
					}
				}
			} elseif ($lastpage > 5 + ($stages * 2)) {
				if ($pg < 1 + ($stages * 2)) {
					for ($counter = 1; $counter < 4 + ($stages * 2); $counter++) {
						if ($counter == $pg) {
							$paginate.= "<span class='item active' itemprop='url'>$counter</span>";
						} else {
							if ($counter == 1) {
								$paginate.= "<a class='item' itemprop='url name' href='$targetpage'>$counter</a>";
							} else {
								$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$counter'>$counter</a>";
							}
						}
					}
					$paginate.= "<span class='item'>...</span>";
					$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$lastpage'>$lastpage</a>";
				} elseif ($lastpage - ($stages * 2) > $pg && $pg > ($stages * 2)) {
					$paginate.= "<a class='item' itemprop='url name' href='$targetpage'>1</a>";
					$paginate.= "<span class='item'>...</span>";
					for ($counter = $pg - $stages; $counter <= $pg + $stages; $counter++) {
						if ($counter == $pg) {
							$paginate.= "<span class='item active' itemprop='url'>$counter</span>";
						} else {
							$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$counter'>$counter</a>";
						}
					}
					$paginate.= "<span class='item'>...</span>";
					$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$lastpage'>$lastpage</a>";
				} else {
					$paginate.= "<a class='item' itemprop='url name' href='/'>1</a>";
					$paginate.= "<span class='item'>...</span>";
					for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++) {
						if ($counter == $pg) {
							$paginate.= "<span class='item active' itemprop='url'>$counter</span>";
						} else {
							$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$counter'>$counter</a>";
						}
					}
				}
			}
		}

		if ($lastpage >= $pg || $pg == 1) {
			# ok
		} else {
			redirect($txt['SITE_LINK']);
		}

		if($pg > 1) { $i = ($pg - 1) * $limit; } else { $i = 0; }

  		$sql = "SELECT id, seo_name, name, players_online, players_total, host, status, short_description, bannerCode FROM php_servers ".$where." ORDER BY ".$order." LIMIT ".$start.", ".serversPerPage;
		$allinfo = getArray($sql, array(':input' => $input));
		if ($allinfo)
		{
			foreach($allinfo as $key => $info)
			{

				$id = safeInput($info['id']);
				$seo_name = safeInput($info['seo_name']);
				$name = safeInput($info['name']);	
				$players_online = safeInput($info['players_online']);	
				$players_total = safeInput($info['players_total']);
				$host = safeInput($info['host']);
				$status = safeInput($info['status']);
				$bannerCode = safeInput($info['bannerCode']);
				$short_description = safeInput($info['short_description']);
				if ($short_description) {
					$short_description = '<p class="description" itemprop="description">'.$short_description.'</p>';
				} else {
					$short_description = '';
				}

				if ($status == 0) {
					$status = '<span class="ui basic red button">'.$txt['txt6'].'</span>';
				} else {
					$status = '<span class="ui basic green button">'.$txt['txt7'].'</span>';
				}

				$rank = $i++;
		?>
    		<tr>
      			<td class="rank">
  					<span class="ranking" itemprop="position">
                        <?php echo $i; ?>
                    </span>
      			</td>
      			<td class="name">
      				<a href="<?php echo $txt['SITE_LINK']; ?>/server-<?php echo $seo_name; ?>.<?php echo $id; ?>" itemprop='url'>
	  					<h3 class="server-name" itemprop='name'>
	                		<?php echo $name; ?>
	            		</h3>
            		</a>
      			</td>
      			<td class="server">
      				<a href="<?php echo $txt['SITE_LINK']; ?>/server-<?php echo $seo_name; ?>.<?php echo $id; ?>">
  						<img class="lazyImg" src="/CSS/noBanner.png" data-src="<?php echo SITE_domain; ?>/banner-<?php echo $id; ?>-<?php echo $bannerCode; ?>.gif" alt="<?php echo $name; ?>" width="468" height="60" itemprop='image'>
  					</a>
  					<div class="server-ip">
                		<p><i class="plug icon" aria-hidden="true"></i>Hytale Not Released Yet</p>
		                <div class="copy-action" data-clipboard-text="<?php echo $host; ?>">
		                    <button class="copy-text"><i class="cut icon" aria-hidden="true"></i> <?php echo $txt['txt9']; ?></button>
		                    <button class="copy-success" aria-label="<?php echo $txt['txt9']; ?>"><i class="thumbs up outline icon" aria-hidden="true"></i></button>
		                </div>
            		</div>
            		<?php echo $short_description; ?>
      			</td>
      			<td class="players">
  					<?php echo $players_online; ?>/<?php echo $players_total; ?>
      			</td>
      			<td class="status">
  					<?php echo $status; ?>
      			</td>
   			</tr>
   		<?php
   			}
   		}
   		else {
   		?>
   			<tr>
   				<td colspan="5" class="ui center aligned server no-server">
   					<i class="ban icon"></i> <?php echo $txt['txt8']; ?>
   				</td>
   			</tr>
   		<?php
   		}
   		?>
  		</tbody>
  		<?php
  		if ($paginate) {
  		?>
  		<tfoot>
    		<tr>
    			<th colspan="5">
    				<div class="ui left floated pagination menu" itemscope itemtype="http://schema.org/SiteNavigationElement">
    				<?php echo $paginate; ?>
      				</div>
      				<div class="clearfix"></div>
      			</th>
      		</tr>
      	</tfoot>
		<?php
		} else { }
		?>
	</table>
<?php
} elseif ($get == 'versions') {
	if(numRows("SELECT COUNT(1) FROM php_versions") > 0) {
		# ok
	} else {
		redirect($txt['SITE_LINK']);
	}
	essentialOne(NULL, $txt['txt20_1']);
?>
	<div class="ui segments">
		<div class="ui segment default-color header">
			<i class="list icon"></i> <?php echo $txt['txt20_1']; ?>
		</div>
		<div class="ui tall stacked segment stackable grid list">
			<?php
			$allinfo = getArray("SELECT name FROM php_versions ORDER BY id");
			if ($allinfo)
			{
				foreach($allinfo as $key => $info)
				{
					$name = safeInput($info['name']);
			?>
			<div class="eight wide column">
			  	<a class="fluid ui button" href="<?php echo $txt['SITE_LINK'].'/versions/'.strtolower(str_replace(' ', '', $name)); ?>" title="<?php echo safeInput($name).' Hytale '.$txt['txt227']; ?>">
  					<?php echo $name; ?>
				</a>
			</div>
			<?php
				}
			} else {
			?>
			<div class="sixteen wide column">
			  	<span class="fluid ui button">
  					No Versions
				</span>
			</div>
			<?php
			}
			?>
		</div>
	</div>
<?php
} elseif ($get == 'types') {
	if(numRows("SELECT COUNT(1) FROM php_types") > 0) {
		# ok
	} else {
		redirect($txt['SITE_LINK']);
	}
	essentialOne(NULL, $txt['txt20_2']);
?>
	<div class="ui segments">
		<div class="ui segment default-color header">
			<i class="list icon"></i> <?php echo $txt['txt20_2']; ?>
		</div>
		<div class="ui tall stacked segment stackable grid list">
			<?php
			$allinfo = getArray("SELECT name FROM php_types ORDER BY id");
			if ($allinfo)
			{
				foreach($allinfo as $key => $info)
				{
					$name = safeInput($info['name']);
			?>
			<div class="eight wide column">
			  	<a class="fluid ui button" href="<?php echo $txt['SITE_LINK'].'/types/'.strtolower(str_replace(' ', '', $name)); ?>" title="<?php echo safeInput($name).' Hytale '.$txt['txt227']; ?>">
  					<?php echo $name; ?>
				</a>
			</div>
			<?php
				}
			} else {
			?>
			<div class="sixteen wide column">
			  	<span class="fluid ui button">
  					No Types
				</span>
			</div>
			<?php
			}
			?>
		</div>
	</div>
<?php
} elseif ($get == 'status-checker') {
	essentialOne(NULL, $txt['txt20_3']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="toggle off icon"></i> <?php echo $txt['txt20_3']; ?>
	</div>
	<form class="ui tall stacked segment form" action="#<?php validate('status_checker'); ?>" method="post">
  		<div class="required field">
    		<label><?php echo $txt['txt97']; ?></label>
    		<input type="text" name="host" placeholder="<?php echo $txt['txt97']; ?>" maxlength="50" required>
  		</div>
  		<div class="required field">
    		<label><?php echo $txt['txt99']; ?></label>
    		<input type="text" name="port" placeholder="<?php echo $txt['txt99']; ?>" value="25565" maxlength="6" required>
  		</div>
  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
		<?php
		uniSession('status_success');
		uniSession('status_error');
		?>
	</form>
</div>
<?php
} elseif ($get == 'votifier-tester') {
	essentialOne(NULL, $txt['txt20_4']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="check square outline icon"></i> <?php echo $txt['txt20_4']; ?>
	</div>
	<form class="ui tall stacked segment form" action="#<?php validate('votifier_tester'); ?>" method="post">
  		<div class="required field">
    		<label><?php echo $txt['txt105']; ?></label>
    		<input type="text" name="host" placeholder="<?php echo $txt['txt105']; ?>" maxlength="50" required>
  		</div>
  		<div class="required field">
    		<label><?php echo $txt['txt99']; ?></label>
    		<input type="number" name="port" placeholder="<?php echo $txt['txt99']; ?>" maxlength="6" required>
  		</div>
  		<div class="required field">
    		<label><?php echo $txt['txt106']; ?></label>
    		<textarea name="key" placeholder="<?php echo $txt['txt106']; ?>" rows="4" maxlength="500"></textarea>
  		</div>
  		<div class="required field">
    		<label><?php echo $txt['txt62']; ?></label>
    		<input type="text" name="username" placeholder="<?php echo $txt['txt62']; ?>" maxlength="30" required>
  		</div>
  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
		<?php
		uniSession('votifier_success');
		uniSession('votifier_error');
		?>
	</form>
</div>
<?php
} elseif ($get == 'server') {
	$id = safeInput($_GET['id']);
	$title = safeInput($_GET['title']);
	$sub = safeInput(isset($_GET['sub'])) ? safeInput($_GET['sub']) : '';
	$info = fetchArray('SELECT name, seo_name, owner, host, description, players_online, players_total, status, votes, uptime, website, lastCheck, version, types, country, bannerCode FROM php_servers WHERE id = :id LIMIT 1', array(':id' => $id));
	if(!$info) {
		redirect($txt['SITE_LINK']);
	} else {
		$seo_name = $info['seo_name'];
		if ($title == $seo_name) {
			$name = $info['name'];
			$owner = $info['owner'];
			$owner_info = fetchArray('SELECT name FROM php_users WHERE id = :id LIMIT 1', array(':id' => $owner));
			$owner = $owner_info['name'];
			$host = $info['host'];
			$description = $info['description'] ? $info['description'] : $txt['txt65'];
			$description = preg_replace("/\r\n|\r|\n/",'<br/>', $description);
			$players_online = $info['players_online'];
			$players_total = $info['players_total'];
			$status = $info['status'];
			if ($status == 0) {
				$status = '<span class="ui basic red small button">'.$txt['txt6'].'</span>';
			} else {
				$status = '<span class="ui basic green small button">'.$txt['txt7'].'</span>';
			}
			$votes = $info['votes'];
			$uptime = $info['uptime'];
			$website = $info['website'];
			$rank = rank($id);
			$country = $info['country'];
			$bannerCode = $info['bannerCode'];
			$timeAgo = $info['lastCheck'];
			if ($timeAgo) {
				$lastCheck = timeago($timeAgo);
			} else {
				$lastCheck = $txt['txt66'];
			}

			$s_versions = safeInput($info['version']);
			preg_match_all('%([^,](.*?)[^,]*)%', $s_versions, $versions_extract);

			$s_types = safeInput($info['types']);
			preg_match_all('%([^,](.*?)[^,]*)%', $s_types, $types_extract);
		} else {
			redirect($txt['SITE_LINK'].'/'.$seo_name.'.'.$id);
		}
	}

	essentialOne('server', $name);
?>
	<div class="ui stackable grid">
  		<div class="six wide column">
  			<table class="ui striped table info">
			 	<thead>
			    	<tr>
			      		<th  class="rank default-color" colspan="2">
			      			<i class="server icon"></i> <?php echo $name; ?>
			      		</th>
			    	</tr>
			  	</thead>
  				<tbody id="server">
    				<tr>
      					<td><?php echo $txt['txt33']; ?></td>
      					<td><?php echo $owner; ?></td>
    				</tr>
    				<tr>
      					<td><?php echo $txt['txt5']; ?></td>
      					<td><?php echo $status; ?></td>
    				</tr>
    				<tr>
      					<td><?php echo $txt['txt34']; ?></td>
      					<td><?php echo $host; ?>Hytale Not Release Yet</td>
    				</tr>
    				<?php
    				if ($website) {
    				?>
    				<tr>
      					<td><?php echo $txt['txt35']; ?></td>
      					<td><a href="<?php echo $website; ?>" rel="nofollow noopener" title="<?php echo $name; ?> <?php echo $txt['txt35']; ?>"><?php echo $website; ?></a></td>
    				</tr>
    				<?php 
    				}
    				?>
    				<tr>
      					<td><?php echo $txt['txt36']; ?></td>
      					<td><?php echo $players_online; ?>/<?php echo $players_total; ?></td>
    				</tr>
    				<?php
    				if ($versions_extract[1]) {
    				?>
    				<tr>
      					<td><?php echo $txt['txt37']; ?></td>
      					<td>
      					<?php 
						foreach ($versions_extract[1] as $version) {
							echo '<a href="'.$txt['SITE_LINK'].'/versions/'.strtolower(str_replace(' ', '', $version)).'" class="ui label" title="'.safeInput($version).' Hytale '.$txt['txt227'].'">'.safeInput($version).'</a>';	
						} 
						?>
						</td>
    				</tr>
    				<?php 
    				}
    				?>
    				<tr>
      					<td><?php echo $txt['txt1']; ?></td>
      					<td><?php echo $rank; ?></td>
    				</tr>
    				<tr>
      					<td><?php echo $txt['txt38']; ?></td>
      					<td><?php echo $votes; ?></td>
    				</tr>
    				<tr>
      					<td><?php echo $txt['txt39']; ?></td>
      					<td><?php echo $uptime; ?>%</td>
    				</tr>
    				<tr>
      					<td><?php echo $txt['txt40']; ?></td>
      					<td>
      					<?php echo $lastCheck ?></td>
    				</tr>
    				<tr>
      					<td><?php echo $txt['txt41']; ?></td>
      					<td><i class="<?php echo $country; ?> flag"></i> <?php echo $txt[strtoupper($country)]; ?></td>
    				</tr>
    				<?php
    				if ($types_extract[1]) {
    				?>
    				<tr>
      					<td><?php echo $txt['txt42']; ?></td>
      					<td>
      					<?php 
						foreach ($types_extract[1] as $type) {
							echo '<a href="'.$txt['SITE_LINK'].'/types/'.strtolower(str_replace(' ', '', $type)).'" class="ui label" title="'.safeInput($type).' Hytale '.$txt['txt227'].'">'.safeInput($type).'</a>';	
						} 
						?>
						</td>
    				</tr>
    				<?php 
    				}
    				if (shareThis) {
    				?>
    				<tr>
    					<td><?php echo $txt['share']; ?><td>
    					<div class="sharethis-inline-share-buttons"></div>
    				</tr>
    				<?php
    				}
    				?>
  				</tbody>
			</table>
  		</div>
  		<div class="ten wide column" id="serverPage">
  			<button class="fluid ui top attached button toggle-button" id="toggleServerMenu">
				<i class="bars icon"></i> <?php echo $txt['txt43']; ?>
			</button>
  			<div class="ui top attached tabular menu default-color nav-menu">
  				<a class="<?php if (!$sub) { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK'].'/server-'.$seo_name.'.'.$id; ?>" title="<?php echo $name; ?> <?php echo $txt['txt44']; ?>"><i class="info circle icon"></i> <?php echo $txt['txt44']; ?></a>
  				<a class="<?php if ($sub == 'statistics') { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK'].'/server-'.$seo_name.'.'.$id; ?>/statistics" title="<?php echo $name; ?> <?php echo $txt['txt45']; ?>"><i class="chart bar icon"></i> <?php echo $txt['txt45']; ?></a>
  				<a class="<?php if ($sub == 'banners') { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK'].'/server-'.$seo_name.'.'.$id; ?>/banners" title="<?php echo $name; ?> <?php echo $txt['txt46']; ?>"><i class="image outline icon"></i> <?php echo $txt['txt46']; ?></a>
  				<a class="<?php if ($sub == 'vote') { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK'].'/server-'.$seo_name.'.'.$id; ?>/vote" title="<?php echo $name; ?> <?php echo $txt['txt47']; ?>"><i class="plus circle icon"></i> <?php echo $txt['txt47']; ?></a>
			</div>
			<div class="ui bottom attached active tab segment">
				<?php
				if ($sub == 'banners') {
				?>
				<p class="center-element"><img src="" alt="" width="468" height="60" class="banner" id="imagePreview"></p>
				<?php
				} else {
				?>
				<p class="center-element"><img src="/banner-<?php echo $id; ?>-<?php echo $bannerCode; ?>.gif" alt="<?php echo $name; ?>" width="468" height="60" class="banner"></p>
				<?php
				}
				?>
				<hr>
				<?php
				if (!$sub) {
				?>
				<p><?php echo $description; ?></p>
				<?php
				} elseif ($sub == 'statistics') {
					//if ('/serversData/'.$id.'.json')) {
				?>
				<div id="serverStats"></div>
				<?php
					//} else {
					//	echo 'Server in new!';
					//}
				} elseif ($sub == 'banners') {
				?>
				<form class="ui form" action="#" method="post">
					<input type="hidden" name="link" value="<?php echo $txt['SITE_LINK'].'/'.$seo_name.'.'.$id.'/vote'; ?>">
					<input type="hidden" name="image" value="<?php echo SITE_domain; ?>/votebanner-<?php echo $id; ?>-">
					<div class="field">
			    		<label><?php echo $txt['txt54']; ?></label>
			    		<select name="colorScheme" onchange="code();" onkeyup="code();">
			    			<option value="4D5988"><?php echo $txt['txt55']; ?></option>
			    			<option value="f7040d"><?php echo $txt['txt56']; ?></option>
			    			<option value="2a9609" selected><?php echo $txt['txt57']; ?></option>
			    			<option value="ff9719"><?php echo $txt['txt58']; ?></option>
			    			<option value="ead40b"><?php echo $txt['txt59']; ?></option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label><?php echo $txt['txt60']; ?></label>
			    		<input type="text" placeholder="<?php echo $txt['txt60']; ?>" value="<?php echo $txt['txt60']; ?>" id="htmlCode" readonly>
			  		</div>
			  		<div class="field">
  						<label><?php echo $txt['txt61']; ?></label>
			    		<input type="text" placeholder="<?php echo $txt['txt61']; ?>" value="<?php echo $txt['txt61']; ?>" id="bbCode" readonly>
  					</div>
				</form>
				<?php
				} elseif ($sub == 'vote') {
				?>
				<p>
				<form class="ui form" action="#<?php validate('vote'); ?>" method="post" id="main-content">
			  		<div class="field">
			    		<label><?php echo $txt['txt62']; ?></label>
			    		<input type="hidden" name="id" value="<?php echo $id; ?>">
			    		<input type="text" name="username" placeholder="<?php echo $txt['txt62']; ?>" maxlength="30" required>
			  		</div>
			  		<div class="field">
			       		<img id="captcha" src="/captcha.png" width="160" height="45" border="1" style="width: 100%; height: auto; max-width: 200px; display: block; margin-bottom: .5em">
			       		<a href="#" onclick="document.getElementById('captcha').src = '/captcha.png?' + Math.random(); document.getElementById('imageVerification').value = ''; return false;">
							<?php echo $txt['refresh']; ?>
						</a>
			      	</div>
			      	<div class="field">
			       		<input type="text" name="captcha" placeholder="Captcha" maxlength="8" required>
			      	</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt63']; ?></button>
			  		<a class="ui basic button" href="<?php echo $txt['SITE_LINK'].'/server-'.$seo_name.'.'.$id; ?>"><i class="arrow alternate circle left outline icon"></i> <?php echo $txt['txt64']; ?></a>
					<?php
					uniSession('vote_error');
					uniSession('vote_success');
					?>
				</form>
				</p>
				<p><?php echo $description; ?></p>
				<?php
				} else {
					redirect($txt['SITE_LINK'].'/'.$seo_name.'.'.$id);
				}
				?>
			</div>
  		</div>
	</div>
<?php
} elseif ($get == 'login') {
	essentialOne(NULL, $txt['txt67']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="key icon"></i> <?php echo $txt['txt67']; ?>
	</div>
	<form class="ui tall stacked segment form" action="#<?php validate('login'); ?>" method="post">
		<?php
		if (maintenance) {
		?>
		<div class='ui error message'>
			<i class='close icon'></i>
			<div class='header'><?php echo $txt['txt230']; ?></div>
			<p><?php echo $txt['txt231']; ?></p>
		</div>
		<?php
		}
		?>
  		<div class="field">
    		<label><?php echo $txt['txt68']; ?></label>
    		<input type="text" name="username" placeholder="<?php echo $txt['txt68']; ?>" required>
  		</div>
  		<div class="field">
    		<label><?php echo $txt['txt69']; ?></label>
    		<input type="password" name="password" placeholder="<?php echo $txt['txt69']; ?>" required>
  		</div>
  		<div class="field">
    		<p><?php echo $txt['txt70']; ?></p>
  		</div>
  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt67']; ?></button>
		<a class="ui basic button" href="<?php echo $txt['SITE_LINK']; ?>/register"><?php echo $txt['txt71']; ?></a>
		<?php
		uniSession('login_error');
		uniSession('register_success');
		uniSession('recover_two_success');
		?>
	</form>
</div>
<?php
} elseif ($get == 'register') {
	essentialOne(NULL, $txt['txt71']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="user plus icon"></i> <?php echo $txt['txt71']; ?>
	</div>
	<form class="ui tall stacked segment form captcha-form" action="#<?php validate('register'); ?>" method="post">
  		<div class="field">
    		<label><?php echo $txt['txt68']; ?></label>
    		<input type="text" name="username" placeholder="<?php echo $txt['txt68']; ?>" maxlength="16" required>
  		</div>
  		<div class="field">
    		<label><?php echo $txt['txt72']; ?></label>
    		<input type="email" name="email" placeholder="<?php echo $txt['txt72']; ?>" maxlength="100" required>
  		</div>
  		<div class="field">
    		<label><?php echo $txt['txt69']; ?></label>
    		<input type="password" name="password" placeholder="<?php echo $txt['txt69']; ?>" required>
  		</div>
  		<div class="field">
    		<label><?php echo $txt['txt73']; ?></label>
    		<input type="password" name="cn-password" placeholder="<?php echo $txt['txt73']; ?>" required>
  		</div>
  		<div class="field">
    		<div class="ui checkbox">
     			<input type="checkbox" tabindex="0" name="agreement">
     			<label><?php echo $txt['txt74']; ?></label>
    		</div>
  		</div>
  		<div class="field">
       		<img id="captcha" src="/captcha.png" width="160" height="45" border="1" style="width: 100%; height: auto; max-width: 200px; display: block; margin-bottom: .5em">
       		<a href="#" onclick="document.getElementById('captcha').src = '/captcha.png?' + Math.random(); document.getElementById('imageVerification').value = ''; return false;">
				Refresh Image
			</a>
      	</div>
      	<div class="field">
       		<input type="text" name="captcha" placeholder="Captcha" maxlength="8" required>
      	</div>
  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt71']; ?></button>
		<?php
		uniSession('register_error');
		?>
	</form>
</div>
<?php
} elseif ($get == 'recover-password') {
	$id = safeInput(isset($_GET['id'])) ? safeInput($_GET['id']) : '';
	essentialOne(NULL, $txt['txt75']);
?>
	<div class="ui segments">
		<?php
		if (!$id) {
		?>
		<div class="ui segment default-color header">
			<i class="unlock alternate icon"></i> <?php echo $txt['txt75']; ?>
		</div>
		<form class="ui tall stacked segment form" action="#<?php validate('recover-password'); ?>" method="post">
  			<div class="field">
    			<label><?php echo $txt['txt72']; ?></label>
    			<input type="email" name="email" placeholder="<?php echo $txt['txt72']; ?>" required>
  			</div>
  			<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt76']; ?></button>
  			<?php
				uniSession('recover_error');
				uniSession('recover_success');
			?>
		</form>
		<?php
		} else {
			$id_check = numRows("SELECT COUNT(*) FROM php_users WHERE recovery_id = :id", array(':id' => $id));
			if ($id_check == 1) 
			{
				# ok
			} else {
				set_error("Invalid password recovery link", true);
				process_error('recover_error', $txt['SITE_LINK'].'/recover-password');
			}

			$recoverydate = date("Y-m-d H:i");
			$recovery_check = numRows("SELECT COUNT(*) FROM php_users WHERE recovery_id = :id AND recovery_date <= :date", array(':id' => $id, ':date' => $recoverydate));
			if ($recovery_check == 1) {
				query("UPDATE php_users SET recovery_id = '', recovery_date = '' WHERE recovery_id = :id LIMIT 1", array(':id' => $id));
				set_error("Expired password recovery link", true);
				process_error('recover_error', $txt['SITE_LINK'].'/recover-password');
			} else {
				# ok
			}
		?>
		<div class="ui segment default-color header">
			<i class="unlock alternate icon"></i> <?php echo $txt['txt77']; ?>
		</div>
		<form class="ui tall stacked segment form" action="#<?php validate('recover-password-step2'); ?>" method="post">
  			<div class="field">
    			<label><?php echo $txt['txt78']; ?></label>
    			<input type="hidden" value="<?php echo $id; ?>" name="recovery-id">
    			<input type="password" name="password" placeholder="<?php echo $txt['txt78']; ?>">
  			</div>
  			<div class="field">
    			<label><?php echo $txt['txt79']; ?></label>
    			<input type="password" name="password-confirm" placeholder="<?php echo $txt['txt79']; ?>">
  			</div>
  			<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt76']; ?></button>
  			<?php
				uniSession('recover_two_error');
			?>
		</form>
		<?php	
		}
		?>
	</div>
<?php
} elseif ($get == 'contacts') {
	essentialOne(NULL, $txt['txt80']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="envelope icon"></i> <?php echo $txt['txt80']; ?>
	</div>
	<form class="ui tall stacked segment form" action="#<?php validate('contacts'); ?>" method="post">
  		<div class="field">
    		<label><?php echo $txt['txt81']; ?></label>
    		<input type="text" name="name" placeholder="<?php echo $txt['txt81']; ?>" required>
  		</div>
  		<div class="field">
    		<label><?php echo $txt['txt72']; ?></label>
    		<input type="email" name="email" placeholder="<?php echo $txt['txt72']; ?>" required>
  		</div>
  		<div class="field">
    		<label><?php echo $txt['txt82']; ?></label>
   			<textarea rows="4" name="message" placeholder="<?php echo $txt['txt82']; ?>" required></textarea>
		</div>
		<div class="field">
       		<img id="captcha" src="/captcha.png" width="160" height="45" border="1" style="width: 100%; height: auto; max-width: 200px; display: block; margin-bottom: .5em">
       		<a href="#" onclick="document.getElementById('captcha').src = '/captcha.png?' + Math.random(); document.getElementById('imageVerification').value = ''; return false;">
				Refresh Image
			</a>
      	</div>
      	<div class="field">
       		<input type="text" name="captcha" placeholder="Captcha" maxlength="8" required>
      	</div>
		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt83']; ?></button>
		<?php
		uniSession('contacts_error');
		uniSession('contacts_success');
		?>
	</form>
</div>
<?php
} elseif (logged() && $get == 'cp') {
	$sub = safeInput(isset($_GET['sub'])) ? safeInput($_GET['sub']) : '';
	essentialOne(NULL, $txt['txt222']);
	$userSettings = fetchArray('SELECT rank FROM php_users WHERE id = :id LIMIT 1', array(':id' => safeInput(readSession('user_id'))));
?>
<div class="ui stackable grid" style="margin-top: 1.5em">
	<div class="row">
  		<div class="five wide column" id="cpMenu">
  			<button id="toggleSearch" class="ui basic button toggle-search"><i class="bars icon"></i> <?php echo $txt['txt84']; ?></button>
      		<div class="ui vertical fluid tabular menu insta-search" style="padding: 0 10px;">
			    <a class=" <?php if(!$sub || $sub == 'server') { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp">
			        <i class="server icon"></i> <?php echo $txt['txt85']; ?>
			    </a>
			    <a class="<?php if($sub == 'add') { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/add">
			        <i class="plus icon"></i> <?php echo $txt['txt86']; ?>
			    </a>
			    <a class="<?php if($sub == 'sponsored') { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/sponsored">
			        <i class="star icon"></i> <?php echo $txt['txt87']; ?>
			    </a>
			    <a class="<?php if($sub == 'account') { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/account">
			        <i class="cog icon"></i> <?php echo $txt['txt88']; ?>
			    </a>
			    <?php
			    if ($userSettings['rank'] == 1) {
			    ?>
			    <a class="<?php if($sub == 'admin') { ?>active <?php } else { } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin">
			        <i class="user md icon"></i> Administration
			    </a>
			    <?php
			    }
			    ?>
    		</div>
  		</div>
    	<div class="eleven wide stretched column">
			<div class="ui segment">
			<?php
			if (!$sub) {
				uniSession('add_success');
				uniSession('edit_success');
			?>
			<a class="ui right floated button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/add">
  				<i class="plus icon"></i> <?php echo $txt['txt86']; ?>
			</a>
			<div class="clearfix"></div>
			<hr style="margin-bottom: 0!important">
			<table class="ui very basic table servers" id="cp">
		  		<thead>
		    		<tr>
		    			<th class="server"><i class="server icon"></i> <?php echo $txt['txt3']; ?></th>
		     			<th class="status"><i class="cog icon"></i> <?php echo $txt['txt89']; ?></th>
		  			</tr>
				</thead>
		  		<tbody>
		  		<?php
				$allinfo = getArray("SELECT id, seo_name, name, host, bannerCode FROM php_servers WHERE owner = :owner ORDER BY votes DESC, status DESC, name ASC", array(':owner' => safeInput(readSession('user_id'))));
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
						$id = safeInput($info['id']);
						$seo_name = safeInput($info['seo_name']);
						$name = safeInput($info['name']);	
						$host = safeInput($info['host']);
						$bannerCode = safeInput($info['bannerCode']);
				?>
	    		<tr>
	      			<td class="server">
	      				<a href="<?php echo $txt['SITE_LINK']; ?>/server-<?php echo $seo_name; ?>.<?php echo $id; ?>">
		      				<div class="name">
			      				<h3 class="server-name">
			                		<i class="linkify icon"></i> <?php echo $name; ?>
			            		</h3>
		            		</div>
	  					</a>
	            		<div class="serverActions">
	            			<div class="clearfix"></div>
		  					<a href="<?php echo $txt['SITE_LINK']; ?>/cp/server/edit.<?php echo $id; ?>" style="margin-right: 1em">
		  						<?php echo $txt['txt90']; ?> <i class="pencil alternate icon"></i>
		  					</a>
		  					<a href="<?php echo $txt['SITE_LINK']; ?>/cp/server/delete.<?php echo $id; ?>" data-confirm="<?php echo $txt['txt91']; ?>" class="confirm">
		  						<?php echo $txt['txt92']; ?> <i class="trash alternate icon"></i>
		  					</a>
		  					<div class="clearfix"></div>
	      				</div>
	      			</td>
	      			<td class="status">
	  					<a href="<?php echo $txt['SITE_LINK']; ?>/cp/server/edit.<?php echo $id; ?>" style="margin-right: .5em" data-tooltip="<?php echo $txt['txt93']; ?>">
	  						<i class="pencil alternate icon"></i>
	  					</a>
	  					<a href="<?php echo $txt['SITE_LINK']; ?>/cp/server/delete.<?php echo $id; ?>" data-confirm="<?php echo $txt['txt91']; ?>" class="confirm" data-tooltip="<?php echo $txt['txt94']; ?>">
	  						<i class="trash alternate icon"></i>
	  					</a>
	      			</td>
	   			</tr>
		   		<?php
		   			}
		   		}
		   		else {
		   		?>
	   			<tr>
	   				<td colspan="2" class="ui center aligned server no-server">
	   					<i class="ban icon"></i><?php echo $txt['txt8']; ?>
	   				</td>
	   			</tr>
		   		<?php
		   		}
		   		?>
  				</tbody>
			</table>
			<div>
			<i>Your Servers: 
			<strong><?php
			$server_count = numRows("SELECT COUNT(*) FROM php_servers WHERE owner = :owner", array(':owner' => safeInput(readSession('user_id'))));
			echo $server_count;
			?></strong></i>
			</div>
			<?php	
			} elseif ($sub == 'add') {
			?>
			<form class="ui small form" action="#<?php validate('add'); ?>" enctype="multipart/form-data" method="post">
				<h4 class="ui dividing header"><i class="plus square icon"></i> <?php echo $txt['txt86']; ?></h4>
		  		<div class="required field">
		    		<label><?php echo $txt['txt95']; ?></label>
		    		<input type="text" name="name" placeholder="<?php echo $txt['txt95']; ?>" maxlength="40" required>
		    		<small><?php echo $txt['txt96']; ?></small>
		  		</div>
		  		<div class="required field" >
		    		<label><?php echo $txt['txt97']; ?></label>
		    		<input type="text" name="host" placeholder="<?php echo $txt['txt97']; ?>" maxlength="30" disabled>
		    		<small><?php //echo $txt['txt98']; ?></small><small style="color: red">This field will be enabled once Hytale will be launched</small>
		  		</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt99']; ?></label>
		    		<input type="text" name="port" placeholder="<?php echo $txt['txt99']; ?>" placeholder="Query Port" maxlength="6" disabled>
		    		<small style="color: red">This field will be enabled once Hytale will be launched</small>
		  		</div>

		  		<?php
				$allinfo = getArray("SELECT code FROM php_countries");
    			if ($allinfo)
    			{
		  		?>
				<div class="required field">
					<label><?php echo $txt['txt100']; ?></label>
					<div class="ui fluid search selection dropdown">
  						<input type="hidden" name="country" maxlength="4">
  						<i class="dropdown icon"></i>
  						<div class="default text"><?php echo $txt['txt101']; ?></div>
  						<div class="menu">
						<?php
        				foreach($allinfo as $key => $info)
        				{
          					$code = strtolower(safeInput($info['code']));
          					$name = safeInput($info['code']);
          				
          					echo '<div class="item" data-value="'.$code.'"><i class="'.$code.' flag"></i>'.$txt[$name].'</div>';
        				}
  						?>
						</div>
 					</div>
				</div>
				<?php
		  		} else { }
		  		?>

				<div class="field">
		    		<label><?php echo $txt['txt102']; ?></label>
		    		<input type="text" name="website" placeholder="<?php echo $txt['txt102']; ?>" maxlength="150">
		    		<small><?php echo $txt['txt103']; ?></small>
		  		</div>
		  		<div class="field">
    				<div class="ui toggle checkbox">
      					<input type="checkbox" tabindex="0" class="hidden" name="votifier-status" id="votifierToggle" value="1">
      					<label><?php echo $txt['txt104']; ?></label>
    				</div>
    				<div class="votifier">
    					<div class="required field">
				    		<label><?php echo $txt['txt105']; ?></label>
				    		<input type="text" name="votifier-host" placeholder="<?php echo $txt['txt105']; ?>" maxlength="50">
		  				</div>
		  				<div class="required field">
				    		<label><?php echo $txt['txt99']; ?></label>
				    		<input type="text" name="votifier-port" placeholder="<?php echo $txt['txt99']; ?>" value="8192" maxlength="6">
		  				</div>
						<div class="required field">
				    		<label><?php echo $txt['txt106']; ?></label>
				    		<textarea name="votifier-key" placeholder="<?php echo $txt['txt106']; ?>" rows="4" maxlength="500"></textarea>
				  		</div>
    				</div>
  				</div>

		  		<div class="field">
		    		<label><?php echo $txt['txt107']; ?></label>
		    		<input type="file" name="banner">
		    		<small><?php echo $txt['txt108']; ?></small>
		  		</div>

				<div class="field">
		    		<label><?php echo $txt['txt109']; ?></label>
		    		<textarea name="description" placeholder="<?php echo $txt['txt109']; ?>" rows="4" maxlength="1500"></textarea>
		  		</div>

		  		<div class="field">
		    		<label><?php echo $txt['txt110']; ?></label>
		    		<textarea name="short-description" placeholder="<?php echo $txt['txt110']; ?>" rows="2" maxlength="300"></textarea>
		  		</div>

  				<?php
				$allinfo = getArray("SELECT name FROM php_versions");
    			if ($allinfo)
    			{
		  		?>
		  		<div class="field">
					<label><?php echo $txt['txt111']; ?></label>
					<div class="ui fluid search selection dropdown" id="selectVersion">
  						<input type="hidden" name="version" maxlength="20">
  						<i class="dropdown icon"></i>
  						<div class="default text"><?php echo $txt['txt112']; ?></div>
  						<div class="menu">
  						<?php
        				foreach($allinfo as $key => $info)
        				{
          					$name = safeInput($info['name']);
          				?>
          					<div class="item" data-value="<?php echo $name; ?>"></i><?php echo $name; ?></div>
          				<?php
        				}
  						?>
						</div>
 					</div>
		  		</div>
		  		<?php
		  		} else { }

				$allinfo = getArray("SELECT name FROM php_types");
    			if ($allinfo)
    			{
		  		?>
		  		<div class="field">
					<label><?php echo $txt['txt113']; ?></label>
					<div class="ui fluid multiple search normal selection dropdown" id="selectTypes">
  						<input type="hidden" name="types" maxlength="200">
  						<i class="dropdown icon"></i>
  						<div class="default text"><?php echo $txt['txt114']; ?></div>
  						<div class="menu">
						<?php
        				foreach($allinfo as $key => $info)
        				{
          					$name = safeInput($info['name']);
          				?>
          					<div class="item" data-value="<?php echo $name; ?>"></i><?php echo $name; ?></div>
          				<?php
        				}
  						?>
						</div>
 					</div>
 					<small><?php echo $txt['txt115']; ?></small>
		  		</div>
				<?php
		  		} else { }
		  		?>

				<div class="field">
		       		<img id="captcha" src="/captcha.png" width="160" height="45" border="1" style="width: 100%; height: auto; max-width: 200px; display: block; margin-bottom: .5em">
		       		<a href="#" onclick="document.getElementById('captcha').src = '/captcha.png?' + Math.random(); document.getElementById('imageVerification').value = ''; return false;">
						Refresh Image
					</a>
		      	</div>
		      	<div class="field">
		       		<input type="text" name="captcha" placeholder="Captcha" maxlength="8" required>
		      	</div>

				<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
				<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp"><?php echo $txt['txt117']; ?></a>
				<?php
				uniSession('add_error');
				?>
			</form>
			<?php
			} elseif ($sub == 'server') {
				$id = safeInput(isset($_GET['id'])) ? safeInput($_GET['id']) : '';
				$action = safeInput(isset($_GET['action'])) ? safeInput($_GET['action']) : '';

				$security_check = numRows("SELECT COUNT(*) FROM php_servers WHERE id = :id AND owner = :owner", array(':id' => $id, ':owner' => safeInput(readSession('user_id'))));
				$security_check_two = numRows("SELECT COUNT(*) FROM php_users WHERE rank = 1 AND id = :user", array(':user' => safeInput(readSession('user_id'))));
				if ($security_check == 1 || $security_check_two == 1) {
					# ok
				} else {
					redirect($txt['SITE_LINK'].'/cp');
				}

				if ($action == 'edit') {
					if ($security_check) {
						$info = fetchArray("SELECT * FROM php_servers WHERE id = :id AND owner = :owner LIMIT 1", array(':id' => $id, ':owner' => safeInput(readSession('user_id'))));
					} elseif ($security_check_two == 1) {
						$info = fetchArray("SELECT * FROM php_servers WHERE id = :id LIMIT 1", array(':id' => $id));
					} else {
						redirect($txt['SITE_LINK'].'/cp');
					}
					$name = $info['name'];
					$host = $info['host'];
					$port = $info['port'];
					$country = $info['country'];
					$website = $info['website'];
					$votifier_status = $info['votifier_status'];
					$votifier_host = $info['votifier_host'];
					$votifier_port = $info['votifier_port'];
					$votifier_key = $info['votifier_key'];
					$description = $info['description'];
					$short_description = $info['short_description'];
					$version = $info['version'];
					$types = $info['types'];

					$isSponsored = fetchArray("SELECT * FROM php_sponsored WHERE start <= :start AND finish > :finish AND server = :server", array(':start' => date("Y-m-d H:i"), ':finish' => date("Y-m-d H:i"), ':server' => $id));
			?>
			<form class="ui small form" action="#<?php validate('edit'); ?>" enctype="multipart/form-data" method="post">
				<h4 class="ui dividing header"><i class="pencil alternate icon"></i> <?php echo $txt['txt93']; ?></h4>
		  		<div class="required field">
		    		<label><?php echo $txt['txt95']; ?></label>
		    		<input type="hidden" name="id" value="<?php echo $id; ?>">
		    		<?php
					if ($security_check && $security_check_two != 1) {

					} elseif ($security_check_two == 1) {
					?>
					<input type="hidden" name="admin" value="1">
					<?php
					} else {
						redirect($txt['SITE_LINK'].'/cp');
					}
					?>
		    		<input type="text" name="name" placeholder="<?php echo $txt['txt95']; ?>" value="<?php echo $name; ?>" maxlength="40" required>
		    		<small><?php echo $txt['txt96']; ?></small>
		  		</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt97']; ?></label>
		    		<input type="text" name="host" placeholder="<?php echo $txt['txt97']; ?>" value="<?php echo $host; ?>" maxlength="30" required>
		    		<small><?php echo $txt['txt98']; ?></small>
		  		</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt99']; ?></label>
		    		<input type="text" name="port" placeholder="<?php echo $txt['txt99']; ?>" value="<?php echo $port; ?>" maxlength="6" required>
		  		</div>

		  		<?php
				$allinfo = getArray("SELECT code FROM php_countries");
    			if ($allinfo)
    			{
		  		?>
				<div class="required field">
					<label><?php echo $txt['txt100']; ?></label>
					<div class="ui fluid search selection dropdown" id="country">
  						<input type="hidden" name="country" value="<?php echo $country; ?>" maxlength="4">
  						<i class="dropdown icon"></i>
  						<div class="default text"><?php echo $txt['txt101']; ?></div>
  						<div class="menu">
						<?php
        				foreach($allinfo as $key => $info)
        				{
          					$code = strtolower(safeInput($info['code']));
          					$name = safeInput($info['code']);
          				
          					if ($code == $country) {
          						$selected = ' active';
          					} else {
          						$selected = '';
          					}

          					echo '<div class="item'.$selected.'" data-value="'.$code.'"><i class="'.$code.' flag"></i>'.$txt[$name].'</div>';
        				}
  						?>
						</div>
 					</div>
				</div>
				<?php
		  		} else { }
		  		?>

				<div class="field">
		    		<label><?php echo $txt['txt102']; ?></label>
		    		<input type="text" name="website" placeholder="<?php echo $txt['txt102']; ?>" value="<?php echo $website; ?>" maxlength="150">
		    		<small><?php echo $txt['txt103']; ?></small>
		  		</div>
		  		<div class="field">
    				<div class="ui toggle checkbox">
      					<input type="checkbox" tabindex="0" class="hidden" name="votifier-status" id="votifierToggle" <?php if($votifier_status) { ?>checked <?php } else { } ?>  value="1">
      					<label><?php echo $txt['txt104']; ?></label>
    				</div>
    				<div class="votifier<?php if($votifier_status) { ?> visible<?php } else { } ?>">
    					<div class="required field">
				    		<label><?php echo $txt['txt105']; ?></label>
				    		<input type="text" name="votifier-host" placeholder="<?php echo $txt['txt105']; ?>" value="<?php echo $votifier_host; ?>" maxlength="50">
		  				</div>
		  				<div class="required field">
				    		<label><?php echo $txt['txt99']; ?></label>
				    		<input type="text" name="votifier-port" placeholder="<?php echo $txt['txt99']; ?>" value="<?php echo $votifier_port; ?>" maxlength="6">
		  				</div>
						<div class="required field">
				    		<label><?php echo $txt['txt106']; ?></label>
				    		<textarea name="votifier-key" placeholder="<?php echo $txt['txt106']; ?>" rows="4" maxlength="500"><?php echo $votifier_key; ?></textarea>
				  		</div>
    				</div>
  				</div>

		  		<?php if ($isSponsored) { ?>
		  		<div class="field">
		    		<label><?php echo $txt['txt232']; ?></label>
		    		<input type="file" name="icon">
		    		<small><?php echo $txt['txt233']; ?></small>
		  		</div>
		  		<?php } else { } ?>

		  		<div class="field">
		    		<label><?php echo $txt['txt107']; ?></label>
		    		<input type="file" name="banner">
		    		<small><?php echo $txt['txt108']; ?></small>
		  		</div>

				<div class="field">
		    		<label><?php echo $txt['txt109']; ?></label>
		    		<textarea name="description" placeholder="<?php echo $txt['txt109']; ?>" rows="4" maxlength="1500"><?php echo $description; ?></textarea>
		  		</div>

		  		<div class="field">
		    		<label><?php echo $txt['txt110']; ?></label>
		    		<textarea name="short-description" placeholder="<?php echo $txt['txt110']; ?>" rows="2" maxlength="300"><?php echo $short_description; ?></textarea>
		  		</div>

  				<?php
				$allinfo = getArray("SELECT name FROM php_versions");
    			if ($allinfo)
    			{
		  		?>
		  		<div class="field">
					<label><?php echo $txt['txt111']; ?></label>
					<div class="ui fluid search selection dropdown" id="selectVersion">
  						<input type="hidden" name="version" value="<?php echo $version; ?>" maxlength="20">
  						<i class="dropdown icon"></i>
  						<div class="default text"><?php echo $txt['txt112']; ?></div>
  						<div class="menu">
  						<?php
        				foreach($allinfo as $key => $info)
        				{
          					$name = safeInput($info['name']);
          				?>
          					<div class="item" data-value="<?php echo $name; ?>"></i><?php echo $name; ?></div>
          				<?php
        				}
  						?>
						</div>
 					</div>
		  		</div>
		  		<?php
		  		} else { }

				$allinfo = getArray("SELECT name FROM php_types");
    			if ($allinfo)
    			{
		  		?>
		  		<div class="field">
					<label><?php echo $txt['txt113']; ?></label>
					<div class="ui fluid multiple search normal selection dropdown" id="selectTypes">
  						<input type="hidden" name="types" value="<?php echo $types; ?>" maxlength="200">
  						<i class="dropdown icon"></i>
  						<div class="default text"><?php echo $txt['txt114']; ?></div>
  						<div class="menu">
						<?php
        				foreach($allinfo as $key => $info)
        				{
          					$name = safeInput($info['name']);
          				?>
          					<div class="item" data-value="<?php echo $name; ?>"></i><?php echo $name; ?></div>
          				<?php
        				}
  						?>
						</div>
 					</div>
 					<small><?php echo $txt['txt115']; ?></small>
		  		</div>
				<?php
		  		} else { }
		  		?>
				<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
				<?php
				if ($security_check && $security_check_two != 1) {
				?>
				<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp"><?php echo $txt['txt117']; ?></a>
				<?php
				} elseif ($security_check_two == 1) {
				?>
				<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/servers"><?php echo $txt['txt117']; ?></a>
				<?php
				} else {
					redirect($txt['SITE_LINK'].'/cp');
				}
				uniSession('edit_error');
				?>
			</form>
			<?php
				} elseif ($action == 'delete') {
					query("DELETE FROM php_servers WHERE id = :id AND owner = :owner LIMIT 1", array(':id' => $id, ':owner' => safeInput(readSession('user_id'))));
					writeSession('add_success', "
					<div class='ui success message' style='margin: 0 0 1em 0!important'>
						<i class='close icon'></i>
						<div class='header'>".$txt['txt118']."</div>
						<p>".$txt['txt119']."</p>
					</div>");
					redirect($txt['SITE_LINK'].'/cp');
				} else {
					redirect($txt['SITE_LINK'].'/cp');
				}
			} elseif ($sub == 'sponsored') {
				$message = safeInput(isset($_GET['message'])) ? safeInput($_GET['message']) : '';
				$totalSponsored = numRows("SELECT COUNT(*) FROM php_sponsored WHERE start <= :start AND finish > :finish", array(':start' => date("Y-m-d H:i"), ':finish' => date("Y-m-d H:i")));

				if($totalSponsored >= sponsored_setti_two) {
					$info = fetchArray("SELECT finish FROM php_sponsored WHERE start <= :start AND finish > :finish ORDER BY finish ASC", array(':start' => date("Y-m-d H:i"), ':finish' => date("Y-m-d H:i")));
					$nextAvailable = $info['finish'];

					$text = $txt['txt158'].' - '.$nextAvailable;
					$button = '<a class="ui disabled button submit" href="#"><i class="ban icon"></i> '.$txt['txt120'].'</a>';
	  			} else {
	  				$text = '<strong>'.(sponsored_setti_two - $totalSponsored).'/'.sponsored_setti_two.'</strong> '.$txt['txt121'];
					$button = '<button class="ui button submit" type="submit" name="submit">'.$txt['txt122'].'</button>';
	  			}

				if(!sponsored_setti || !auction_id)
				{
			?>
			<form class="ui small form" action="#<?php validate('sponsored_buy'); ?>" method="post">
				<h4 class="ui dividing header"><i class="star icon"></i> <?php echo $txt['txt123']; ?></h4>
				<div class=" field">
					<i class="info circle icon"></i> <?php echo $text; ?>
				</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt124']; ?></label>
		    		<select name="server" required>
		    		<?php
		    		$allinfo = getArray("SELECT id, name FROM php_servers WHERE owner = :owner ORDER BY name ASC", array(':owner' => safeInput(readSession('user_id'))));
				    if ($allinfo)
				    {
				    ?>
				    	<option value='' selected><?php echo $txt['txt124']; ?></option>
				    <?php
				        foreach($allinfo as $key => $info)
				        {
				          	$id = safeInput($info['id']);
				          	$name = safeInput($info['name']);
				         	echo '<option value="'.$id.'">'.$name.'</option>';
				        }
				    } else {
				    	echo '<option value="">'.$txt['txt125'].'</option>';
				    }
   					?>
	    			</select>
	  			</div>
				<div class="required field">
	    			<label><?php echo $txt['txt126']; ?></label>
	    			<select name="period" required>
	    			<?php
	    			$allinfo = getArray("SELECT id, period, price FROM php_sponsoredoptions ORDER BY price ASC");
				    if ($allinfo)
				    {
				    ?>
				    	<option value='' selected><?php echo $txt['txt126']; ?></option>
				    <?php
				        foreach($allinfo as $key => $info)
				        {
				          	$id = safeInput($info['id']);
				          	$period = safeInput($info['period']);
				          	$price = safeInput($info['price']);
				         	echo '<option value="'.$id.'">'.$period.' '.$txt['txt159'].' / $'.$price.'</option>';
				        }
				    } else {
				    	echo '<option value="">'.$txt['txt127'].'</option>';
				    }
   					?>
	    			</select>
	  			</div>
	  			<div class="field">
		    		<label><?php echo $txt['txt238']; ?></label>
		    		<input type="text" name="coupon" placeholder="<?php echo $txt['txt238']; ?>">
		    		<small><?php echo $txt['txt239']; ?></small>
	  			</div>
	  			<?php echo $button; ?>
	  			<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp"><?php echo $txt['txt117']; ?></a>
	  			<?php
	  			if (!$message) {
		  			uniSession('sponsored_success');
					uniSession('sponsored_error');
				} else {
				?>
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'><?php echo $txt['txt118']; ?></div>
					<p><?php echo $txt['txt128']; ?></p>
				</div>
				<?php	
				}
				?>
		  	</form>
			<?php
				} else {
  					$auction_info = fetchArray('SELECT * FROM php_auction WHERE id = :id LIMIT 1', array(':id' => auction_id));
					$today = date("Y-m-d H:i");
					$start = $auction_info['start'];
					$finish = $auction_info['finish'];
					$deadline = $auction_info['deadline'];
					$minBid = $auction_info['minBid'];
					$period = $auction_info['period'];

  					if ($today < $start) {
  						$status = '<span class="ui basic orange mini button">'.$txt['txt129'].'</span>';
  						$formStatus = false;
  					} elseif ($today >= $start && $today < $finish) {
  						$status = '<span class="ui basic green mini button">'.$txt['txt130'].'</span>';
  						$formStatus = true;
  					} else {
  						$status = '<span class="ui basic red mini button">'.$txt['txt131'].'</span>';
  						$formStatus = false;
  					}

  					if (!$formStatus && $deadline > $today) {
  						$winCheck = fetchArray('SELECT id FROM php_bids WHERE auction = :id AND user = :user ORDER BY amount DESC LIMIT '.sponsored_setti_two, array(':id' => auction_id, ':user' => safeInput(readSession('user_id'))));
						$paymentStatus = true;
  					} else {
  						$paymentStatus = false;
  						$winCheck = false;
  					}

  					$sponsoredStart = date("d M G:i A", strtotime('+1 days', strtotime($deadline)));
  					$totalBids = numRows('SELECT COUNT(*) FROM php_bids WHERE auction = :id', array(':id' => auction_id));

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
			?>
			<div class="ui top attached tabular menu default-color nav-menu" id="auction">
  				<a class="active item" data-tab="top"><?php echo $txt['txt132']; ?> <?php echo sponsored_setti_two; ?> <?php echo $txt['txt133']; ?></a>
  				<a class="item" data-tab="all"><?php echo $txt['txt134']; ?></a>
			</div>
			<div class="ui bottom attached active tab segment" data-tab="top" style="padding:0!important">
				<table class="ui very basic unstackable table info">
					<tr>
						<th class="rank" style="width: 10%; text-align: center">#</th>
						<th class="rank" style="width: 65%"><?php echo $txt['txt135']; ?></th>
						<th class="rank" style="width: 25%; text-align: center"><?php echo $txt['txt136']; ?></th>
					</tr>
					<?php
					$allinfo = getArray('SELECT server, amount FROM php_bids WHERE auction = :id ORDER BY amount DESC LIMIT '.sponsored_setti_two, array(':id' => auction_id));
  					if ($allinfo)
  					{
  						$i = 0;
					    foreach($allinfo as $key => $info)
					    {
					    	$i += 1;
      						$server = safeInput($info['server']);
      						$server_info = fetchArray('SELECT name FROM php_servers WHERE id = :id LIMIT 1', array(':id' => $server));
							$name = $server_info['name'];
      						$amount = safeInput($info['amount']);
 							
 							echo '<tr><td style="width: 10%; text-align: center">'.$i.'</td><td style="width: 65%">'.$name.'</td><td style="width: 25%; text-align: center">$'.$amount.'</td></tr>';
    					}
  					} else {
   						echo '<tr><td colspan="3" style="text-align: center">'.$txt['txt160'].'</td></tr>';
  					}
					?>
				</table>
			</div>
			<div class="ui bottom attached tab segment" data-tab="all" style="padding:0!important">
				<table class="ui very basic unstackable table info">
					<tr>
						<th class="rank" style="width: 10%; text-align: center">#</th>
						<th class="rank" style="width: 65%"><?php echo $txt['txt135']; ?></th>
						<th class="rank" style="width: 25%; text-align: center"><?php echo $txt['txt136']; ?></th>
					</tr>
					<?php
					$allinfo = getArray('SELECT server, amount FROM php_bids WHERE auction = :id ORDER BY amount DESC', array(':id' => auction_id));
  					if ($allinfo)
  					{
  						$i = 0;
					    foreach($allinfo as $key => $info)
					    {
					    	$i += 1;
      						$server = safeInput($info['server']);
      						$server_info = fetchArray('SELECT name FROM php_servers WHERE id = :id LIMIT 1', array(':id' => $server));
							$name = $server_info['name'];
      						$amount = safeInput($info['amount']);
 							
 							echo '<tr><td style="width: 10%; text-align: center">'.$i.'</td><td style="width: 65%">'.$name.'</td><td style="width: 25%; text-align: center">$'.$amount.'</td></tr>';
    					}
  					} else {
   						echo '<tr><td colspan="3" style="text-align: center">'.$txt['txt160'].'</td></tr>';
  					}
					?>
				</table>
			</div>

			<?php
			if ($formStatus) {
			?>
			<form class="ui small form" action="#<?php validate('sponsored_bid'); ?>" method="post">
				<div class="required field">
					<label><?php echo $txt['txt137']; ?></label>
					<input type="number" name="bid" min="<?php echo $calcuMinBid; ?>" placeholder="<?php echo $txt['txt138']; ?> <?php echo $calcuMinBid; ?>" required>
				</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt124']; ?></label>
		    		<select name="server" required>
		    		<?php
		    		$allinfo = getArray("SELECT id, name FROM php_servers WHERE owner = :owner ORDER BY name ASC", array(':owner' => safeInput(readSession('user_id'))));
				    if ($allinfo)
				    {
				    ?>
				    	<option value='' selected><?php echo $txt['txt124']; ?></option>
				    <?php
				        foreach($allinfo as $key => $info)
				        {
				          	$id = safeInput($info['id']);
				          	$name = safeInput($info['name']);
				         	echo '<option value="'.$id.'">'.$name.'</option>';
				        }
				    } else {
				    	echo '<option value="">'.$txt['txt125'].'</option>';
				    }
   					?>
	    			</select>
	  			</div>
	  			<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt139']; ?></button>
	  			<?php
	  			if (!$message) {
		  			uniSession('sponsored_bid_success');
					uniSession('sponsored_bid_error');
				} else {
				?>
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'><?php echo $txt['txt118']; ?></div>
					<p><?php echo $txt['txt119']; ?></p>
				</div>
				<?php	
				}
				?>
		  	</form>
		  	<?php
		  	} elseif ($paymentStatus && $winCheck) {
		    	$allinfo = getArray('SELECT server, amount FROM php_bids WHERE auction = :id AND user = :user AND paid = 0 ORDER BY amount DESC LIMIT '.sponsored_setti_two, array(':id' => auction_id, ':user' => safeInput(readSession('user_id'))));
				if ($allinfo)
				{
				?>
				<form class="ui small form" action="#<?php validate('sponsored_pay'); ?>" method="post">
					<div class="field">
						<label><?php echo $txt['txt140']; ?></label>
		    			<select>
						<?php
						$totalPay = 0;
						foreach($allinfo as $key => $info)
						{
						   	$server = safeInput($info['server']);
						   	$server_info = fetchArray('SELECT name FROM php_servers WHERE id = :id LIMIT 1', array(':id' => $server));
							$name = $server_info['name'];
						   	$amount = safeInput($info['amount']);
						   	$totalPay += $amount;
						   	echo '<option value="'.$amount.'">'.$name.' | '.$txt['txt141'].' - $'.$amount.'</option>';
						}
						?>
						</select>
						<input type="hidden" name="price" value="<?php echo $totalPay; ?>">
						<small><?php echo $txt['txt142']; ?> <strong>$<?php echo $totalPay; ?></strong> <?php echo $txt['txt143']; ?> <?php echo date("d M G:i A", strtotime($deadline)); ?>.</small>
					</div>
					<button class="ui tiny button submit" type="submit" name="submit"><?php echo $txt['txt144']; ?></button>
					<a class="ui tiny basic button" href="<?php echo $txt['SITE_LINK']; ?>/contacts"><?php echo $txt['txt145']; ?></a>
				</form>
				<?php
				}
		  		if (!$message) {
			  		uniSession('sponsored_pay_success');
					uniSession('sponsored_pay_error');
				} else {
				?>
				<div class='ui success message'>
					<i class='close icon'></i>
					<div class='header'><?php echo $txt['txt118']; ?></div>
					<p><?php echo $txt['txt119']; ?></p>
				</div>
				<?php	
				}
		  	} else { }
		  	?>

			<table class="ui unstackable striped table info">
				<thead>
					<tr>
						<th colspan="2" class="rank default-color"><i class="info circle icon"></i> <?php echo $txt['txt146']; ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo $txt['txt147']; ?></td><td><?php echo auction_id; ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt148']; ?></td><td><?php echo $status; ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt149']; ?></td><td><?php echo $period; ?> Days</td>
					</tr>
					<tr>
						<td><?php echo $txt['txt150']; ?></td><td><?php echo $sponsoredStart; ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt151']; ?></td><td><?php $newPeriod = $period + 1; echo date("d M G:i A", strtotime('+'.$newPeriod.' day', strtotime($deadline))); ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt152']; ?></td><td><?php echo $totalBids; ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt153']; ?></td><td>$<?php echo $minBid; ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt154']; ?></td><td>UTC</td>
					</tr>
					<tr>
						<td><?php echo $txt['txt155']; ?></td><td><?php echo date("d M G:i A"); ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt161']; ?></td><td><?php echo date("d M G:i A", strtotime($start)); ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt156']; ?></td><td><?php echo date("d M G:i A", strtotime($finish)); ?></td>
					</tr>
					<tr>
						<td><?php echo $txt['txt157']; ?></td><td><?php echo date("d M G:i A", strtotime($deadline)); ?></td>
					</tr>
				</tbody>
			</table>
			<?php
				}
			}  elseif ($sub == 'account') {
				uniSession('account_success');
				uniSession('account_error');
			?>
			<form class="ui small form" action="#<?php validate('account_username'); ?>" method="post" style="border-radius:.28571429rem;border: 1px solid rgba(34,36,38,.15);margin-bottom:1em;padding:1em">
				<h4 class="ui dividing header"><i class="user icon"></i> <?php echo $txt['txt162']; ?> <?php echo $txt['txt68']; ?></h4>
		  		<div class="required field">
		    		<label><?php echo $txt['txt69']; ?></label>
		    		<input type="password" name="password" placeholder="<?php echo $txt['txt69']; ?>" required>
		  		</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt68']; ?></label>
		    		<input type="text" name="username" placeholder="<?php echo $txt['txt68']; ?>" maxlength="16" required>
		  		</div>
		  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			</form>

			<form class="ui small form" action="#<?php validate('account_email'); ?>" method="post" style="border-radius:.28571429rem;border: 1px solid rgba(34,36,38,.15);margin-bottom:1em;padding:1em">
				<h4 class="ui dividing header"><i class="envelope icon"></i> <?php echo $txt['txt162']; ?> <?php echo $txt['txt72']; ?></h4>
		  		<div class="required field">
		    		<label><?php echo $txt['txt69']; ?></label>
		    		<input type="password" name="password" placeholder="<?php echo $txt['txt69']; ?>" required>
		  		</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt72']; ?></label>
		    		<input type="email" name="email" placeholder="<?php echo $txt['txt72']; ?>" maxlength="100" required>
		  		</div>
		  		<button class="ui button submit" type="submit" name="submit_two"><?php echo $txt['txt116']; ?></button>
			</form>

			<form class="ui small form" action="#<?php validate('account_password'); ?>" method="post" style="border-radius:.28571429rem;border: 1px solid rgba(34,36,38,.15);padding:1em">
				<h4 class="ui dividing header"><i class="key icon"></i> <?php echo $txt['txt162']; ?> <?php echo $txt['txt69']; ?></h4>
		  		<div class="required field">
		    		<label><?php echo $txt['txt69']; ?></label>
		    		<input type="password" name="password" placeholder="<?php echo $txt['txt69']; ?>" required>
		  		</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt78']; ?></label>
		    		<input type="password" name="new_password" placeholder="<?php echo $txt['txt78']; ?>" required>
		  		</div>
		  		<div class="required field">
		    		<label><?php echo $txt['txt79']; ?></label>
		    		<input type="password" name="c_new_password" placeholder="<?php echo $txt['txt79']; ?>" required>
		  		</div>
		  		<button class="ui button submit" type="submit" name="submit_three"><?php echo $txt['txt116']; ?></button>
			</form>
			<?php
			} elseif ($sub == 'admin' && $userSettings['rank'] == 1) { 
				$adminSub = safeInput(isset($_GET['admin_sub'])) ? safeInput($_GET['admin_sub']) : '';
				$action = safeInput(isset($_GET['action'])) ? safeInput($_GET['action']) : '';
				$id = safeInput(isset($_GET['id'])) ? safeInput($_GET['id']) : '';
				?>
				<div class="ui dropdown labeled search icon button adminMenu">
  					<i class="chess king icon"></i>
  					<span class="text" style="margin:0 auto!important;!important;cursor:pointer!important;">Administrator Menu</span>
  					<div class="menu">
	  					<a class="<?php if(!$adminSub) { ?>active <?php } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin"><i class="cog icon"></i> General</a>
	  					<a class="<?php if($adminSub == 'languages') { ?>active <?php } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/languages"><i class="flag icon"></i> Languages</a>
	  					<a class="<?php if($adminSub == 'designs') { ?>active <?php } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/designs"><i class="leaf icon"></i> Designs</a>
	  					<a class="<?php if($adminSub == 'sponsored') { ?>active <?php } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored"><i class="star icon"></i> Sponsored</a>
	  					<a class="<?php if($adminSub == 'users') { ?>active <?php } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/users"><i class="users icon"></i> Users</a>
	  					<a class="<?php if($adminSub == 'servers') { ?>active <?php } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/servers"><i class="server icon"></i> Servers</a>
	  					<a class="<?php if($adminSub == 'types') { ?>active <?php } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/types"><i class="clipboard list icon"></i> Types</a>
	  					<a class="<?php if($adminSub == 'versions') { ?>active <?php } ?>item" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/versions"><i class="clipboard list icon"></i> Versions</a>
  					</div>
  				</div>
  				<div class="clearfix"></div>
				<?php
				if (!$adminSub) {
					$site_info = fetchArray('SELECT * FROM php_settings LIMIT 1');
					uniSession('admin_general_success');
					uniSession('admin_general_error');
				?>
				<form class="ui form" action="#<?php validate('admin_general'); ?>" method="post">
					<h4 class="ui dividing header">General Settings</h4>
					<div class="ui styled accordion" style="width: 100%">
 						<div class="active title">
 							<i class="dropdown icon"></i> !Important Settings
 						</div>
 						<div class="content">
 							<div class="field">
					    		<label>Maintenance Mode</label>
					    		<select name="maintenance">
					    			<option value="0" <?php if (!maintenance) { echo 'selected'; } else { } ?>>Off</option>
					    			<option value="1" <?php if (maintenance) { echo 'selected'; } else { } ?>>On</option>
					    		</select>
					    		<small>Only logged administrators will be able to see website content.</small>
					  		</div>
					  		<div class="required field">
					    		<label>Website Link</label>
					    		<input type="text" name="link" placeholder="Website Link" value="<?php echo $site_info['link']; ?>" maxlength="50" required>
					  		</div>
					  		<div class="required field">
					    		<label>Project Name</label>
					    		<input type="text" name="name" placeholder="TOP Name" value="<?php echo $site_info['name']; ?>" maxlength="50" required>
					  		</div>
					  		<div class="required field">
					    		<label>General e-mail Address</label>
					    		<input type="text" name="email" placeholder="General e-mail Address" value="<?php echo $site_info['email']; ?>" maxlength="50" required>
					    		<small>You will receive all emails to this email address from contact form</small>
					  		</div>
					  		<div class="required field">
					    		<label> Servers Per Page</label>
					    		<input type="number" name="serversPerPage" placeholder="ShareThis Property ID" value="<?php echo $site_info['serversPerPage']; ?>" min="1" max="999" maxlength="3" required>
					    		<small>How many server displayed per page + pagination part</small>
					  		</div>
					  		<div class="required field">
					    		<label>Default Language</label>
					    		<select name="defaultLanguage">
					    		<?php
				 				$sql = "SELECT id, name, isDefault FROM php_languages ORDER BY isDefault DESC";
				  				$allinfo = getArray($sql);
								if ($allinfo)
								{
									foreach($allinfo as $key => $info)
									{
									   	$id = $info['id'];
				      					$name = $info['name'];
				      					$isDefault = $info['isDefault'];

										if ($isDefault == 1) {
				      						$selected = ' selected';
				      					} else {
				      						$selected = '';
				      					}
				      					echo '<option value="'.$id.'"'.$selected.'>'.$name.'</option>';
						    		}
						    	}
								?>
					    		</select>
					    		<small>Add, edit, delete languages <a href="<?php echo $txt['SITE_LINK']; ?>/cp/languages">here</a></small>
					  		</div>
					  		<div class="required field">
					    		<label>Default Design</label>
					    		<select name="defaultDesign">
					    		<?php
				 				$sql = "SELECT id, name, isDefault FROM php_designs ORDER BY name ASC";
				  				$allinfo = getArray($sql);
								if ($allinfo)
								{
									foreach($allinfo as $key => $info)
									{
									   	$id = $info['id'];
				      					$name = $info['name'];
				      					$isDefault = $info['isDefault'];

										if ($isDefault == 1) {
				      						$selected = ' selected';
				      					} else {
				      						$selected = '';
				      					}
				      					echo '<option value="'.$id.'"'.$selected.'>'.$name.'</option>';
						    		}
						    	}
								?>
					    		</select>
					    		<small>Add, edit, delete designs <a href="<?php echo $txt['SITE_LINK']; ?>/cp/designs">here</a></small>
					  		</div>
					  		<div class="field">
					    		<label>Logo Settings</label>
					    		<select name="logoDisplay">
					    			<option value="0"<?php if (!$site_info['logoDisplay']) { ?> selected <?php } ?>>Site Name</option>
					    			<option value="1"<?php if ($site_info['logoDisplay'] == 1) { ?> selected <?php } ?>>Logo Image</option>
					    		</select>
					    		<small>If 'Logo Image' option selected upload your logo to CSS/ folder and name it logo.png . Recommended size 300 pixels width</small>
					  		</div>
					  		<div class="required field">
					    		<label><i class="clock icon"></i> Time before User Can Vote Again</label>
					    		<input type="number" name="voteHours" placeholder="Time before User Can Vote Again" value="<?php echo $site_info['voteHours']; ?>" min="1" max="99" maxlength="2" required>
					    		<small>Time in hours which user has to wait to vote again</small>
					  		</div>
					  		<button class="ui red basic button confirm" type="submit" name="resetVotes" data-confirm="Are you sure you want to reset votes?"><i class="trash alternate outline icon"></i> Reset Votes</button>
					  		<hr>
					  		<div class="field">
					    		<label><i class="share alternate icon"></i> ShareThis Property ID</label>
					    		<input type="text" name="shareThis" placeholder="ShareThis Property ID" value="<?php echo $site_info['shareThis']; ?>" maxlength="150">
					    		<small>Get your property ID from <a href="https://platform.sharethis.com/settings">https://platform.sharethis.com/settings</a> and activate inline share buttons</small>
					  		</div>
					  	</div>
					</div>
			  		<hr>
			  		<div class="ui styled accordion" style="width: 100%">
 						<div class="active title">
 							<i class="dropdown icon"></i> Mailing Options - mail() & STMP
 						</div>
 						<div class="content">
					  		<div class="required field">
					    		<label>Mailing Method</label>
					    		<select name="mailingOption">
					    			<option value="0"<?php if (!$site_info['mailingOption']) { ?> selected <?php } ?>>PHP mail()</option>
					    			<option value="1"<?php if ($site_info['mailingOption'] == 1) { ?> selected <?php } ?>>SMTP</option>
					    		</select>
					    		<small>Select how you want your toplist mailing system to work. Using <a href="http://php.net/manual/en/function.mail.php">PHP mail()</a> function or <a href="https://www.web-development-blog.com/archives/send-e-mail-messages-via-smtp-with-phpmailer-and-gmail/">SMTP</a> (where script will send emails directly from your mailing account)</small>
					  		</div>
					  		<div class="field">
					    		<label><i class="envelope icon"></i> SMTP Host</label>
					    		<input type="text" name="smtp_host" placeholder="SMTP Host" value="<?php echo $site_info['smtp_host']; ?>" maxlength="150">
					    		<small>Only enter if using SMTP method</small>
					  		</div>
					  		<div class="field">
					    		<label><i class="envelope icon"></i> SMTP Port</label>
					    		<input type="number" name="smtp_port" placeholder="SMTP Port" value="<?php echo $site_info['smtp_port']; ?>" maxlength="7">
					    		<small>Only enter if using SMTP method</small>
					  		</div>
					  		<div class="field">
					    		<label><i class="envelope icon"></i> SMTP Username</label>
					    		<input type="text" name="smtp_username" placeholder="SMTP Username" value="<?php echo $site_info['smtp_username']; ?>" maxlength="150">
					    		<small>Only enter if using SMTP method</small>
					  		</div>
					  		<div class="field">
					    		<label><i class="envelope icon"></i> SMTP Password</label>
					    		<input type="text" name="smtp_password" placeholder="SMTP Password" value="<?php echo $site_info['smtp_password']; ?>" maxlength="150">
					    		<small>Only enter if using SMTP method</small>
					  		</div>
					  	</div>
					</div>
					<hr>
			  		<div class="ui styled accordion" style="width: 100%">
 						<div class="active title">
 							<i class="dropdown icon"></i> PayPal Settings
 						</div>
 						<div class="content">
					  		<div class="field">
					    		<label><i class="paypal icon"></i> Paypal Currency (USD, EUR, GBP, etc.)</label>
					    		<input type="text" name="defaultCurrency" placeholder="PayPal Currency" value="<?php echo $site_info['defaultCurrency']; ?>" maxlength="4">
					    		<small>IMPORTANT! Set this up to start earning from Sponsored slots. Find all available currencies <a href="https://developer.paypal.com/docs/classic/api/currency_codes/">here</a></small>
					  		</div>
							<div class="field">
					    		<label><i class="paypal icon"></i> Your Paypal Email</label>
					    		<input type="email" name="ppMerch" placeholder="Paypal Email" value="<?php echo $site_info['pp_merchID']; ?>" maxlength="150">
					    		<small>IMPORTANT! Set this up to start earning from Sponsored slots</small>
					  		</div>
					  		<div class="field">
					  			<strong style="font-color: red">IMPORTANT!</strong> - For automatic payments to work you need to enable notifications on your paypal account, follow this guide - <a href="https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNSetup/">https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNSetup/</a>
					  		</div>
					  	</div>
					</div>
			  		<hr>
			  		<div class="ui styled accordion" style="width: 100%">
 						<div class="active title">
 							<i class="dropdown icon"></i> Google Settings
 						</div>
 						<div class="content">
					  		<div class="field">
					    		<label><i class="google icon"></i> Google [Analytics] Tracking ID</label>
					    		<input type="text" name="goTrack" placeholder="Example UA-XXXXXX-00" value="<?php echo $site_info['googleTrackingID']; ?>" maxlength="15">
					    		<small>Follow this article to get your analytics code - <a href="http://blog.analytics-toolkit.com/2016/google-analytics-tracking-code-id-check-setup/">http://blog.analytics-toolkit.com/2016/google-analytics-tracking-code-id-check-setup/</a></small>
					  		</div>
					  		<div class="field">
					    		<label><i class="google icon"></i> Google [Verification] Code</label>
					    		<input type="text" name="goVerifi" placeholder="Verification Code" value="<?php echo $site_info['google_verification']; ?>" maxlength="50">
								<small>Verify your ownership of project at google <a href="https://www.google.com/webmasters/verification/home">here</a></small>
					  		</div>
					  		<div class="field">
					    		<label><i class="google icon"></i> Google [Adsense] Client ID</label>
					    		<input type="text" name="googleAdClient" placeholder="ca-pub-XXXXXXXXXXXX" value="<?php echo $site_info['googleAdClient']; ?>">
					    		<small>!important! You need this to start earning from google adSense</small>
					  		</div>
					  		<div class="field">
					    		<label><i class="google icon"></i> Google [Adsense] AD Slot</label>
					    		<input type="text" name="googleAdSlot" placeholder="Google [Adsense] AD Slot" value="<?php echo $site_info['googleAdSlot']; ?>">
					    		<small>!important! You need this to start earning from google adSense</small>
					  		</div>
					  	</div>
					</div>
					<hr>
					<div class="ui styled accordion" style="width: 100%">
 						<div class="active title">
 							<i class="dropdown icon"></i> Social Links
 						</div>
 						<div class="content">
					  		<div class="field">
					    		<label><i class="facebook icon"></i> Facebook Link</label>
					    		<input type="text" name="facebook" placeholder="Facebook Link" value="<?php echo $site_info['facebookLink']; ?>" maxlength="150">
					  		</div>
					  		<div class="field">
					    		<label><i class="twitter icon"></i> Twitter Link</label>
					    		<input type="text" name="twitter" placeholder="Twitter Link" value="<?php echo $site_info['twitterLink']; ?>" maxlength="150">
					  		</div>
							<div class="field">
					    		<label><i class="google plus icon"></i> Google+ Link</label>
					    		<input type="text" name="google" placeholder="Google+ Link" value="<?php echo $site_info['googleLink']; ?>" maxlength="150">
					  		</div>
					  		<div class="field">
					    		<label><i class="instagram icon"></i> Instagram Link</label>
					    		<input type="text" name="instagram" placeholder="Instagram Link" value="<?php echo $site_info['instagramLink']; ?>" maxlength="150">
					  		</div>
					  		<div class="field">
					    		<label><i class="youtube icon"></i> Youtube Link</label>
					    		<input type="text" name="youtube" placeholder="Youtube Link" value="<?php echo $site_info['youtubeLink']; ?>" maxlength="150">
					  		</div>
					  		<div class="field">
					    		<label><i class="vk icon"></i> VK.com Link</label>
					    		<input type="text" name="vk" placeholder="VK.com Link" value="<?php echo $site_info['vkLink']; ?>" maxlength="150">
					  		</div>
					  	</div>
					</div>
					<hr>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
				</form>
				<?php
				} elseif ($adminSub == 'designs') {
					if (!$action) {
						uniSession('admin_design_error');
						uniSession('admin_design_success');
				?>
				<table class="ui basic table unstackable" style="margin-top:0">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Name</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
		 		$sql = "SELECT id, name, isDefault FROM php_designs ORDER BY isDefault DESC";
		  		$allinfo = getArray($sql);
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
		      			$name = $info['name'];
		      			$flagCode = $info['flagCode'];
		      			$isDefault = $info['isDefault'];
						if ($isDefault == 1) {
      						$disabled = ' disabled';
      						$text = ' (Default)';
      					} else {
      						$disabled = '';
      						$text = '';
      					}
      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">'.$name.$text.'</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/designs/'.$id.'.edit" data-tooltip="Edit Design">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm'.$disabled.'" data-confirm="Are you sure you want to delete this design?" href="'.$txt['SITE_LINK'].'/cp/admin/designs/'.$id.'.delete" data-tooltip="Delete Design"'.$disabled.'>
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				?>
				</table>
				<a class="ui button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/designs/add">
  					<i class="plus icon"></i> Add			
  				</a>
				<?php
					} elseif ($action == 'edit') {
						$info = fetchArray('SELECT * FROM php_designs WHERE id = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_design'); ?>" method="post">
					<h4 class="ui dividing header">Edit Design</h4>
			  		<div class="required field">
			    		<label>Design Name</label>
			    		<input type="hidden" name="id" value="<?php echo $id; ?>">
			    		<input type="text" name="name" placeholder="Design Name" maxlength="25" value="<?php echo $info['name']; ?>" required>
			    		<small>Name which will appear in designs selection dropdown</small>
			  		</div>
			  		<div class="required field">
			    		<label>Main Color</label>
			    		<input type="text" name="mainColor" placeholder="Main Color" maxlength="8" value="#<?php echo $info['main_color']; ?>" required>
			    		<div><small>Format: #COLORCODE or just COLORCODE. Color for navigation, titles background & other main elements. Some nice color palletes - http://colormind.io/ or color picker https://www.w3schools.com/colors/colors_picker.asp</small></div>
			  		</div>
			  		<div class="required field">
			    		<label>Buttons Text Color</label>
			    		<input type="text" name="textColor" placeholder="Buttons Text Color" maxlength="8" value="#<?php echo $info['text_color']; ?>" required>
			    		<div><small>Format: #COLORCODE or just COLORCODE. Some nice color palletes - http://colormind.io/ or color picker https://www.w3schools.com/colors/colors_picker.asp</small></div>
			  		</div>
			  		<div class="required field">
			    		<label>Secondary Color</label>
			    		<input type="text" name="secondaryColor" placeholder="Secondary Color" maxlength="8" value="#<?php echo $info['secondary_color']; ?>" required>
			    		<div><small>Format: #COLORCODE or just COLORCODE. Color mostly for buttons & other elements. Some nice color palletes - http://colormind.io/ or color picker https://www.w3schools.com/colors/colors_picker.asp</small></div>
			  		</div>
			  		<div class="required field">
			    		<label>Background Color</label>
			    		<input type="text" name="bgColor" placeholder="Background Color" maxlength="8" value="#<?php echo $info['bg_color']; ?>" required>
			    		<div><small>Format: #COLORCODE or just COLORCODE. Some nice color palletes - http://colormind.io/ or color picker https://www.w3schools.com/colors/colors_picker.asp</small></div>
			  		</div>
			  		<div class="field">
			    		<label>Background Image</label>
			    		<select name="bgImage">
			    			<option value="0"<?php if (!$info['bgImage']) { ?> selected <?php } ?>>None</option>
			    			<option value="1"<?php if ($info['bgImage'] == 1) { ?> selected <?php } ?>>Hytale NPCs</option>
			    			<option value="2"<?php if ($info['bgImage'] == 2) { ?> selected <?php } ?>>Hytale Steve</option>
			    			<option value="3"<?php if ($info['bgImage'] == 3) { ?> selected <?php } ?>>Hytale Monsters</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Background Gradients</label>
			    		<select name="gradient">
			    			<?php
			    			$grads = array('None', 'Lines', 'Always Grey', 'Arches', 'Asfalt Dark', 'AZ Subtle', 'Binding Dark', 'Black Mamba', 'Black Scales', 'Black Thread', 'Black Thread Light', 'Black Twill', 'Blue Stripes', 'Brick Wall', 'Bright Squares', 'Brilliant', 'Carbon Fibre', 'Cartographer', 'Dark Circles', 'Dark Matter', 'Dimension', 'Escheresque', 'Hytale Mold', 'Hytale Bricks', 'Hytale Creepers');
			    			for($i = 0; $i < count($grads); $i++) {
			    				if ($i == $info['gradient']) {
			    					$selected = ' selected'; 
			    				} else {
			    					$selected = '';
			    				}
			    				echo '<option value="'.$i.'"'.$selected.'>'.$grads[$i].'</option>';
			    			}
			    			?>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Navigation Position</label>
			    		<select name="menu">
			    			<option value="0"<?php if (!$info['menu']) { ?> selected <?php } ?>>Static</option>
			    			<option value="1"<?php if ($info['menu'] == 1) { ?> selected <?php } ?>>Fixed Attached</option>
			    		</select>
			  		</div>
					<div class="field">
			    		<label>Servers Tables Styling</label>
			    		<select name="tables">
			    			<option value="0"<?php if (!$info['tables']) { ?> selected <?php } ?>>Very Basic</option>
			    			<option value="1"<?php if ($info['tables'] == 1) { ?> selected <?php } ?>>Basic</option>
			    			<option value="2"<?php if ($info['tables'] == 2) { ?> selected <?php } ?>>Striped</option>
			    		</select>
			  		</div>
					<div class="field">
			    		<label>Logo Align</label>
			    		<select name="logoAlign">
			    			<option value="0"<?php if (!$info['logoAlign']) { ?> selected <?php } ?>>Left</option>
			    			<option value="2"<?php if ($info['logoAlign'] == 2) { ?> selected <?php } ?>>Center</option>
			    			<option value="3"<?php if ($info['logoAlign'] == 3) { ?> selected <?php } ?>>Right</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Show Statistics</label>
			    		<select name="showStats">
			    			<option value="1"<?php if ($info['showStats'] == 1) { ?> selected <?php } ?>>Yes</option>
			    			<option value="0"<?php if (!$info['showStats']) { ?> selected <?php } ?>>No</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Statistics Position</label>
			    		<select name="statPosition">
			    			<option value="0"<?php if (!$info['statPosition']) { ?> selected <?php } ?>>Top</option>
			    			<option value="1"<?php if ($info['statPosition'] == 1) { ?> selected <?php } ?>>Bottom</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Default?</label>
			    		<select name="default">
			    			<?php if (!$info['isDefault']) { ?>
			    			<option value="0"<?php if (!$info['isDefault']) { ?> selected <?php } ?>>---</option>
			    			<?php } ?>
			    			<option value="1"<?php if ($info['isDefault'] == 1) { ?> selected <?php } ?>>Yes</option>
			    		</select>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/designs">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							redirect($txt['SITE_LINK'].'/cp/admin/designs');
						}
					} elseif ($action == 'delete') {
						$check = numRows('SELECT COUNT(*) FROM php_designs WHERE id = :id AND isDefault = 0', array(':id' => $id));
						if ($check) {
							query('DELETE FROM php_designs WHERE id = :id AND isDefault = 0 LIMIT 1', array(':id' => $id));
							writeSession('admin_design_success', "
							<div class='ui success message'>
								<i class='close icon'></i>
								<div class='header'>Success</div>
								<p>Design successfully deleted</p>
							</div>");
						} else {
							writeSession('admin_design_error', "
							<div class='ui error message'>
								<i class='close icon'></i>
								<div class='header'>Error</div>
								<p>Can't delete default design!</p>
							</div>");
						}
						redirect($txt['SITE_LINK'].'/cp/admin/designs');
					} elseif ($action == 'add') {
				?>
				<form class="ui form" action="#<?php validate('admin_add_design'); ?>" method="post">
					<h4 class="ui dividing header">Add Design</h4>
			  		<div class="required field">
			    		<label>Design Name</label>
			    		<input type="hidden" name="id" value="<?php echo $id; ?>">
			    		<input type="text" name="name" placeholder="Design Name" maxlength="25" required>
			    		<small>Name which will appear in designs selection dropdown</small>
			  		</div>
			  		<div class="required field">
			    		<label>Main Color</label>
			    		<input type="color" name="mainColor" placeholder="Main Color" maxlength="8" value="#4b5787" required>
			    		<div><small>Format: #COLORCODE or just COLORCODE. Color for navigation, titles background & other main elements. Some nice color palletes - http://colormind.io/ or color picker https://www.w3schools.com/colors/colors_picker.asp</small></div>
			  		</div>
			  		<div class="required field">
			    		<label>Buttons Text Color</label>
			    		<input type="color" name="textColor" placeholder="Buttons Text Color" maxlength="8" value="#FFFFFF" required>
			    		<div><small>Format: #COLORCODE or just COLORCODE. Some nice color palletes - http://colormind.io/ or color picker https://www.w3schools.com/colors/colors_picker.asp</small></div>
			  		</div>
			  		<div class="required field">
			    		<label>Secondary Color</label>
			    		<input type="color" name="secondaryColor" placeholder="Secondary Color" maxlength="8" value="#6e7bb1" required>
			    		<div><small>Format: #COLORCODE or just COLORCODE. Color mostly for buttons & other elements. Some nice color palletes - http://colormind.io/ or color picker https://www.w3schools.com/colors/colors_picker.asp</small></div>
			  		</div>
			  		<div class="required field">
			    		<label>Background Color</label>
			    		<input type="color" name="bgColor" placeholder="Background Color" maxlength="8" value="#F0F0F0" required>
			    		<div><small>Format: #COLORCODE or just COLORCODE. Some nice color palletes - http://colormind.io/ or color picker https://www.w3schools.com/colors/colors_picker.asp</small></div>
			  		</div>
			  		<div class="field">
			    		<label>Background Image</label>
			    		<select name="bgImage">
			    			<option value="0">None</option>
			    			<option value="1" selected>Hytale NPCs</option>
			    			<option value="2">Hytale Steve</option>
			    			<option value="3">Hytale Monsters</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Background Gradients</label>
			    		<select name="gradient">
			    			<?php
			    			$grads = array('None', 'Lines', 'Always Grey', 'Arches', 'Asfalt Dark', 'AZ Subtle', 'Binding Dark', 'Black Mamba', 'Black Scales', 'Black Thread', 'Black Thread Light', 'Black Twill', 'Blue Stripes', 'Brick Wall', 'Bright Squares', 'Brilliant', 'Carbon Fibre', 'Cartographer', 'Dark Circles', 'Dark Matter', 'Dimension', 'Escheresque', 'Hytale Mold', 'Hytale Bricks', 'Hytale Creepers');
			    			for($i = 0; $i < count($grads); $i++) {
			    				echo '<option value="'.$i.'">'.$grads[$i].'</option>';
			    			}
			    			?>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Navigation Position</label>
			    		<select name="menu">
			    			<option value="0" selected>Static</option>
			    			<option value="1">Fixed Attached</option>
			    		</select>
			  		</div>
					<div class="field">
			    		<label>Servers Tables Styling</label>
			    		<select name="tables">
			    			<option value="0" selected>Very Basic</option>
			    			<option value="1">Basic</option>
			    			<option value="2">Striped</option>
			    		</select>
			  		</div>
					<div class="field">
			    		<label>Logo Align</label>
			    		<select name="logoAlign">
			    			<option value="0" selected>Left</option>
			    			<option value="2">Center</option>
			    			<option value="3">Right</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Show Statistics</label>
			    		<select name="showStats">
			    			<option value="0" selected>Yes</option>
			    			<option value="1">No</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Statistics Position</label>
			    		<select name="statPosition">
			    			<option value="1" selected>Top</option>
			    			<option value="0">Bottom</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>Default?</label>
			    		<select name="default">
			    			<option value="0" selected>---</option>
			    			<option value="1">Yes</option>
			    		</select>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/designs">
  						Cancel		
  					</a>
				</form>
				<?php
					} else {
						redirect($txt['SITE_LINK'].'cp/admin');
					}
				} elseif ($adminSub == 'languages') {
					if (!$action) {
						uniSession('admin_language_error');
						uniSession('admin_language_success');
				?>
				<table class="ui basic table unstackable" style="margin-top:0">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Name</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
		 		$sql = "SELECT id, flagCode, name, isDefault FROM php_languages ORDER BY isDefault DESC";
		  		$allinfo = getArray($sql);
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
		      			$name = $info['name'];
		      			$flagCode = $info['flagCode'];
		      			$isDefault = $info['isDefault'];
						if ($isDefault == 1) {
      						$disabled = ' disabled';
      						$text = ' (Default)';
      					} else {
      						$disabled = '';
      						$text = '';
      					}
      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important"><i class="'.$flagCode.' flag"></i> '.$name.$text.'</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/languages/'.$id.'.edit" data-tooltip="Edit Language">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm'.$disabled.'" data-confirm="Are you sure you want to delete this language?" href="'.$txt['SITE_LINK'].'/cp/admin/languages/'.$id.'.delete" data-tooltip="Delete Language"'.$disabled.'>
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				?>
				</table>
				<a class="ui button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/languages/add">
  					<i class="plus icon"></i> Add			
  				</a>
				<?php
					} elseif ($action == 'edit') {
						$info = fetchArray('SELECT name, code, flagCode, isDefault FROM php_languages WHERE id = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_language'); ?>" method="post">
					<h4 class="ui dividing header">Edit Language</h4>
			  		<div class="required field">
			    		<label>Language Name</label>
			    		<input type="hidden" name="id" value="<?php echo $id; ?>">
			    		<input type="hidden" name="oldName" value="<?php echo $info['code']; ?>">
			    		<input type="text" name="name" placeholder="Language Name" maxlength="25" value="<?php echo $info['name']; ?>" required>
			    		<small>Name which will appear in languages selection dropdown</small>
			  		</div>
			  		<div class="required field">
			    		<label>Language Code</label>
			    		<input type="text" name="code" placeholder="Language Code" maxlength="5" value="<?php echo $info['code']; ?>" required>
			    		<small>Find your language code <a href="http://www.loc.gov/standards/iso639-2/php/code_list.php">here</a> (Check 'ISO 639-1 Code' column)</small>
			  		</div>
			  		<div class="required field">
			    		<label>Flag Code</label>
			    		<input type="text" name="flagCode" placeholder="Flag Code" maxlength="2" value="<?php echo $info['flagCode']; ?>" required>
			    		<small>All Semantic UI flag codes list <a href="https://semantic-ui.com/elements/flag.html">here</a> (Check 'ISO 3166-2 Code' column)</small>
			  		</div>
			  		<div class="field">
			    		<label>Default?</label>
			    		<select name="default">
			    			<?php if (!$info['isDefault']) { ?>
			    			<option value="0"<?php if (!$info['isDefault']) { ?> selected <?php } ?>>---</option>
			    			<?php } ?>
			    			<option value="1"<?php if ($info['isDefault'] == 1) { ?> selected <?php } ?>>Yes</option>
			    		</select>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/languages">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							redirect($txt['SITE_LINK'].'/cp/admin/languages');
						}
					} elseif ($action == 'delete') {
						$check = numRows('SELECT COUNT(*) FROM php_languages WHERE id = :id AND isDefault = 0', array(':id' => $id));
						if ($check) {
							query('DELETE FROM php_languages WHERE id = :id AND isDefault = 0 LIMIT 1', array(':id' => $id));
							writeSession('admin_language_success', "
							<div class='ui success message'>
								<i class='close icon'></i>
								<div class='header'>Success</div>
								<p>Language successfully deleted</p>
							</div>");
						} else {
							writeSession('admin_language_error', "
							<div class='ui error message'>
								<i class='close icon'></i>
								<div class='header'>Error</div>
								<p>Can't delete default language!</p>
							</div>");
						}
						redirect($txt['SITE_LINK'].'/cp/admin/languages');
					} elseif ($action == 'add') {
				?>
				<form class="ui form" action="#<?php validate('admin_add_language'); ?>" method="post">
					<h4 class="ui dividing header">Add Language</h4>
			  		<div class="required field">
			    		<label>Language Name</label>
			    		<input type="text" name="name" placeholder="Language Name" maxlength="25" required>
			    		<small>Name which will appear in languages selection dropdown</small>
			  		</div>
			  		<div class="required field">
			    		<label>Language Code</label>
			    		<input type="text" name="code" placeholder="Language Code" maxlength="5" required>
			    		<small>Find your language code <a href="http://www.loc.gov/standards/iso639-2/php/code_list.php">here</a> (Check 'ISO 639-1 Code' column)</small>
			  		</div>
			  		<div class="required field">
			    		<label>Flag Code</label>
			    		<input type="text" name="flagCode" placeholder="Flag Code" maxlength="2" required>
			    		<small>All Semantic UI flag codes list <a href="https://semantic-ui.com/elements/flag.html">here</a> (Check 'ISO 3166-2 Code' column)</small>
			  		</div>
			  		<div class="field">
			    		<label>Default?</label>
			    		<select name="default">
			    			<option value="0" selected>---</option>
			    			<option value="1">Yes</option>
			    		</select>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/languages">
  						Cancel		
  					</a>
				</form>
				<?php
					} else {
						redirect($txt['SITE_LINK'].'cp/admin');
					}
				} elseif ($adminSub == 'sponsored') {
					$site_info = fetchArray('SELECT * FROM php_settings LIMIT 1');
					uniSession('admin_sponsored_success');
					uniSession('admin_sponsored_error');
					if (!$action) {
				?>
				<form class="ui form" action="#<?php validate('admin_sponsored'); ?>" method="post">
					<h4 class="ui dividing header">Sponsored Settings</h4>
			  		<div class="required field">
			  			<label>Sponsored Selling Method</label>
			    		<select name="sponsored">
			    			<option value="0"<?php if (!$site_info['sponsored']) { ?> selected <?php } ?>>Buy Monthly</option>
			    			<option value="1"<?php if ($site_info['sponsored'] == 1) { ?> selected <?php } ?>>Auction</option>
			    		</select>
			    		<small>Select how you want to sell sponsored status</small>
			  		</div>
			  		<div class="required field">
			    		<label>Slots for Sale</label>
			    		<input type="number" name="sponsoredamount" placeholder="Slots for Sale" value="<?php echo $site_info['sponsoredamount']; ?>" min="1" max="99" maxlength="2" required>
			  		</div>
			  		<button class="ui tiny button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui tiny red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/languages">
  						Cancel		
  					</a>
			  	</form>
			  	<hr>
			  	<table class="ui basic table unstackable">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Auctions</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
		 		$sql = "SELECT * FROM php_auction ORDER BY id DESC";
		  		$allinfo = getArray($sql);
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
		      			$minBid = $info['minBid'];
		      			$period = $info['period'];

      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">ID: '.$id.' | Min Bid: '.$minBid.' ('.defaultCurrency.') | Period: '.$period.' days</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/sponsored/'.$id.'.edit" data-tooltip="Edit Auction">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm" data-confirm="Are you sure you want to delete this auction?" href="'.$txt['SITE_LINK'].'/cp/admin/sponsored/'.$id.'.delete" data-tooltip="Delete Auction">
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				else {
					echo '
      					<tr>
      						<td colspan="2" style="text-align: center">
      							No Auctions
  							</td>
  						</tr>';
				}
				?>
				</table>
				<a class="ui tiny button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored/add-auction">
  					<i class="plus icon"></i> Add Auction		
  				</a>
  				<hr>
  				<table class="ui basic table unstackable">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Purchase Options</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
		 		$sql = "SELECT id, period, price FROM php_sponsoredoptions";
		  		$allinfo = getArray($sql);
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
		      			$period = $info['period'];
		      			$price = $info['price'];

      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">'.$price.' ('.defaultCurrency.') for '.$period.' days</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/sponsored/'.$id.'.editopt" data-tooltip="Edit Option">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm" data-confirm="Are you sure you want to delete this option?" href="'.$txt['SITE_LINK'].'/cp/admin/sponsored/'.$id.'.deleteopt" data-tooltip="Delete Option">
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				else {
					echo '
      				<tr>
      					<td colspan="2" style="text-align: center">
      						No Purchase Options
  						</td>
  					</tr>';
				}
				?>
				</table>
				<a class="ui tiny button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored/add-option">
  					<i class="plus icon"></i> Add Purchase Option		
  				</a>
  				<hr>
			  	<table class="ui basic table unstackable">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Sponsored Servers</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
				$allinfo = getArray("SELECT server FROM php_sponsored WHERE start <= :start AND finish > :finish ORDER BY position DESC LIMIT ".sponsored_setti_two, array(':start' => date("Y-m-d H:i"), ':finish' => date("Y-m-d H:i")));
				if ($allinfo)
				{
					$i = 0;
					foreach($allinfo as $key => $sponsorInfo)
					{
						$id = safeInput($sponsorInfo['server']);

						$info = fetchArray("SELECT name FROM php_servers WHERE id = :id", array(':id' => $id));
						$seo_name = safeInput($info['seo_name']);
						$name = safeInput($info['name']);		

      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">'.$name.'</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/sponsored/'.$id.'.editsponsor" data-tooltip="Edit Sponsored Server">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm" data-confirm="Are you sure you want to delete this sponsored server?" href="'.$txt['SITE_LINK'].'/cp/admin/sponsored/'.$id.'.deletesponsor" data-tooltip="Delete Sponsored Server">
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				else {
					echo '
      					<tr>
      						<td colspan="2" style="text-align: center">
      							No Sponsored Servers
  							</td>
  						</tr>';
				}
				?>
				</table>
				<a class="ui tiny button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored/add-sponsored">
  					<i class="plus icon"></i> Add Sponsored Server		
  				</a>
  				<hr>
			  	<table class="ui basic table unstackable">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Discount Coupons</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
				$allinfo = getArray("SELECT id, coupon, discount FROM php_coupons ORDER BY coupon DESC");
				if ($allinfo)
				{
					$i = 0;
					foreach($allinfo as $key => $info)
					{
						$id = safeInput($info['id']);
						$coupon = safeInput($info['coupon']);
						$discount = safeInput($info['discount']);

      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">'.$coupon.' (<strong>'.$discount.'%</strong>)</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/sponsored/'.$id.'.editcoupon" data-tooltip="Edit Discount Coupon">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm" data-confirm="Are you sure you want to delete this sponsored server?" href="'.$txt['SITE_LINK'].'/cp/admin/sponsored/'.$id.'.deletecoupon" data-tooltip="Delete Discount Coupon">
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				else {
					echo '
      					<tr>
      						<td colspan="2" style="text-align: center">
      							No Discount Coupons
  							</td>
  						</tr>';
				}
				?>
				</table>
				<a class="ui tiny button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored/add-coupon">
  					<i class="plus icon"></i> Add Discount Coupon	
  				</a>
  				<?php
  					} elseif ($action == 'delete') {
						query('DELETE FROM php_auction WHERE id = :id LIMIT 1', array(':id' => $id));
						writeSession('admin_sponsored_success', "
						<div class='ui success message'>
							<i class='close icon'></i>
							<div class='header'>Success</div>
							<p>Auction successfully deleted</p>
						</div>");
						redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
					} elseif ($action == 'deletecoupon') {
						query('DELETE FROM php_coupons WHERE id = :id LIMIT 1', array(':id' => $id));
						writeSession('admin_sponsored_success', "
						<div class='ui success message'>
							<i class='close icon'></i>
							<div class='header'>Success</div>
							<p>Coupon successfully deleted</p>
						</div>");
						redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
					} elseif ($action == 'deleteopt') {
						query('DELETE FROM php_sponsoredoptions WHERE id = :id LIMIT 1', array(':id' => $id));
						writeSession('admin_sponsored_success', "
						<div class='ui success message'>
							<i class='close icon'></i>
							<div class='header'>Success</div>
							<p>Purchase option successfully deleted</p>
						</div>");
						redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
					} elseif ($action == 'deletesponsor') {
						query('DELETE FROM php_sponsored WHERE server = :id LIMIT 1', array(':id' => $id));
						writeSession('admin_sponsored_success', "
						<div class='ui success message'>
							<i class='close icon'></i>
							<div class='header'>Success</div>
							<p>Sponsored status successfully removed</p>
						</div>");
						redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
					} elseif ($action == 'add-auction') {
				?>
				<form class="ui form" action="#<?php validate('admin_add_auction'); ?>" method="post">
					<h4 class="ui dividing header">Add Auction</h4>
			  		<div class="required field">
			    		<label>Start Date</label>
			    		<input type="text" name="start" placeholder="Start Date" maxlength="20" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Finish Date</label>
			    		<input type="text" name="finish" placeholder="Finish Date" maxlength="20" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Deadline Date</label>
			    		<input type="text" name="deadline" placeholder="Deadline Date" maxlength="20" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Min Bid</label>
			    		<input type="number" name="minBid" placeholder="Min Bid" maxlength="4" value="5" min="1" max="9999" required>
			  		</div>
			  		<div class="required field">
			    		<label>Period (days)</label>
			    		<input type="number" name="period" placeholder="Period" maxlength="3" value="30" min="1" max="999" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored">
  						Cancel		
  					</a>
				</form>
				<?php
					} elseif ($action == 'add-sponsored') {
				?>
				<form class="ui form" action="#<?php validate('admin_add_sponsorstatus'); ?>" method="post">
					<h4 class="ui dividing header">Give Sponsored Status</h4>
					<div class="required field">
			    		<label>Server ID</label>
			    		<input type="number" name="server" placeholder="Server ID" maxlength="5" required>
			  		</div>
			  		<div class="required field">
			    		<label>Position</label>
			    		<input type="number" name="position" placeholder="Position" maxlength="4" required>
			    		<small>Which sponsored slot you give: from 1 to <?php echo sponsored_setti_two; ?></small>
			  		</div>
			  		<div class="required field">
			    		<label>Start Date</label>
			    		<input type="text" name="start" placeholder="Start Date" maxlength="20" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Finish Date</label>
			    		<input type="text" name="finish" placeholder="Finish Date" maxlength="20" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored">
  						Cancel		
  					</a>
				</form>
				<?php
					} elseif ($action == 'editsponsor') {
						$info = fetchArray('SELECT * FROM php_sponsored WHERE server = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_sponsorstatus'); ?>" method="post">
					<h4 class="ui dividing header">Edit Sponsored Status</h4>
					<div class="required field">
						<input type="hidden" value="<?php echo $info['server']; ?>" name="id">
			    		<label>Server ID</label>
			    		<input type="number" name="server" placeholder="Server ID" maxlength="5" value="<?php echo $info['server']; ?>" required>
			  		</div>
			  		<div class="required field">
			    		<label>Position</label>
			    		<input type="number" name="position" placeholder="Position" maxlength="4" value="<?php echo $info['position']; ?>" required>
			    		<small>Which sponsored slot you give: from 1 to <?php echo sponsored_setti_two; ?></small>
			  		</div>
			  		<div class="required field">
			    		<label>Start Date</label>
			    		<input type="text" name="start" placeholder="Start Date" maxlength="20" value="<?php echo $info['start']; ?>" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Finish Date</label>
			    		<input type="text" name="finish" placeholder="Finish Date" maxlength="20" value="<?php echo $info['finish']; ?>" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
						}
					} elseif ($action == 'add-coupon') {
				?>
				<form class="ui form" action="#<?php validate('admin_add_coupon'); ?>" method="post">
					<h4 class="ui dividing header">Add Discount Coupon</h4>
					<div class="required field">
			    		<label>Coupon Code</label>
			    		<input type="text" name="coupon" placeholder="Coupon Code" maxlength="10" required>
			  		</div>
			  		<div class="required field">
			    		<label>Expiry Date</label>
			    		<input type="text" name="finish" placeholder="Expiry Date" maxlength="20" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Discount Given (%)</label>
			    		<input type="number" name="discount" placeholder="Discount Given (%)" maxlength="3" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored">
  						Cancel		
  					</a>
				</form>
				<?php
					} elseif ($action == 'editcoupon') {
						$info = fetchArray('SELECT * FROM php_coupons WHERE id = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_coupon'); ?>" method="post">
					<h4 class="ui dividing header">Edit Discount Coupon</h4>
			  		<div class="required field">
			  			<input type="hidden" value="<?php echo $info['id']; ?>" name="id">
			    		<label>Coupon Code</label>
			    		<input type="text" name="coupon" placeholder="Coupon Code" maxlength="10" value="<?php echo $info['coupon']; ?>" required>
			  		</div>
			  		<div class="required field">
			    		<label>Expiry Date</label>
			    		<input type="text" name="finish" placeholder="Expiry Date" maxlength="20" value="<?php echo $info['finish']; ?>" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Discount Given (%)</label>
			    		<input type="number" name="discount" placeholder="Discount Given (%)" maxlength="3" value="<?php echo $info['discount']; ?>" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
						}
					} elseif ($action == 'edit') {
						$info = fetchArray('SELECT * FROM php_auction WHERE id = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_auction'); ?>" method="post">
					<h4 class="ui dividing header">Edit Auction</h4>
			  		<div class="required field">
			    		<label>Start Date</label>
			    		<input type="hidden" value="<?php echo $info['id']; ?>" name="id">
			    		<input type="text" name="start" placeholder="Start Date" maxlength="20" value="<?php echo $info['start']; ?>" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Finish Date</label>
			    		<input type="text" name="finish" placeholder="Finish Date" maxlength="20" value="<?php echo $info['finish']; ?>" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Deadline Date</label>
			    		<input type="text" name="deadline" placeholder="Deadline Date" maxlength="20" value="<?php echo $info['deadline']; ?>" required>
			    		<small>Date Format: YYYY-MM-DD HH:ii</small>
			  		</div>
			  		<div class="required field">
			    		<label>Min Bid</label>
			    		<input type="number" name="minBid" placeholder="Min Bid" maxlength="4" value="<?php echo $info['minBid']; ?>" min="1" max="9999" required>
			  		</div>
			  		<div class="required field">
			    		<label>Period (days)</label>
			    		<input type="number" name="period" placeholder="Period" maxlength="3" value="<?php echo $info['period']; ?>" min="1" max="999" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
						}
					} elseif ($action == 'add-option') {
				?>
				<form class="ui form" action="#<?php validate('admin_add_option'); ?>" method="post">
					<h4 class="ui dividing header">Add Purchase Option</h4>
					<div class="required field">
			    		<label>Period (days)</label>
			    		<input type="number" name="period" placeholder="Period" maxlength="4" min="1" max="9999" required>
			  		</div>
			  		<div class="required field">
			    		<label>Price</label>
			    		<input type="number" name="price" placeholder="Price" maxlength="4" min="1" max="9999" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored">
  						Cancel		
  					</a>
				</form>
				<?php
					} elseif ($action == 'editopt') {
						$info = fetchArray('SELECT * FROM php_sponsoredoptions WHERE id = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_option'); ?>" method="post">
					<h4 class="ui dividing header">Edit Purchase Option</h4>
			  		<div class="required field">
			    		<label>Period (days)</label>
			    		<input type="hidden" value="<?php echo $info['id']; ?>" name="id">
			    		<input type="number" name="period" placeholder="Period" maxlength="3" value="<?php echo $info['period']; ?>" min="1" max="9999" required>
			  		</div>
			  		<div class="required field">
			    		<label>Price</label>
			    		<input type="number" name="price" placeholder="Min Bid" maxlength="4" value="<?php echo $info['price']; ?>" min="1" max="9999" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/sponsored">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							redirect($txt['SITE_LINK'].'/cp/admin/sponsored');
						}
					} else {
						redirect($txt['SITE_LINK'].'/cp/admin');
					}	
				} elseif ($adminSub == 'users') {
					uniSession('admin_users_success');
					uniSession('admin_users_error');
					if (!$action) {
				?>
				<table class="ui basic table unstackable" style="margin-top:0">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Users</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
		 		$sql = "SELECT * FROM php_users ORDER BY date DESC";
		  		$allinfo = getArray($sql);
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
		      			$name = $info['name'];
		      			$rank = $info['rank'];

		      			if ($rank == '1') {
		      				$disabled = ' disabled';
		      			} else {
		      				$disabled = '';
		      			}

      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">'.$name.'</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/users/'.$id.'.edit" data-tooltip="Edit User">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm'.$disabled.'" data-confirm="Are you sure you want to delete this user?" href="'.$txt['SITE_LINK'].'/cp/admin/users/'.$id.'.delete" data-tooltip="Delete User"'.$disabled.'>
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				else {
					echo '
      					<tr>
      						<td colspan="2" style="text-align: center">
      							No Users
  							</td>
  						</tr>';
				}
				?>
				</table>
				<?php
					} elseif ($action == 'edit') {
						$info = fetchArray('SELECT * FROM php_users WHERE rank != 1 AND id = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_user'); ?>" method="post">
					<h4 class="ui dividing header">Edit User</h4>
			  		<div class="required field">
			    		<label>Username</label>
			    		<input type="hidden" value="<?php echo $info['id']; ?>" name="id">
			    		<input type="text" name="name" placeholder="Username" maxlength="30" value="<?php echo $info['name']; ?>" required>
			  		</div>
			  		<div class="required field">
			    		<label>Email</label>
			    		<input type="text" name="email" placeholder="Email" maxlength="100" value="<?php echo $info['email']; ?>" required>
			  		</div>
			  		<div class="field">
			    		<label>Banned?</label>
			    		<select name="isBanned">
			    			<option value="0"<?php if (!$info['isBanned']) { ?> selected <?php } ?>>No</option>
			    			<option value="1"<?php if ($info['isBanned'] == 1) { ?> selected <?php } ?>>Yes</option>
			    		</select>
			  		</div>
			  		<div class="field">
			    		<label>If Banned - Reason</label>
			    		<input type="text" name="banReason" placeholder="Reason for Ban" maxlength="200" value="<?php echo $info['banReason']; ?>">
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/users">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							writeSession('admin_users_error', "
							<div class='ui error message'>
								<i class='close icon'></i>
								<div class='header'>Error</div>
								<p>Can't edit owner's account!</p>
							</div>");
							redirect($txt['SITE_LINK'].'/cp/admin/users');
						}
					} elseif ($action == 'delete') {
						$check = numRows('SELECT COUNT(*) FROM php_users WHERE id = :id AND rank != 1', array(':id' => $id));
						if ($check) {
							query('DELETE FROM php_users WHERE id = :id AND rank != 1 LIMIT 1', array(':id' => $id));
							writeSession('admin_users_success', "
							<div class='ui success message'>
								<i class='close icon'></i>
								<div class='header'>Success</div>
								<p>User successfully deleted</p>
							</div>");
						} else {
							writeSession('admin_users_error', "
							<div class='ui error message'>
								<i class='close icon'></i>
								<div class='header'>Error</div>
								<p>Can't delete owner's account!</p>
							</div>");
						}
						redirect($txt['SITE_LINK'].'/cp/admin/users');
					} else {
						redirect($txt['SITE_LINK'].'/cp/admin');
					}	
				} elseif ($adminSub == 'types') {
					uniSession('admin_types_success');
					uniSession('admin_types_error');
					if (!$action) {
				?>
				<table class="ui basic table unstackable" style="margin-top:0">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Types</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
		 		$sql = "SELECT * FROM php_types ORDER BY id";
		  		$allinfo = getArray($sql);
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
		      			$name = $info['name'];

      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">'.$name.'</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/types/'.$id.'.edit" data-tooltip="Edit Type">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm" data-confirm="Are you sure you want to delete this type?" href="'.$txt['SITE_LINK'].'/cp/admin/types/'.$id.'.delete" data-tooltip="Delete Type">
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				else {
					echo '
      					<tr>
      						<td colspan="2" style="text-align: center">
      							No Types
  							</td>
  						</tr>';
				}
				?>
				</table>
				<a class="ui tiny button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/types/add">
  					<i class="plus icon"></i> Add Type	
  				</a>
				<?php
					} elseif ($action == 'edit') {
						$info = fetchArray('SELECT * FROM php_types WHERE id = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_type'); ?>" method="post">
					<h4 class="ui dividing header">Edit Type</h4>
			  		<div class="required field">
			    		<label>Type Name</label>
			    		<input type="hidden" value="<?php echo $info['id']; ?>" name="id">
			    		<input type="text" name="name" placeholder="Type Name" maxlength="20" value="<?php echo $info['name']; ?>" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/types">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							redirect($txt['SITE_LINK'].'/cp/admin/types');
						}
					} elseif ($action == 'add') {
				?>
				<form class="ui form" action="#<?php validate('admin_add_type'); ?>" method="post">
					<h4 class="ui dividing header">Add Type</h4>
			  		<div class="required field">
			  			<label>Type Name</label>
			    		<input type="text" name="name" placeholder="Type Name" maxlength="20" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/types">
  						Cancel		
  					</a>
				</form>
				<?php
					} elseif ($action == 'delete') {
						$check = numRows('SELECT COUNT(*) FROM php_types WHERE id = :id', array(':id' => $id));
						if ($check) {
							query('DELETE FROM php_types WHERE id = :id LIMIT 1', array(':id' => $id));
							writeSession('admin_types_success', "
							<div class='ui success message'>
								<i class='close icon'></i>
								<div class='header'>Success</div>
								<p>Type successfully deleted</p>
							</div>");
						} else {
							# ok
						}
						redirect($txt['SITE_LINK'].'/cp/admin/types');
					} else {
						redirect($txt['SITE_LINK'].'/cp/admin');
					}
				} elseif ($adminSub == 'versions') {
					uniSession('admin_versions_success');
					uniSession('admin_versions_error');
					if (!$action) {
				?>
				<table class="ui basic table unstackable" style="margin-top:0">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Versions</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
		 		$sql = "SELECT * FROM php_versions ORDER BY id";
		  		$allinfo = getArray($sql);
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
		      			$name = $info['name'];

      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">'.$name.'</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/admin/versions/'.$id.'.edit" data-tooltip="Edit Version">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm" data-confirm="Are you sure you want to delete this version?" href="'.$txt['SITE_LINK'].'/cp/admin/versions/'.$id.'.delete" data-tooltip="Delete Version">
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				else {
					echo '
      					<tr>
      						<td colspan="2" style="text-align: center">
      							No Versions
  							</td>
  						</tr>';
				}
				?>
				</table>
				<a class="ui tiny button submit" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/versions/add">
  					<i class="plus icon"></i> Add Version	
  				</a>
				<?php
					} elseif ($action == 'edit') {
						$info = fetchArray('SELECT * FROM php_versions WHERE id = :id LIMIT 1', array(':id' => $id));
						if ($info) {
				?>
				<form class="ui form" action="#<?php validate('admin_edit_version'); ?>" method="post">
					<h4 class="ui dividing header">Edit Version</h4>
			  		<div class="required field">
			    		<label>Version Name</label>
			    		<input type="hidden" value="<?php echo $info['id']; ?>" name="id">
			    		<input type="text" name="name" placeholder="Version Name" maxlength="20" value="<?php echo $info['name']; ?>" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/versions">
  						Cancel		
  					</a>
				</form>
				<?php
						} else {
							redirect($txt['SITE_LINK'].'/cp/admin/versions');
						}
					} elseif ($action == 'add') {
				?>
				<form class="ui form" action="#<?php validate('admin_add_version'); ?>" method="post">
					<h4 class="ui dividing header">Add Version</h4>
			  		<div class="required field">
			  			<label>Version Name</label>
			    		<input type="text" name="name" placeholder="Version Name" maxlength="20" required>
			  		</div>
			  		<button class="ui button submit" type="submit" name="submit"><?php echo $txt['txt116']; ?></button>
			  		<a class="ui red basic button" href="<?php echo $txt['SITE_LINK']; ?>/cp/admin/versions">
  						Cancel		
  					</a>
				</form>
				<?php
					} elseif ($action == 'delete') {
						$check = numRows('SELECT COUNT(*) FROM php_versions WHERE id = :id', array(':id' => $id));
						if ($check) {
							query('DELETE FROM php_versions WHERE id = :id LIMIT 1', array(':id' => $id));
							writeSession('admin_versions_success', "
							<div class='ui success message'>
								<i class='close icon'></i>
								<div class='header'>Success</div>
								<p>Version successfully deleted</p>
							</div>");
						} else {
							# ok
						}
						redirect($txt['SITE_LINK'].'/cp/admin/versions');
					} else {
						redirect($txt['SITE_LINK'].'/cp/admin');
					}
				} elseif ($adminSub == 'servers') {
					$pg = safeInput(isset($_GET['pg'])) ? safeInput($_GET['pg']) : '';
					uniSession('admin_servers_success');
					uniSession('admin_servers_error');
					if (!$action) {
					// Pagination
			  		$total_pages = numRows("SELECT COUNT(1) FROM php_servers");
			  		$targetpage = $txt['SITE_LINK'].'/cp/admin/servers'; 
			  		$limit = 25; 
					$stages = 1;
					if($pg) {
						$start = ($pg - 1) * $limit;
					} else {
						$start = 0;
					}
					if ($pg == 0) {
						$pg = 1;
					}
					$prev = $pg - 1;
					$next = $pg + 1;
					$lastpage = ceil($total_pages/$limit);
					if ($pg == 1) {
						$rel_prev = '';
					} else {
						if ($prev == 1) {
							$rel_prev = '<link rel="prev" href="'.$targetpage.'">';
						} else {
							$rel_prev = '<link rel="prev" href="'.$targetpage.'/pg.'.$prev.'">';
						}
					}
					if ($lastpage > $pg) {
						$rel_next = '<link rel="next" href="'.$targetpage.'/pg.'.$next.'">';
					} else {
						$rel_next = '';
					}
					$paginate = '';
					if($lastpage > 1) {
						if ($lastpage < 7 + ($stages * 2)) {
							for ($counter = 1; $counter <= $lastpage; $counter++) {
								if ($counter == $pg) {
									$paginate.= "<span class='item active' itemprop='url'>$counter</span>";
								} else {
									if ($counter == 1) {
										$paginate.= "<a class='item' itemprop='url name' href='$targetpage'>$counter</a>";
									} else {
										$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$counter'>$counter</a>";
									}
								}
							}
						} elseif ($lastpage > 5 + ($stages * 2)) {
							if ($pg < 1 + ($stages * 2)) {
								for ($counter = 1; $counter < 4 + ($stages * 2); $counter++) {
									if ($counter == $pg) {
										$paginate.= "<span class='item active' itemprop='url'>$counter</span>";
									} else {
										if ($counter == 1) {
											$paginate.= "<a class='item' itemprop='url name' href='$targetpage'>$counter</a>";
										} else {
											$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$counter'>$counter</a>";
										}
									}
								}
								$paginate.= "<span class='item'>...</span>";
								$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$lastpage'>$lastpage</a>";
							} elseif ($lastpage - ($stages * 2) > $pg && $pg > ($stages * 2)) {
								$paginate.= "<a class='item' itemprop='url name' href='$targetpage'>1</a>";
								$paginate.= "<span class='item'>...</span>";
								for ($counter = $pg - $stages; $counter <= $pg + $stages; $counter++) {
									if ($counter == $pg) {
										$paginate.= "<span class='item active' itemprop='url'>$counter</span>";
									} else {
										$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$counter'>$counter</a>";
									}
								}
								$paginate.= "<span class='item'>...</span>";
								$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$lastpage'>$lastpage</a>";
							} else {
								$paginate.= "<a class='item' itemprop='url name' href='$targetpager'>1</a>";
								$paginate.= "<span class='item'>...</span>";
								for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++) {
									if ($counter == $pg) {
										$paginate.= "<span class='item active' itemprop='url'>$counter</span>";
									} else {
										$paginate.= "<a class='item' itemprop='url name' href='$targetpage/pg.$counter'>$counter</a>";
									}
								}
							}
						}
					}
					if ($lastpage >= $pg || $pg == 1) {
						# ok
					} else {
						redirect($txt['SITE_LINK'].'/cp/admin/servers');
					}
					if($pg > 1) { $i = ($pg - 1) * $limit; } else { $i = 0; }
				?>
				<table class="ui basic table unstackable" style="margin-top:0">
					<thead>
						<th style="width: 70%; padding-left: 1em!important">Servers</th>
						<th><i class="pencil alternate icon"></i> Action</th>
					</thead>
				<?php
				$sql = "SELECT id, name FROM php_servers ORDER BY date DESC LIMIT ".$start.", 25";
		  		$allinfo = getArray($sql);
				if ($allinfo)
				{
					foreach($allinfo as $key => $info)
					{
					   	$id = $info['id'];
		      			$name = $info['name'];

      					echo '
      					<tr>
      						<td style="width: 70%; padding-left: 1em!important">'.$name.'</td>
      						<td>
      							<a class="ui tiny button" href="'.$txt['SITE_LINK'].'/cp/server/edit.'.$id.'" data-tooltip="Edit Server">
  									Edit		
  								</a>
  								<a class="ui tiny red basic button confirm" data-confirm="Are you sure you want to delete this server?" href="'.$txt['SITE_LINK'].'/cp/admin/servers/'.$id.'.delete" data-tooltip="Delete Server">
  									Delete		
  								</a>
  							</td>
  						</tr>';
		    		}
				}
				else {
					echo '
      					<tr>
      						<td colspan="2" style="text-align: center">
      							No Servers
  							</td>
  						</tr>';
				}
				?>
				</table>
				<?php
  				if ($paginate) {
  				?>
  				<table>
		  		<tfoot>
		    		<tr>
		    			<th style="padding:0!important">
		    				<div class="ui left floated pagination menu" itemscope itemtype="http://schema.org/SiteNavigationElement">
		    				<?php echo $paginate; ?>
		      				</div>
		      			</th>
		      		</tr>
		      	</tfoot>
		      	</table>
				<?php
				} else { }
				?>
				<?php
					} elseif ($action == 'delete') {
						query('DELETE FROM php_servers WHERE id = :id LIMIT 1', array(':id' => $id));
						writeSession('admin_servers_success', "
						<div class='ui success message'>
							<i class='close icon'></i>
							<div class='header'>Success</div>
							<p>Server successfully deleted</p>
						</div>");
						redirect($txt['SITE_LINK'].'/cp/admin/servers');
					} else {
						redirect($txt['SITE_LINK'].'/cp/admin');
					}
				} else {
					redirect($txt['SITE_LINK'].'/cp');
				}
			} else {
				redirect($txt['SITE_LINK'].'/cp');
			}
			?>		
			</div>
		</div>
	</div>
</div>
<?php
} elseif ($get == 'terms') {
	essentialOne(NULL, $txt['txt163']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="clipboard icon"></i> <?php echo $txt['txt163']; ?>
	</div>
	<div class="ui tall stacked segment form"><?php echo $txt['terms']; ?></div>
</div>
<?php
} elseif ($get == 'privacy') {
	essentialOne(NULL, $txt['txt164']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="clipboard icon"></i> <?php echo $txt['txt164']; ?>
	</div>
	<div class="ui tall stacked segment form"><?php echo $txt['privacy']; ?></div>
</div>
<?php
} elseif ($get == 'help') {
	essentialOne(NULL, $txt['txt170']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="clipboard icon"></i> <?php echo $txt['txt170']; ?>
	</div>
	<div class="ui tall stacked segment form"><?php echo $txt['help']; ?></div>
</div>

<?php
} elseif ($get == 'news') {
	essentialOne(NULL, $txt['txt241']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="clipboard icon"></i> <?php echo $txt['txt241']; ?>
	</div>
	<div class="ui tall stacked segment form"><?php echo $txt['news']; ?>
	
	<?php include('news/news.php'); ?>
	</div>
</div>

<?php

} elseif ($get == 'news-what-are-the-best-hytale-servers') {
    essentialOne(NULL, $txt['txt242']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="clipboard icon"></i> <?php echo $txt['txt242']; ?>
	</div>
	<div class="ui tall stacked segment form"><?php echo $txt['news']; ?>

<?php include('news/what-are-the-best-hytale-servers.php'); ?>
</div>
</div>

<?php

} elseif ($get == 'news-types-of-hytale-servers') {
    essentialOne(NULL, $txt['txt243']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="clipboard icon"></i> <?php echo $txt['txt243']; ?>
	</div>
	<div class="ui tall stacked segment form"><?php echo $txt['news']; ?>

<?php include('news/types-of-hytale-servers.php'); ?>


<?php
}else {
	header("HTTP/1.0 404 Not Found");
	essentialOne(NULL, $txt['txt165']);
?>
<div class="ui segments">
	<div class="ui segment default-color header">
		<i class="info circle icon"></i> <?php echo $txt['txt165']; ?>
	</div>
	<div class="ui tall stacked segment form"><?php echo $txt['txt166']; ?></div>
</div>
<?php
}
?>	
</section>
<footer class="ui container">
	<?php
	if (showStats == 1 && statPosition) {
	$servers =numRows("SELECT COUNT(*) FROM php_servers");
	$users = numRows("SELECT COUNT(*) FROM php_users");
	$players = getTotal('players_online', 'php_servers');
	?>
	<div class="ui three tiny statistics" id="bottomStat">
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
	    		<i class="user plus icon"></i> <?php echo number_format('133', 0, '.', ' '); ?>
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
	<div class="clearfix"></div>
	<?php
	}
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
	<div class="hytale-blog">

		<h1><font color=red>NEWS: </font>Click <a href="news">HERE</a> to visit our Hytale Blog!</h1>
	
		</div>
		
		
		
		
		
		
	
	<div class="line">
	 
	    <br>
	    <br>
	    
	<div class="row">
			<div class="col-12">
				<div class="row">
	<div class="col1">
	<p class="footer_title">HYTALEONLINESERVERS.COM</p>
	<ul>
			<li><a href="/register">Register</a></li>
				<li><a href="/login">Login</a></li>
		<li><a href="/news">Hytale News</a></li>
			<li><a href="/sponsored">Sponsored Slots</a></li>
		<li><a href="/versions">Versions</a></li>
				<li><a href="/types">Types</a></li>
		<li><a href="/votifier-tester">Votifier Tester</a></li>
		<li><a href="/status-checker">Status Checker</a></li>
									</ul>
	</div>

	<div class="col2">
	<p class="footer_title">HYTALE LINKS & PARTNERS</p>
	<ul>
		<li><a href="https://hytale.com/" target="_blank">Hytale Official Website</a></li>
		<li><a href="https://www.hytalespy.com/">HytaleSpy</a></li>
			</ul>
	</div>

	<div class="col3">
	<p class="footer_title">LATEST NEWS</p>
	<ul>
		<li><a href="/news-what-are-the-best-hytale-servers">Best Hytale Servers?</a></li>
	</ul>
	</div>
	
		<div class="col4">
	<p class="footer_title">SUPPORT</p>
	<ul>
		<li><a href="/contacts/">Contact Us</a></li>
		<li><a href="/help">Help</a></li>
		<li><a href="/terms">Terms of Service</a></li>
		<li><a href="/privacy">Privacy Policy</a></li>
		
	</ul>
	</div>


</div>
		</div>
		</div>
		
		
		<br>
		<br>
<div class="end">
		<p>Copyright © 2020 <a href="http://hytaleonlineservers.com/" target="_blank">HytaleOnlineServers</a><br>
					<small><em>Hytale and associated Hytale images are copyright of Hypixel Studios.</em></small><br>
					<small><em>HytaleOnlineServers.com is not affiliated with Hytale and Hypixel Studios or Riot Games.</em></small>
					</p>
				</div>
	<br>
</footer>
</div>

<style>
   .row {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: 20;
}
    .center-element, footer .line {
    text-align: left;
}

.col1, .col2, .col3, .col4 {
    margin-right: 40;
    color:#f0cd6c !important;
}

.end {
    text-align:center;
}
</style>

<?php
$nagCookie = safeInput(isset($_COOKIE['cookies'])) ? safeInput($_COOKIE['cookies']) : '';
if(!$nagCookie) {
?>
<div class="ui inline cookie nag">
  	<span class="title">
   		<?php echo $txt['nag']; ?>
  	</span>
  	<i class="close icon"></i>
</div>
<?php
}
?>
<script src="/CSS/minified.js"></script>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
$('.ui.accordion').accordion();
$("form").submit(function(event) {
	$(".ui.button.submit").addClass('loading disabled');
});
</script>
<?php
$nagCookie = safeInput(isset($_COOKIE['cookies'])) ? safeInput($_COOKIE['cookies']) : '';
if(!$nagCookie) {
?>
<script>
$('.cookie.nag .close.icon').on('click', function() {
	Cookies.set('cookies', true);
	$(".cookie.nag").hide();
});
</script>
<?php
}
if (($get == 'server' && shareThis) || shareThis) {
?>
<script>
	var element = document.createElement('script');
	element.src = "https://platform-api.sharethis.com/js/sharethis.js#property=<?php echo shareThis; ?>&product=inline-share-buttons";
	setTimeout(function(){
		document.body.appendChild(element);
	},3000)}
if ($get == 'server' && $sub == 'statistics') {
	$id = safeInput($_GET['id']);
?>
<script src="/CSS/Chart.js"></script>
<script>
google.charts.load("visualization", "1", {packages:["corechart"]});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
	var jsonData = $.ajax({
        url: "/serversData/<?php echo $id; ?>.json",
        dataType: "json",
        async: false
    }).responseText;

	var data = new google.visualization.DataTable(jsonData);
	var options = {
		title: '<?php echo $txt['txt3'].' '.$txt['txt45']; ?> - <?php echo date('M Y'); ?>',
		'is3D':true,
		legend: 'bottom',
		hAxis: { 
			minValue: 0,
			format: 'short'
		},
		vAxis: {
			minValue: 0,
        	scaleType: 'log',
        	format: 'short'
  		},
    	pointSize: 5,
        series: {
            0: { pointShape: 'circle' },
            1: { pointShape: 'triangle' },
            2: { pointShape: 'square' },
            3: { pointShape: 'polygon' }
        }						
	};
	var chart = new google.visualization.LineChart(document.getElementById('serverStats'));
	chart.draw(data, options);
}
$(window).resize(function(){
	drawChart();
});
</script>
<?php
} else { }
?>

</body>
</html>
<?php
}
?>

<style>

@font-face {
  font-family:Penumbra;
  src: url('Penumbra.otf');
}

 

#main{
	font-family:fonta;
	
}
 

@import url("https://p.typekit.net/p.css?s=1&k=jut7cbo&ht=tk&f=36064.36066&a=18270221&app=typekit&e=css");

@font-face {
font-family:"penumbra-serif-std";
src:url("https://use.typekit.net/af/024c41/00000000000000003b9b0a89/27/l?primer=7cdcb44be4a7db8877ffa5c0007b8dd865b3bbc383831fe2ea177f62257a9191&fvd=n4&v=3") format("woff2"),url("https://use.typekit.net/af/024c41/00000000000000003b9b0a89/27/d?primer=7cdcb44be4a7db8877ffa5c0007b8dd865b3bbc383831fe2ea177f62257a9191&fvd=n4&v=3") format("woff"),url("https://use.typekit.net/af/024c41/00000000000000003b9b0a89/27/a?primer=7cdcb44be4a7db8877ffa5c0007b8dd865b3bbc383831fe2ea177f62257a9191&fvd=n4&v=3") format("opentype");
font-style:normal;font-weight:400;
}

@font-face {
font-family:"penumbra-serif-std";
src:url("https://use.typekit.net/af/7d45ff/00000000000000003b9b0a8b/27/l?primer=7cdcb44be4a7db8877ffa5c0007b8dd865b3bbc383831fe2ea177f62257a9191&fvd=n7&v=3") format("woff2"),url("https://use.typekit.net/af/7d45ff/00000000000000003b9b0a8b/27/d?primer=7cdcb44be4a7db8877ffa5c0007b8dd865b3bbc383831fe2ea177f62257a9191&fvd=n7&v=3") format("woff"),url("https://use.typekit.net/af/7d45ff/00000000000000003b9b0a8b/27/a?primer=7cdcb44be4a7db8877ffa5c0007b8dd865b3bbc383831fe2ea177f62257a9191&fvd=n7&v=3") format("opentype");
font-style:normal;font-weight:700;
}

.tk-penumbra-serif-std { font-family: "penumbra-serif-std",sans-serif; }

 .ui.large.header {
    font-weight:600 !important;
    font-family:penumbra-serif-std !important;
    font-size: 35px !important;
    text-transform: uppercase;
    margin: 24px 0; 
	position: relative;
    background: linear-gradient(#ffe98d, #e19f27);
    -webkit-background-clip: text;
	    letter-spacing: 1px;
	        position: relative;
    background: linear-gradient(#ffe98d, #e19f27);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 600;
    text-transform: uppercase;
    padding-bottom: 5px;
    letter-spacing: 1px;
}

 .ui.menu>.container, .ui.menu>.grid {
 font-weight:600 !important;
    justify-content: center;
    align-items: center;
    background-color: #203658;
    margin: 0 auto;
    font-size:!important;
    text-align: center;
  font-family:penumbra-serif-std !important;
    border-radius: 10px;
    border: 15px solid transparent;
    border-image: url(https://media.discordapp.net/attachments/530534623906627585/530538643719454722/border-image.png) 35%;
    z-index: 999;
     
 }
 
 
 <!---------today css------------->
 .copy-action, .copy-action button, nav .ui.button, #server .ui.label {
    color: #ffffff;
    background: #203658 !important;
}

.ui.buttons .or {
 
    display: none;
}
  
</style>