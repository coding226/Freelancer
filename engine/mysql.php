<?php
if(!defined('TOP_STARTED')) exit('Site security activated !');
	function query($sql, $params=array())
	{
		global $db;
		$sth = $db->prepare($sql)->execute($params);
		return $sth;
		$db = null;
		$sth = null;
	}
	
	function fetchArray($sql, $params=array())
	{
		global $db;
		if (!empty($params)) {
			$sth = $db->prepare($sql);
			$sth->execute($params);
		} else {
			$sth = $db->prepare($sql);
			$sth->execute();
		}
		$fetch_array = $sth->fetch();
		return $fetch_array;
		$db = null;
		$query = null;
	}
	
	function fetchRow($sql)
	{
		global $db;
		if (!empty($params)) {
			$sth = $db->prepare($sql);
			$sth->execute($params);
		} else {
			$sth = $db->query($sql);
		}
		while($row = $query->fetch_row())
		{
			$array[] = $row;
		}
		return $array;
	}

	function getArray($sql, $params=array())
	{
		global $db;
		if (!empty($params)) {
			$sth = $db->prepare($sql);
			$sth->execute($params);
		} else {
			$sth = $db->prepare($sql);
			$sth->execute();
		}
		
		while($row = $sth->fetch(PDO::FETCH_ASSOC))
		{
			$array[] = $row;
		}
		if (!empty($array))
		{
			return $array;
		}
		else
		{
			return NULL;
		}
	}
	
	function numRows($sql, $params=array())
	{
		global $db;		
		if (!empty($params)) {
			$sth = $db->prepare($sql);
			$sth->execute($params);
		} else {
			$sth = $db->prepare($sql);
			$sth->execute();
		}
		$count = $sth->fetchColumn();
		return $count;
	}
	
	function getTotal($sql, $sql_two)
	{
		global $db;
		$query = $db->query("SELECT sum($sql) as total_mark FROM $sql_two");
		$row = $query->fetch(); 
		$sum = $row['total_mark'];
		return $sum;
		$db = null;
		$row = null;
	}
	
	function fetchObject($sql, $name)
	{
		global $db;
		$first_step = $db->query($sql);
		$final_result = $first_step->fetch_object()->$name;
		return $final_result;
	}