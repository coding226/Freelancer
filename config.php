<?php


$host = "localhost";
$db = "khbaqstr_hytale";
$user = "khbaqstr_lishan";
$pass = "Lishan133";
$db = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8mb4', $user, $pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);