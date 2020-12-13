<?php
session_start();

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

if ( !isset($_SESSION["loggedUserID"]) ) {
  header("Location: Authenticate.php");
  die();
}

if ($_GET['signout'] == true) {
  unset($_SESSION['loggedUserID']);
  header("Location: index.php");
}

require_once 'php/DBConnect.php';
require_once 'php/GetLoggedUserInfo.php';
require_once 'php/HelperFunctions.php';
$ProfileInfo = GetProfileInfo($_SESSION['loggedUserID']);

if (isset($_POST['Cancel'])) {
  header("Location: index.php");
}

if (isset($_POST['SaveChanges'])) {

  $ImageName = "";
  $folder = "UserImageUploads/";

  if (!empty($_FILES['ChangeProfileImage']['tmp_name'])) {

    $sql = "SELECT ProfilePictureName FROM users WHERE ID=:userid";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':userid', $_SESSION['loggedUserID']);
    $stmt->execute();
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rows['ProfilePictureName'] != 'default_profile_picture.jpg') {
      unlink($folder . $rows['ProfilePictureName']);
    }

    // Create Appropriate Unique File Name for Image
    $FileNameParts = explode(".", $_FILES["ChangeProfileImage"]["name"]);
    $NewFileName = $FileNameParts[0] . "-" . date('Ymd') . "-" . md5($_SESSION["loggedUserID"] + rand(1, 15)) . ".jpg";
    $ImageName = $NewFileName;

    // Compress and upload Image
    CompressImage($_FILES['ChangeProfileImage']['tmp_name'], "$folder".$NewFileName, 50);
  } else {
    $ImageName = $ProfileInfo['ProfilePictureName'];
  }

  $BIO = $_POST['bio'];
  $ShowEmail = $_POST['ShowEmail'] == 'on' ? '1' : '0';
  $Location = $_POST['Location'];
  $ShowLocation = $_POST['ShowLocation'] == 'on' ? '1' : '0';
  $Website = $_POST['Website'];
  $ShowWebsite = $_POST['ShowWebsite'] == 'on' ? '1' : '0';
  $Gender = $_POST['Gender'] == '0' ? 'man' : 'woman';
  $ShowGender = $_POST['ShowGender'] == 'on' ? '1' : '0';
  $DOB = $_POST['DOB'];
  $ShowDOB = $_POST['ShowDOB'] == 'on' ? '1' : '0';

  $dateTime = new DateTime($DOB);
  $formattedDate = date_format($dateTime, 'Y-m-d');

  $sql = "UPDATE users SET Bio=:bio, ShowEmail=:showEmail, Location=:location, ShowLocation=:showLocation, Website=:website, ShowWebsite=:showWebsite, Gender=:gender, ShowGender=:showGender, DOB=:dob, ShowDOB=:showDOB, ProfilePictureName=:ppcname WHERE ID=:userid";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':bio', $BIO);
  $stmt->bindValue(':showEmail', $ShowEmail);
  $stmt->bindValue(':location', $Location);
  $stmt->bindValue(':showLocation', $ShowLocation);
  $stmt->bindValue(':website', $Website);
  $stmt->bindValue(':showWebsite', $ShowWebsite);
  $stmt->bindValue(':gender', $Gender);
  $stmt->bindValue(':showGender', $ShowGender);
  $stmt->bindValue(':dob', $formattedDate);
  $stmt->bindValue(':showDOB', $ShowDOB);
  $stmt->bindValue(':ppcname', $ImageName);
  $stmt->bindValue(':userid', $_SESSION['loggedUserID']);
  $result = $stmt->execute();
  if ($result) {
    echo "Updated Profile!";
  } else {
    echo "Failed to Update info!";
    // Remove image if post upload into DB Fails
    unlink($folder . $NewFileName);
  }

  // Prevent Form Resubmittion
  header("Location: " . $_SERVER['REQUEST_URI']);
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>NetYeet Social Network</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="css/ViewProfile_design.css">
    <link rel="stylesheet" href="css/TopButton.css">
  </head>
  <body>

    <!-- < Nav Bar > -->
    <div class="ui inverted segment">
      <div class="ui container">
        <div class="ui stackable inverted secondary menu">
          <a href='index.php' class="active item">Home</a>
          <div class="right menu">
            <div class="item">
              <div class="ui search">
                <div class="ui inverted transparent icon input">
                  <input class="prompt" id="TopSearchInput" type="text" placeholder="Search...">
                  <i class="search icon"></i>
                </div>
                <div class="results"></div>
              </div>
            </div>
            <div class="ui dropdown icon item">
              <div href="item">
                <i class="user icon"></i> <?php echo strtoupper($_SESSION['loggedUsername']); ?>
              </div>
              <div class="menu">
                <div style="cursor: pointer" onclick="window.location.href='ViewProfile.php?id=<?php echo $_SESSION['loggedUserID']; ?>'" class="item">View Profile</div>
                <div style="cursor: pointer" onclick="window.location.href='EditProfile.php'" class="item">Profile Settings</div>
                <div class="divider"></div>
                <a href="?signout=true" class="item">Log Out</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- </ Nav Bar > -->

    <div class="ui container">

      <h1>Edit Profile</h1>
      <form action="EditProfile.php" method="post" enctype="multipart/form-data">
        <div id="loader" class="ui loading basic segment">
          <div class="ui stackable grid">
            <div class="four wide column">
              <h3>Image</h3>
              <img id="ProfilePictureImagePreview" class="ui medium centered image" src="<?php echo 'UserImageUploads/'.$ProfileInfo['ProfilePictureName']; ?>">
              <div class="ui bottom attached">
                <div class="column">
                  <label for="ChangeProfileImageInput" class="ui secondary fluid icon button">
                    <i class="image icon"></i>
                    Change Profile Image</label>
                  <input type="file" accept="image/*" id="ChangeProfileImageInput" style="display:none" name="ChangeProfileImage">
                </div>
              </div>
              <div style="display: none" id="ImageSizeError" class="ui negative message">
                <div class="header">
                  Image too big to upload
                </div>
                <p>Maximum Image Size is 2MB</p>
              </div>
            </div>
            <div class="twelve wide column">
              <div class="ui form">
                <h3>Bio</h3>
                <div class="field">
                  <textarea id="bio" name="bio"><?php echo $ProfileInfo['Bio']; ?></textarea>
                  <p> <span style="font-weight: bold" id="avalBioCharCount">250</span> / <span id="maxBioCharCount">250</span> </p>
                </div>

                <div class="ui two column">
                  <div class="ui segment">
                    <div id="EmailInputField" class="disabled field">
                      <label>E-mail</label>
                      <input tabindex="-1" type="email" value="<?php echo $ProfileInfo['Email']; ?>" placeholder="joe@schmoe.com">
                    </div>
                    <div class="field">
                      <div id="ShowEmailCheckbox" class="ui toggle checkbox">
                        <input type="checkbox" tabindex="0" name="ShowEmail" class="hidden">
                        <label>Show Email on profile</label>
                      </div>
                    </div>
                  </div>

                  <div class="ui segment">
                    <div id="LocationInputField" class="field">
                      <label>Location</label>
                      <input name="Location" type="text" value="<?php echo $ProfileInfo['Location']; ?>" placeholder="United Kingdom">
                    </div>
                    <div class="field">
                      <div id="ShowLocationCheckbox" class="ui toggle checkbox">
                        <input type="checkbox" name="ShowLocation" tabindex="0" class="hidden">
                        <label>Show Location on profile</label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="ui segment">
                  <div id="WebsiteInputField" class="field">
                    <label>Website</label>
                    <input name="Website" type="text" value="<?php echo $ProfileInfo['Website']; ?>" placeholder="www.byicee.me/">
                  </div>
                  <div class="field">
                    <div id="ShowWebsiteCheckbox" class="ui toggle checkbox">
                      <input type="checkbox" name="ShowWebsite" tabindex="0" class="hidden">
                      <label>Show Website on profile</label>
                    </div>
                  </div>
                </div>

                <div class="ui segment">
                  <div id="GenderInputField" class="field">
                    <label>Gender</label>
                    <div class="ui selection dropdown">
                      <input type="hidden" name="Gender">
                      <i class="dropdown icon"></i>
                      <div class="default text">Gender</div>
                      <div class="menu">
                        <div class="item" value="man" data-value="0">
                          <i class="male icon"></i>
                          Male
                        </div>
                        <div class="item" value="woman" data-value="1">
                          <i class="female icon"></i>
                          Female
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="field">
                    <div id="ShowGenderCheckbox" class="ui toggle checkbox">
                      <input type="checkbox" name="ShowGender" tabindex="0" class="hidden">
                      <label>Show Gender on profile</label>
                    </div>
                  </div>
                </div>

                <div class="ui segment">
                  <div id="DOBInputField" class="field">
                    <label>Date of Birth</label>
                    <input type="date" name="DOB" value="<?php echo $ProfileInfo['DOB']; ?>">
                  </div>
                  <div class="field">
                    <div id="ShowDOBCheckbox" class="ui toggle checkbox">
                      <input type="checkbox" name="ShowDOB" tabindex="0" class="hidden">
                      <label>Show Date of Birth on profile</label>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <div class="ui divider"></div>

        <button type="submit" name="SaveChanges" class="ui primary button">Save Changes</button>
        <button type="submit" name="Cancel" class="ui secondary button">Cancel</button>

        <div class="ui hidden divider"></div>
      </form>

    </div>

    <button onclick="ScrollToTop()" id="ToTopButton" class="ui secondary big icon button">
      <i class="angle up icon"></i>
    </button>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    <script src="js/jquery.form.min.js" charset="utf-8"></script>
    <script src="js/SearchBarScript.js" charset="utf-8"></script>
    <script src="js/EditProfile.js" charset="utf-8"></script>
    <script type="text/javascript">
      $("#GenderInputField .ui.dropdown").dropdown('set selected', '<?php echo $ProfileInfo['Gender'] == 'man' ? "0" : "1"; ?>');
      $("#GenderInputField .ui.dropdown").dropdown();
      $('.ui.dropdown').dropdown();
    </script>

  </body>
</html>
