<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ( isset($_SESSION["loggedUserID"]) ) {
  header("Location: index.php");
  die();
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>NetYeet Social Network - Login/Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.17/css/uikit.min.css" />
    <link rel="stylesheet" href="css/authenticate_design.css">
  </head>
  <body>

    <div id="site-bg"></div>

    <div id="site-wrapper" class="uk-flex uk-flex-column uk-flex-middle uk-flex-center uk-text-center uk-container">

      <div class="uk-card uk-card-default uk-card-large uk-card-body uk-width-1-2@m uk-width-1-2@s">

        <div id="login-section">
          <h3 class="uk-card-title">Prijava</h3>

          <!--<form>-->
            <div class="uk-margin">
              <input name="login-username" autocomplete="username" class="uk-input" type="text" placeholder="Uporabniško Ime" required>
            </div>

            <div class="uk-margin">
              <input name='login-password' autocomplete="current-password" class="uk-input" type="password" placeholder="Geslo" required>
            </div>

            <button name='login-submit' onclick="SubmitForm('login')" class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">Prijava</button>
          <!--</form>-->

          <p>Še nimate računa? <a onclick="SwitchSection('register')">Registriraj se!</a> </p>

          <p>Lahko tudi uporabite preizkusni račun:
            <br>
            Uporabniško ime: <span style="font-weight: bold">byicee</span>
            <br>
            Geslo: <span style="font-weight: bold">test1</span>
          </p>
        </div>

        <div id="register-section">
          <h3 class="uk-card-title">Registracija</h3>

          <!--<form>-->
            <div class="uk-margin">
              <input name='register-username' autocomplete="username" class="uk-input" type="text" placeholder="Uporabniško Ime" required>
            </div>

            <div class="uk-margin">
              <input name='register-email' autocomplete="email" class="uk-input" type="email" placeholder="Email" required>
            </div>

            <div class="uk-margin">
              <input name='register-password' autocomplete="new-password" class="uk-input" type="password" placeholder="Geslo" required>
            </div>

            <div class="uk-margin">
              <input name='register-confirmpassword' autocomplete="new-password" class="uk-input" type="password" placeholder="Ponovno vnesite geslo" required>
            </div>

            <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text">Datum rojstva</label>
              <div class="uk-form-controls">
                  <input name='register-date' class="uk-input" id="form-stacked-text" type="date">
              </div>
            </div>

            <div class="uk-margin">
              <div class="uk-margin uk-grid-small uk-child-width-auto uk-grid">
                <label><input class="uk-radio" type="radio" name="gender" value="man" checked /> Moški</label>
                <label><input class="uk-radio" type="radio" name="gender" value="woman" /> Ženska</label>
              </div>
            </div>

            <button name="register-submit" onclick="SubmitForm('register')" class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">Registriraj</button>
          <!--</form>-->

          <p>Že imate račun? <a onclick="SwitchSection('login')">Prijavite se!</a> </p>
        </div>

        <div id="message-section">

          <div class="uk-padding-small">
            <span id="message-section-icon" uk-icon="icon: mail; ratio: 2"></span>
          </div>

          <div class="uk-padding-small">
            <h3 class="uk-card-title" id="message-section-msg"></h3>
          </div>

          <ul class="uk-list" id="message-section-links">
            <li><a onclick="SwitchSection('login')">Prijava</a></li>
          </ul>

        </div>

      </div>
    </div>

    <!-- UIkit JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.17/js/uikit.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.17/js/uikit-icons.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/authenticate_script.js" charset="utf-8"></script>

  </body>
</html>
