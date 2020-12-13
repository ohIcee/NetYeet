<?php

require_once 'DBConnect.php';

$sql = "SELECT Username FROM users WHERE ID=:id";
$stmt = $db->prepare($sql);
$stmt->bindValue(":id", $_SESSION['loggedUserID']);
$result = $stmt->execute();
$row = $stmt->fetch();

$_SESSION['loggedUsername'] = $row['Username'];

?>
