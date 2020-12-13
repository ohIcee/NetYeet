<?php

if (!isset($_GET['cc'])) {
  echo "Invalid Key";
  exit();
}

require_once 'php/DBConnect.php';

function CheckConfirmCode() {
  global $db;

  $sql = "SELECT ConfirmCode FROM users WHERE ConfirmCode=:confirmcode";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':confirmcode', $_GET['cc']);
  $result = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($row['ConfirmCode'] == $_GET['cc']) {
    return true;
  } else {
    return false;
  }

}
if (!CheckConfirmCode()) {
  echo "Invalid Key!";
  exit();
}

function ClearConfirmCode() {
  global $db;

  $sql = "UPDATE users SET ConfirmCode=:empty WHERE ConfirmCode=:confirmcode";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':confirmcode', $_GET['cc']);
  $stmt->bindValue(':empty', null, PDO::PARAM_INT);
  $result = $stmt->execute();
}

$sql = "SELECT Active FROM users WHERE ConfirmCode=:confirmcode";
$stmt = $db->prepare($sql);
$stmt->bindValue(":confirmcode", $_GET['cc']);
$result = $stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row['Active'] == 1) {
  header("Location: Authenticate.php");
  exit();
}

if ($row) {

  $sql = "UPDATE users SET Active=1 WHERE ConfirmCode=:confirmcode";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':confirmcode', $_GET['cc']);
  $result = $stmt->execute();

  ClearConfirmCode();

  if ($result) {
    header("Location: Authenticate.php#emailconfirmsuccess");
  } else {
    header("Location: Authenticate.php#emailconfirmfail");
    echo "Account Confirmation Failed! (DB)";
  }

} else {
  echo "Invalid Key";
}

?>
