<?php
require_once 'DBConnect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$AuthInfo = $_POST['authInfo'];

if ($AuthInfo === "anonymous_login") {
  $_SESSION["isLoggedInAnonymous"] = true;
  echo "SUCCESS";
} else {
  $AuthInfo = json_decode($AuthInfo, true);
  switch ($AuthInfo['authType']) {
    case 'login':
      ProcessLogin();
      break;
    case 'register':
      ProcessRegister();
      break;
    case 'forgotpasswordrequest':
      ProcessForgotPasswordRequest();
      break;
    case 'resendconfirmemail':
      PreSendConfirmationMail();
      break;
  }
}



function ProcessLogin()
{
  global $AuthInfo, $db;

  function CheckErrors()
  {
    global $AuthInfo;

    if (
      strlen($AuthInfo['username']) < 5
      || strlen($AuthInfo['password']) < 5
    ) {
      echo "ERR_InvalidForm";
      exit();
    }
  }
  CheckErrors();

  $sql = "SELECT ID, Active, Password FROM users WHERE Username=:username";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(":username", $AuthInfo['username']);
  $result = $stmt->execute();
  $row = $stmt->fetch();

  $CorrectPassword = $row['Password'];
  if (password_verify($AuthInfo['password'], $CorrectPassword)) {
    if ($row['Active'] == 0) {
      echo "ERR_ACC_NOTCONFIRMED";
      exit();
    } else {
      $_SESSION["loggedUserID"] = $row['ID'];
      unset($_SESSION["isLoggedInAnonymous"]);
      echo "SUCCESS";
    }
  } else {
    echo "ERR_INCORRECT";
    exit();
  }
}

function ProcessRegister()
{
  global $AuthInfo, $db;

  function CheckErrors()
  {
    global $AuthInfo, $db;
    $infoList = array(
      'username',
      'email',
      'password',
      'confirmPassword',
      'dateOfBirth'
    );
    foreach ($infoList as $infoName) {
      if (strlen($AuthInfo[$infoName]) < 5) {
        echo 'ERR_InvalidForm';
        exit();
      }
    }

    if ($AuthInfo['password'] != $AuthInfo['confirmPassword']) {
      echo 'ERR_PasswordNotMatch';
      exit();
    }

    $registerEmail = filter_var($AuthInfo['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($registerEmail, FILTER_VALIDATE_EMAIL)) {
      echo 'ERR_InvalidEmail';
      exit();
    }

    // DUPLICATE CHECK
    $sql = "SELECT COUNT(Username) AS usrnum FROM users WHERE Username = :username";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':username', $AuthInfo['username']);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['usrnum'] > 0) {
      echo 'ERR_DuplicateUsername';
      exit();
    }

    $sql = "SELECT COUNT(Email) AS emailnum FROM users WHERE Email = :email";
    $stmt = $db->prepare($sql);

    $stmt->bindValue(':email', $AuthInfo['email']);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['emailnum'] > 0) {
      echo 'ERR_DuplicateEmail';
      exit();
    }
  }
  CheckErrors();

  $passwordHash = password_hash($AuthInfo['password'], PASSWORD_BCRYPT, array("cost" => 12));
  $dateTime = new DateTime($AuthInfo['dateOfBirth']);
  $formattedDate = date_format($dateTime, 'Y-m-d');

  $sql = "INSERT INTO users (FirstName, LastName, Username, Email, Password, DOB, Gender) VALUES (:firstname, :lastname, :username, :email, :password, :dob, :gender)";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':firstname', $AuthInfo['firstname']);
  $stmt->bindValue(':lastname', $AuthInfo['lastname']);
  $stmt->bindValue(':username', $AuthInfo['username']);
  $stmt->bindValue(':email', $AuthInfo['email']);
  $stmt->bindValue(':password', $passwordHash);
  $stmt->bindValue(':dob', $formattedDate);
  $stmt->bindValue(':gender', $AuthInfo['gender']);

  $result = $stmt->execute();

  if ($result) {
    SendConfirmationMail($AuthInfo['email']);
    // echo "SUCCESS";
  } else {
    echo "ERR_INSERT";
  }
}

function PreSendConfirmationMail()
{

  global $AuthInfo, $db;

  $sql = "SELECT Active, Email FROM users WHERE Username=:username";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(":username", $AuthInfo['username']);
  $result = $stmt->execute();
  $row = $stmt->fetch();

  if ($row["Active"] == 0) {
    SendConfirmationMail($row["Email"]);
  } else {
    echo "ERR_AlreadyConfirmed";
  }
}

function SendConfirmationMail($email)
{
  global $db;

  $ConfirmationCode = md5($email . rand(0, 50));
  $sql = "UPDATE users SET ConfirmCode=:confirmcode WHERE Email=:email";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':confirmcode', $ConfirmationCode);
  $stmt->bindValue(':email', $email);
  $result = $stmt->execute();
  if ($result) {
    $to      = $email;
    $subject = 'NetYeet Account Confirmation';
    $message = "Please open the following link to confirm your email: \n"
      . "localhost/NetYeet/ConfirmEmail.php?cc=" . $ConfirmationCode
      . "\n\n\n If you didn't request this please ignore the message!";
    $headers = "From: icevx1@gmail.com";
    $result = mail($to, $subject, $message, $headers);

    echo "SUCCESS";
  }
}

function ProcessForgotPasswordRequest()
{
  global $db, $AuthInfo;

  // Check if Account is Activated
  $sql = "SELECT * FROM users WHERE Email=:email";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':email', $AuthInfo['email']);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo "ERR_NotFound";
    exit();
  }

  $ConfirmationCode = md5($AuthInfo["email"] . rand(0, 50));
  $sql = "UPDATE users SET ConfirmCode=:confirmcode WHERE Email=:email";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':confirmcode', $ConfirmationCode);
  $stmt->bindValue(':email', $AuthInfo['email']);
  $result = $stmt->execute();
  if ($result) {
    $to      = $AuthInfo['email'];
    $subject = 'NetYeet Password Reset';
    $message = "Please open the following link to reset your NetYeet password: \n"
      . "http://byicee.me/projects/NetYeet/ResetPassword.php?cc=" . $ConfirmationCode
      . "\n\n\n If you didn't request this please ignore the message!";
    $headers = "From: icevx1@gmail.com";
    $result = mail($to, $subject, $message, $headers);

    echo "SUCCESS";
  }
}
