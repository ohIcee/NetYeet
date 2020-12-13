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

if (isset($_GET['id']) && isset($_GET['ToEditProfile'])) {
  header("Location: EditProfile.php?id=" . $_GET['id']);
}

if ($_GET['signout'] == true) {
  unset($_SESSION['loggedUserID']);
  header("Location: index.php");
}

if (!isset($_GET['id'])) {
  header("Location: index.php");
}

require_once 'php/DBConnect.php';
require_once 'php/GetLoggedUserInfo.php';
require_once 'php/HelperFunctions.php';

$ProfileIDToShow = $_GET['id'];

if (isset($_GET['deletepost'])) {
  DeletePost($_GET['deletepost']);
}

if (isset($_GET['follow'])) {
  FollowUser($_SESSION['loggedUserID'], $_GET['id']);
}

if (isset($_GET['unfollow'])) {
  UnfollowUser($_SESSION['loggedUserID'], $_GET['id']);
}

$ProfileInfo = GetProfileInfo($ProfileIDToShow);
// $ProfileInfo["Content"]; -> Post Content etc.

$AllowProfileEdit = $_GET['id'] == $_SESSION['loggedUserID'] ? true : false;

$ProfileData = GetUserInfo($_GET['id']);
$UserPreferences = GetProfilePreferences($_GET['id']);
$Posts = GetUserPosts($_GET['id']);
$LikedPosts = GetUserLikedPosts($_SESSION['loggedUserID']);

// $Posts[0][11]['Content'];
//       user;post;column

?>

<script type="text/javascript">
  var loggedUserID = <?php echo $_SESSION['loggedUserID']; ?>;
  var profileID = <?php echo $_GET['id']; ?>;
</script>

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
          <a href='index.php' class="item">Home</a>
          <div class="right menu">
            <div class="item">
              <div class="ui search">
                <div class="ui inverted transparent icon input">
                  <input class="prompt" id="TopSearchInput" type="text" placeholder="Search...">
                  <i class="search link icon"></i>
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
      <div class="ui grid">

        <div class="four wide column">
          <div class="ui card">
            <div class="compact image">
              <img src="<?php echo 'UserImageUploads/'.$ProfileInfo['ProfilePictureName']; ?>">
            </div>
          </div>
        </div>

        <div class="twelve wide column">

          <h1 class="ui header">
            <?php echo $ProfileInfo["Username"]; ?>
            <div id="UserBio" class="sub header">
              <?php echo !empty($ProfileInfo['Bio'])  ?  $ProfileInfo['Bio'] : 'No Bio.' ?>
            </div>
          </h1>

          <div class="ui middle aligned list">

            <?php

            if ($UserPreferences['ShowEmail'] == '1' && !empty($ProfileInfo['Email'])) {
               echo '
               <div class="item">
                 <i class="large mail icon"></i>
                 <div class="content">
                   <a href="mailto:'.$ProfileInfo["Email"].'">' . $ProfileInfo["Email"] . '</a>
                 </div>
               </div>
               ';
            }

            if ($UserPreferences['ShowLocation'] == '1' && !empty($ProfileInfo['Location'])) {
               echo '
               <div class="item">
                 <i class="large marker icon"></i>
                 <div class="content">
                   '. $ProfileInfo['Location'] .'
                 </div>
               </div>
               ';
            }

            if ($UserPreferences['ShowWebsite'] == '1' && !empty($ProfileInfo['Website'])) {
              echo '
              <div class="item">
                <i class="large linkify icon"></i>
                <a href="https://'.$ProfileInfo['Website'].'" class="content">
                  '. $ProfileInfo['Website'] .'
                </a>
              </div>
              ';
            }

            if ($UserPreferences['ShowDOB'] == '1') {
              echo '
              <div class="item">
                <i class="large birthday cake icon"></i>
                <div class="content">
                  '. $ProfileInfo['DOB'] .'
                </div>
              </div>
              ';
            }

            if ($UserPreferences['ShowGender'] == '1') {
              $Gender = $UserPreferences["Gender"] == '1' ? 'female' : 'male';
              echo '
              <div class="item">
                <i class="large '. $Gender .' icon"></i>
                <div class="content">
                  '. ucfirst($Gender) .'
                </div>
              </div>
              ';
            }

            if ($AllowProfileEdit) {
              echo '
              <div class="ui divider"></div>
              <button onclick="window.location.href=\'EditProfile.php\'" class="ui primary basic button">Edit Profile Info</button>
              ';
            }

            ?>

          </div>
        </div>

        <?php
        // Follow Button Logic
        $FollowSection = "";
        if ($AllowProfileEdit) {
          $FollowSection = '<div id="followersButton" class="ui loading disabled left labeled button" tabindex="0">
            <a onclick="ShowFollowers()" class="ui basic label">
              '.$ProfileData[1]["Followers"].' Followers
            </a>
          </div>';
        } else {
          if ($ProfileData[3]['isFollowing'] == 0) {
            $FollowSection = '<div id="followersButton" class="ui loading disabled left labeled button" tabindex="0">
              <a onclick="ShowFollowers()" class="ui basic right pointing label">
                '.$ProfileData[1]["Followers"].' Followers
              </a>
              <div onclick="FollowUser('.$_GET["id"].')" id="FollowUserButton" class="ui positive button">
                <i class="plus icon"></i> Follow
              </div>
            </div>';
          } else {
            $FollowSection = '<div id="followersButton" class="ui loading disabled left labeled button" tabindex="0">
              <a onclick="ShowFollowers()" class="ui basic right pointing label">
                '.$ProfileData[1]["Followers"].' Followers
              </a>
              <div onclick="UnfollowUser('.$_GET["id"].')" id="UnfollowUserButton" class="ui negative button">
                <i class="minus icon"></i> Unfollow
              </div>
            </div>';
          }
        }

        ?>

        <div class="ui raised segment">

          <?php echo $FollowSection; ?>

          <button id="followingButton" onclick="ShowFollowingUsers()" class="ui loading basic button">
            <?php echo $ProfileData[0]['FollowingCount']; ?> Following
          </button>

          <button id="likedPostsButton" class="ui basic button">
            <?php echo $ProfileData[2]['LikedPosts']; ?> Liked Posts
          </button>
        </div>
      </div>


      <div class="ui hidden divider"></div>

      <label class="ui large header">Posts</label>
      <div class="ui hidden divider"></div>

      <div class="ui large feed">

        <?php
        foreach ($Posts as $key => $value):

          $bottomAttached = '';
          if ($AllowProfileEdit) {
            $bottomAttached = '
            <a onclick="ShowConfirmRemovePost('.$_SESSION['loggedUserID'].', '.$value['ID'].')" class="remove">
              <i class="minus icon"></i> Remove Post
            </a>
            ';
          }

          $IsPostLiked = '';
          foreach ($LikedPosts as $likekey => $likevalue) {
            if ($likevalue['PostID'] == $value['ID']) {
              $IsPostLiked = 'active';
            }
          }

          $PostLikes = GetPostLikes($value['ID']);
          $PostComments = GetPostCommentCount($value['ID']);

          $PostPublishDate = $value['PublishDate'];
          $CurrentDate = date('Y-m-d h:i:s', time());
          $CurrentDateTime = new DateTime($CurrentDate);

          $interval = date_diff($PostPublishDate, $CurrentDateTime);
          echo $interval;

          if ($value['Type'] == '0') {
            // Non-Image Post
            echo '
            <div class="event">
              <div class="label">
                <img src="UserImageUploads/'.$ProfileInfo['ProfilePictureName'].'">
              </div>
              <div class="content">
                <div class="summary">
                  <p class="user">
                    '.ucfirst($value['Username']).'
                  </p>
                  <div class="date">
                    '.$value['PublishDate'].'
                  </div>
                  <div class="extra text">
                    '.$value['Content'].'
                  </div>
                </div>
                <div class="meta">
                  <a onclick="ToggleLike(this, '.$value['ID'].')" class="'.$IsPostLiked.' like">
                    <i class="like icon"></i> <span class="likecount">'.$PostLikes['likeCount'].'</span> Likes
                  </a>
                  <a onclick="OpenPostComments('.$value['ID'].')" class="comment">
                    <i class="comment icon"></i> <span class="commentcount">'.$PostComments['commentCount'].'</span> Comments
                  </a>
                  '.$bottomAttached.'
                </div>
              </div>
            </div>
            ';

          } else {
            // Image Post
            echo '
            <div class="event">
              <div class="label">
                <img src="UserImageUploads/'.$ProfileInfo['ProfilePictureName'].'">
              </div>
              <div class="content">
                <div class="summary">
                  <p class="user">
                    '.ucfirst($value['Username']).'
                  </p>
                  <div class="date">
                    '.$value['PublishDate'].'
                  </div>
                  <div class="extra text">
                    <img style="max-width: 100%; height: auto" src="UserImageUploads/'.$value['ImageName'].'" />
                    <br>'.$value['Content'].'
                  </div>
                </div>
                <div class="meta">
                  <a onclick="ToggleLike(this, '.$value['ID'].')" class="'.$IsPostLiked.' like">
                    <i class="like icon"></i> <span class="likecount">'.$PostLikes['likeCount'].'</span> Likes
                  </a>
                  <a onclick="OpenPostComments('.$value['ID'].')" class="comment">
                    <i class="comment icon"></i> <span class="commentcount">'.$PostComments['commentCount'].'</span> Comments
                  </a>
                  '.$bottomAttached.'
                </div>
              </div>
            </div>
            ';

          }

          echo '<div class="ui section divider"></div>';

        endforeach;
        ?>

      </div>
    </div>

    <div id="FollowersModal" class="ui tiny longer modal">
      <div class="header">Followers</div>
      <div class="scrolling content">
        <div id="FollowersList" class="ui middle aligned selection list"></div>
      </div>
      <div class="actions">
        <div class="ui cancel button">Close</div>
      </div>
    </div>

    <div id="FollowingModal" class="ui tiny longer modal">
      <div class="header">Following</div>
      <div class="scrolling content">
        <div id="FollowingUsersList" class="ui middle aligned selection list"></div>
      </div>
      <div class="actions">
        <div class="ui cancel button">Close</div>
      </div>
    </div>

    <button onclick="ScrollToTop()" id="ToTopButton" class="ui secondary big icon button">
      <i class="angle up icon"></i>
    </button>

    <div id="PostComments" class="ui modal">
      <div class="header">Comments</div>
      <div class="scrolling content">
        <div id="CommentsModalSegment" class="ui basic segment">

          <div id="CommentsArea" class="ui comments"></div>

          <form class="ui reply form">
            <input type="text" style="position: fixed; left: -10000000px;" disabled/>
            <div class="field">
              <textarea id="CommentTextArea"></textarea>
            </div>
            <div onclick="PostComment()" class="ui blue labeled submit icon button">
              <i class="icon edit"></i> Add Reply
            </div>
          </form>
        </div>
      </div>
      <div class="actions">
        <div class="ui cancel button">Close</div>
      </div>
    </div>

    <div id="PostRemovalConfirmModal" class="ui mini modal">
      <div class="ui icon header">
        <i class="trash alternate icon"></i>
        Delete Post
      </div>
      <div class="content">
        <p>Are you sure you want to delete this post?</p>
      </div>
      <div class="actions">
        <div class="ui red basic cancel button">
          <i class="remove icon"></i>
          No
        </div>
        <div class="ui green ok button">
          <i class="checkmark icon"></i>
          Yes
        </div>
      </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    <script src="js/SearchBarScript.js" charset="utf-8"></script>
    <script src="js/ViewProfile.js" charset="utf-8"></script>
    <script src="js/TopButton.js" charset="utf-8"></script>
    <script src="js/LikeManager.js" charset="utf-8"></script>
    <script src="js/CommentManager.js" charset="utf-8"></script>
  </body>
</html>
