<?php

session_start();

$user = 'user';
$pass = 'pass';
$db = new PDO( 'mysql:host=localhost;dbname=Dripp', $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4") );

?>
