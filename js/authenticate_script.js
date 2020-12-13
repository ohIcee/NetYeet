// // TODO:
// FINISH FORGOT PASSWORD EMAIL SEND AND RESETTING
// FINISH RESEND CONFIRMATION EMAIL

var activeSection = "login";

var loginSection = document.getElementById('login-section');
var registerSection = document.getElementById('register-section');
var messageSection = document.getElementById('message-section');

var loginUsernameInput = document.getElementsByName('login-username')[0];
var loginPasswordInput = document.getElementsByName('login-password')[0];
var loginSubmitButton = document.getElementsByName('login-submit')[0];

var registerUsernameInput = document.getElementsByName('register-username')[0];
var registerEmailInput = document.getElementsByName('register-email')[0];
var registerPasswordInput = document.getElementsByName('register-password')[0];
var registerConfirmPasswordInput = document.getElementsByName('register-confirmpassword')[0];
var registerDateInput = document.getElementsByName('register-date')[0];
var selectedGender = document.querySelector('input[name="gender"]:checked').value;
var registerSubmitButton = document.getElementsByName('register-submit')[0];

var messageSectionIcon = document.getElementById('message-section-icon');
var messageSectionMsg = document.getElementById('message-section-msg');
var messageSectionLinks = document.getElementById('message-section-links');

var LoginInfo;

function CreateInputEvents() {
  // On press enter submit form
  document.addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
      event.preventDefault();
      SubmitForm(activeSection);
    }
  });
}
CreateInputEvents();

function SendConfirmationMail() {

  let SendConfirmEmailInfo = new SendConfirmationEmailFormInfo(LoginInfo["username"]);
  let JSONSendConfirmEmailInfo = JSON.stringify(SendConfirmEmailInfo);

  $.ajax({
    type: 'POST',
    url: 'php/SubmitAuthenticate.php',
    data: {
      authInfo: JSONSendConfirmEmailInfo
    },
    success: function(data) {

      console.log(data);

      if (data == "ERR_AlreadyConfirmed") {
        console.log("Already Confirmed!");
      }
      if (data == "SUCCESS") {
        SwitchSection('message');
        messageSectionIcon.setAttribute('uk-icon', 'mail');
        messageSectionMsg.innerHTML = 'A confirmation email has been sent. Please confirm your account to login.';
        messageSectionLinks.innerHTML = "<li><a onclick='SwitchSection(\"login\")'>Login</a><li>";
      }
    }
  });

}

// Submits Register/Login Form
// Checks for errors in JS then PHP
function SubmitForm(authType) {
  switch (authType) {
    case 'login':
      ProcessLogin();
      break;
    case 'register':
      ProcessRegister();
      break;
    case 'forgotpassword':
      ProcessForgotPasswordRequest("SwitchTo");
      break;
  }

  function ProcessLogin() {

    loginSubmitButton.disabled = true;

    var Username = loginUsernameInput.value;
    var Password = loginPasswordInput.value;

    if (!CheckInputs()) {
      console.log('Invalid Form!');
      loginSubmitButton.disabled = false;
      return;
    }

    LoginInfo = new LoginFormInfo(Username, Password);
    let JSONLoginInfo = JSON.stringify(LoginInfo);

    $.ajax({
      type: 'POST',
      url: 'php/SubmitAuthenticate.php',
      data: {
        authInfo: JSONLoginInfo
      },
      success: function(data) {
        loginSubmitButton.disabled = false;

        console.log(data);

        if (data == 'ERR_INCORRECT') {

          var msg = "Invalid Username or Password! Please check your inputs and try again.";
          var links = ""
          + "<li>"
            + "<a onclick='SwitchSection(\"login\", false)'>Login</a>"
          + "</li>"
          + "<li>"
            + "<li><a onclick='SubmitForm(\"forgotpassword\")'>Forgot Password</a></li>"
          + "</li>"

          SwitchSection('message', false);
          messageSectionIcon.setAttribute('uk-icon', 'ban');
          messageSectionMsg.innerHTML = msg;
          messageSectionLinks.innerHTML = links;
        }

        if (data == "ERR_ACC_NOTCONFIRMED") {
          var msg = "Account is not confirmed!";
          var links = ""
          + "<li><button class='uk-button uk-button-primary' onclick='SendConfirmationMail()'>Resend Confirmation Email</button></li>"
          + "<li><a onclick='SwitchSection(\"login\", true)'>Login</a><li>";

          SwitchSection('message', true);
          messageSectionIcon.setAttribute('uk-icon', 'warning');
          messageSectionMsg.innerHTML = msg;
          messageSectionLinks.innerHTML = links;
        }

        if (data == "SUCCESS") {
          window.location.href = "Authenticate.php";
        }

        console.log(data);
      }
    });

    function CheckInputs() {
      loginUsernameInput.style.borderColor = '#e5e5e5';
      loginPasswordInput.style.borderColor = '#e5e5e5';

      if (Username.length < 5) {
        loginUsernameInput.style.borderColor = 'red';
        return false;
      }

      if (Password.length < 5) {
        loginPasswordInput.style.borderColor = 'red';
        return false;
      }

      return true;
    }
  }

  function ProcessRegister() {
    registerSubmitButton.disabled = true;
    registerSubmitButton.innerHTML = '<div uk-spinner></div>';

    selectedGender = document.querySelector('input[name="gender"]:checked').value;
    var Username = registerUsernameInput.value;
    var Email = registerEmailInput.value;
    var Password = registerPasswordInput.value;
    var ConfirmPassword = registerConfirmPasswordInput.value;
    var date = registerDateInput.value;

    if (!CheckInputs()) {
      console.log('Invalid Form!');
      registerSubmitButton.innerHTML = 'Register';
      registerSubmitButton.disabled = false;
      return;
    }

    let RegisterInfo = new RegisterFormInfo(Username, Email, Password, ConfirmPassword, date, selectedGender);
    RegisterInfo = JSON.stringify(RegisterInfo);

    $.ajax({
      type: 'POST',
      url: 'php/SubmitAuthenticate.php',
      async: true,
      data: {
        authInfo: RegisterInfo
      },
      success: function(data) {
        registerSubmitButton.disabled = false;

        if (data == 'ERR_DuplicateUsername') {
          SwitchSection('message');
          messageSectionIcon.setAttribute('uk-icon', 'info');
          messageSectionMsg.innerHTML = 'This username already exists.';
          messageSectionLinks.innerHTML = "<li><a onclick='SwitchSection(\"register\")'>Register</a><li>";
        }

        if (data == 'ERR_DuplicateEmail') {
          SwitchSection('message');
          messageSectionIcon.setAttribute('uk-icon', 'info');
          messageSectionMsg.innerHTML = 'This E-Mail already exists.';
          messageSectionLinks.innerHTML = "<li><a onclick='SwitchSection(\"register\")'>Register</a><li>";
        }

        console.log(data);
        if (data == 'SUCCESS') {
          SwitchSection('message');
          messageSectionIcon.setAttribute('uk-icon', 'mail');
          messageSectionMsg.innerHTML = 'A confirmation email has been sent. Please confirm your account to login.';
          messageSectionLinks.innerHTML = "<li><a onclick='SwitchSection(\"login\")'>Login</a><li>";
        }
      }
    });

    function CheckInputs() {

      registerUsernameInput.style.borderColor = '#e5e5e5';
      registerEmailInput.style.borderColor = '#e5e5e5';
      registerPasswordInput.style.borderColor = '#e5e5e5';
      registerConfirmPasswordInput.style.borderColor = '#e5e5e5';
      registerDateInput.style.borderColor = '#e5e5e5';

      if (Username.length < 5) {
        registerUsernameInput.style.borderColor = 'red';
        return false;
      }

      if (Email.length < 5) {
        registerEmailInput.style.borderColor = 'red';
        return false;
      }

      if (Password.length < 5) {
        registerPasswordInput.style.borderColor = 'red';
        return false;
      }

      if (ConfirmPassword != Password) {
        registerConfirmPasswordInput.style.borderColor = 'red';
        return false;
      }

      return true;

    }

    registerSubmitButton.innerHTML = 'Registriraj';
    registerSubmitButton.disabled = false;
  }

}

function ProcessForgotPasswordRequest(action) {

  if (action == "SwitchTo") {
    SwitchTo();
  } else if (action == "Process") {
    Process();
  }


  function SwitchTo() {
    var msg = 'Please enter the email of the account you want to reset password for';
    var links = ""
    + "<li><input name='forgotpasswordrequest-email' class='uk-input' type='text' placeholder='Email'></li>"
    + "<li><button name='forgotpasswordrequest-submit' onclick='ProcessForgotPasswordRequest(\"Process\")' class='uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom'>Send</button></li>"
    + "<a onclick='SwitchSection(\"login\", true)'>Cancel</a>";

    SwitchSection('message', true);
    messageSectionIcon.setAttribute('uk-icon', 'question');
    messageSectionMsg.innerHTML = msg;
    messageSectionLinks.innerHTML = links;
  }

  function Process() {
    var forgotPasswordEmailInput = document.getElementsByName('forgotpasswordrequest-email')[0];

    let forgotPasswordRequestInfo = new ForgotPasswordRequestInfo(forgotPasswordEmailInput.value);
    let JSONForgotPasswordRequestInfo = JSON.stringify(forgotPasswordRequestInfo);

    $.ajax({
      type: 'POST',
      url: 'php/SubmitAuthenticate.php',
      data: {
        authInfo: JSONForgotPasswordRequestInfo
      },
      success: function(data) {

        console.log(data);

        if (data == "ERR_NotFound") {
          console.log("Account does not exist!");
        }
        if (data == "SUCCESS") {
          SwitchSection('message');
          messageSectionIcon.setAttribute('uk-icon', 'mail');
          messageSectionMsg.innerHTML = 'A password reset request has been sent';
          messageSectionLinks.innerHTML = "<li><a onclick='SwitchSection(\"login\")'>Login</a><li>";
        }

      }
    });

  }

}

function ProcessAfterEmailConfirm(success) {

  var msg = "";
  var links = "";

  if (success) {
    messageSectionIcon.setAttribute('uk-icon', 'check');
    msg = 'Account successfully confirmed. You can log in now.';
    links = ""
    + "<li><a onclick='SwitchSection(\"login\")'>Login</a><li>";
  } else {
    messageSectionIcon.setAttribute('uk-icon', 'close');
    msg = 'Failed to verify account. Please try again.';
    links = ""
    + "<li><a onclick='SwitchSection(\"confirmemail\")'>Confirm Email</a><li>"
    + "<li><a onclick='SwitchSection(\"login\")'>Login</a><li>";
  }

  SwitchSection('message', true);
  messageSectionMsg.innerHTML = msg;
  messageSectionLinks.innerHTML = links;

}

// Gets Hash from URL to automatically
// switch to Register/Login section
var urlHash = window.location.hash;
ProcessHash();
function ProcessHash() {
  history.replaceState(null, null, ' ');
  switch (urlHash) {
    case '#login':
    case '':
      activeSection = 'login';
      SwitchSection('login', true);
      return;
    case '#register':
      activeSection = 'register';
      SwitchSection('register', true);
      return;
    case '#forgotpassword':
      ProcessForgotPasswordRequest("SwitchTo");
      return;
    case '#emailconfirmfail':
      ProcessAfterEmailConfirm(false);
      return;
    case '#emailconfirmsuccess':
      ProcessAfterEmailConfirm(true);
      return;
    case '#passwordresetsuccess':
      // TODO ProcessAfterPasswordReset(true);
      return;
    case '#passwordresetfail':
      // TODO ProcessAfterPasswordReset(false);
      return;
  }
}

// Switches section to Login/Register
function SwitchSection(section, clearInputs) {

  if (clearInputs)
    ClearInputs();

  loginSection.classList.remove("uk-animation-slide-bottom-small");
  loginSection.style.display = "none";
  registerSection.classList.remove("uk-animation-slide-bottom-small");
  registerSection.style.display = "none";
  messageSection.classList.remove("uk-animation-slide-bottom-small");
  messageSection.style.display = "none";

  if (section == 'login') {
    activeSection = 'login';
    loginSection.style.display = "block";
    loginSection.classList.add("uk-animation-slide-bottom-small");
  } else if (section == 'register') {
    activeSection = 'register';
    registerSection.style.display = "block";
    registerSection.classList.add("uk-animation-slide-bottom-small");
  } else if (section == 'message') {
    messageSection.style.display = "block";
    messageSection.classList.add("uk-animation-slide-bottom-small");
  }

  function ClearInputs() {
    loginUsernameInput.value = '';
    loginPasswordInput.value = '';
    loginSubmitButton.value = '';
    registerUsernameInput.value = '';
    registerEmailInput.value = '';
    registerPasswordInput.value = '';
    registerConfirmPasswordInput.value = '';
    registerDateInput.value = '';
  }

}

class ForgotPasswordRequestInfo {
  constructor(email) {
    this.email = email;
    this.authType = 'forgotpasswordrequest';
  }
}

class SendConfirmationEmailFormInfo {
  constructor(username) {
    this.username = username;
    this.authType = 'resendconfirmemail';
  }
}

class LoginFormInfo {
  constructor(user, pass) {
    this.username = user;
    this.password = pass;
    this.authType = 'login';
  }
}

class RegisterFormInfo {
  constructor(user, email, pass, confirmpass, dob, gender) {
    this.username = user;
    this.email = email;
    this.password = pass;
    this.confirmPassword = confirmpass;
    this.dateOfBirth = dob;
    this.gender = gender;
    this.authType = 'register';
  }
}
