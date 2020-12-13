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

if (isset($_POST['submit'])) {

  $NewPass = $_POST['pass'];
  $ConfirmNewPass = $_POST['repass'];

  if (strlen($NewPass) >= 5) {
    if ($NewPass == $ConfirmNewPass) {

      $hashedPass = password_hash($NewPass, PASSWORD_BCRYPT, array("cost" => 12));

      $sql = "UPDATE users SET password=:newpass WHERE ConfirmCode=:confirmcode";
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':newpass', $hashedPass);
      $stmt->bindValue(':confirmcode', $_GET['cc']);
      $result = $stmt->execute();

      ClearConfirmCode();

      if ($result) {
        header("Location: Authenticate.php#passwordresetsuccess");
      } else {
        header("Location: Authenticate.php#passwordresetfail");
      }

    } else {
      echo "Passwords don't match!";
    }
  } else {
    echo "Password must be minimum 5 characters long!";
  }

}

?>

<form method="post">
  <input type="text" name="pass" value="" placeholder="Enter New Password">
  <input type="text" name="repass" value="" placeholder="Reenter New Password">
  <input type="submit" name="submit" value="Set Password">
</form>
