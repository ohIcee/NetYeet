<?php

if (!session_start()) {
    session_start();
}

$user = 'root';
$pass = '';
$db = new PDO('mysql:host=localhost;dbname=NetYeet', $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
