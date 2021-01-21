<?php
if(!defined('TOP_STARTED')) exit('Site security activated !');
    function writeSession($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    function readSession($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
		} else {
            return null;
        }
    }
    function removeSession($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
	function uniSession($key)
	{
		if (isset($_SESSION[$key])) 
		{
			echo $_SESSION[$key];
			unset($_SESSION[$key]);
		}
		else
		{
			return null;
		}
	}
?>