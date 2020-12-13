<?php
session_start();

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

if (isset($_POST['SubmitCreatePostButton'])) {

  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  try {

    $PostContent = $_POST["postContent"];
    $PostPrivacy = $_POST["postPrivacySelect"];
    $PostType = 0;
    $ImageName = "";

    // Upload Photo
    if (!empty($_FILES['CreatePostImage']['tmp_name'])) {

      $PostType = 1;

      // Create Appropriate Unique File Name for Image
      $FileNameParts = explode(".", $_FILES["CreatePostImage"]["name"]);
      $NewFileName = $FileNameParts[0] . "-" . date('Ymd') . "-" . md5($_SESSION["loggedUserID"] + rand(1, 15)) . ".jpg";
      $ImageName = $NewFileName;

      // Compress and upload Image
      $folder = "UserImageUploads/";
      CompressImage($_FILES['CreatePostImage']['tmp_name'], "$folder".$NewFileName, 50);
    }

    // Upload Post into DB
    $sql = "INSERT INTO posts (PosterID, Content, Type, ImageName, Privacy)
            VALUES (:posterID, :content, :type, :imageName, :privacy)";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':posterID', $_SESSION['loggedUserID']);
    $stmt->bindValue(':content', $PostContent);
    $stmt->bindValue(':type', $PostType);
    $stmt->bindValue(':imageName', $ImageName);
    $stmt->bindValue(':privacy', $PostPrivacy);
    $result = $stmt->execute();

    if ($result) {
      echo "SUCCESS CREATE POST";
    } else {
      echo "FAILED TO CREATE POST";

      // Remove image if post upload into DB Fails
      unlink($folder . $NewFileName);
    }

    // Prevent Form Resubmittion
    header("Location: " . $_SERVER['REQUEST_URI']);

  } catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
    // Remove image if post upload into DB Fails
    unlink($folder . $NewFileName);
  }
}

$Posts = GetFollowingUsersPosts();
$LikedPosts = GetUserLikedPosts($_SESSION['loggedUserID']);

?>

<script type="text/javascript">
  var loggedUserID = <?php echo $_SESSION['loggedUserID']; ?>;
</script>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>NetYeet Social Network</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="css/index_design.css">
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

      <div class="ui stackable grid">
        <div class="computer only four wide column">
          <!--<a href='../../_FILES/bolsago_1.0.2.apk' download>-->
            <img src="https://i.imgur.com/k3xM0PV.png" alt="" style="width: 100%; cursor: pointer">
        <!--  </a>-->
        </div>
        <div class="twelve wide column">

          <!-- < Create new Post Form > -->
          <form class="raised segment ui form" action="index.php" method="post" enctype="multipart/form-data" id="CreatePostForm">
            <!-- < Post Input/Preview > -->
            <label class="ui large header">Create new post</label>
            <div class="ui hidden divider"></div>

            <div class="ui stackable grid">

              <div class="five wide column">
                <img id="CreatePostImagePreview" class="ui centered image" src="https://cobblestone.me/wp-content/plugins/photonic/include/images/placeholder.png">
              </div>

              <div class="eleven wide column">
                <div class="field">
                  <textarea id="PostContent" name="postContent" rows="11" placeholder="Caption"></textarea>
                </div>
              </div>

            </div>
            <!-- </ Post Input/Preview > -->

            <div id="ImageSizeError" class="ui negative message">
              <div class="header">
                Image too big to upload
              </div>
              <p>Maximum Image Size is 2MB</p>
            </div>

            <!-- < Bottom Buttons > -->
            <div class="ui stackable three column grid">

              <div class="column">
                <label for="CreatePostImageInput" class="ui secondary fluid icon button">
                  <i class="image icon"></i>
                  Add Image</label>
                <input type="file" accept="image/*" id="CreatePostImageInput" style="display:none" name="CreatePostImage">
              </div>

              <div class="column">

                <div class="field disabled">
                  <select id="PostPrivacySelect" name="postPrivacySelect" class="ui fluid dropdown">
                    <option value="0">Public</option>
                    <option value="1" selected>Friends Only</option>
                    <option value="2">Private</option>
                  </select>
                </div>
              </div>

              <div class="column">
                <button type="submit" name="SubmitCreatePostButton" class="ui submit fluid primary button">Post</button>
              </div>

            </div>
            <!-- </ Bottom Buttons > -->
          </form>
          <!-- </ Create new Post Form > -->

          <div class="ui hidden divider"></div>

          <label class="ui large header">Feed</label>
          <div class="ui hidden divider"></div>

          <!--<div class="ui two stackable cards">-->
          <div class="ui large feed">
            <?php

            if (count($Posts) > 0) {

              foreach ($Posts as $key => $value):

                $IsPostLiked = '';
                foreach ($LikedPosts as $likekey => $likevalue) {
                  if ($likevalue['PostID'] == $value['ID']) {
                    $IsPostLiked = 'active';
                  }
                }

                $PostLikes = GetPostLikes($value['ID']);
                $PostComments = GetPostCommentCount($value['ID']);

                if ($value['Type'] == '0') {
                  // Non-Image Post
                  echo '
                  <div class="event">
                    <div class="label">
                      <img src="UserImageUploads/'.$value['ProfilePictureName'].'">
                    </div>
                    <div class="content">
                      <div class="summary">
                        <a class="user" href="ViewProfile.php?id='.$value['UserID'].'">
                          '.ucfirst($value['Username']).'
                        </a>
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
                      </div>
                    </div>
                  </div>
                  ';

                } else {
                  // Image Post
                  echo '
                  <div class="event">
                    <div class="label">
                      <img src="UserImageUploads/'.$value['ProfilePictureName'].'">
                    </div>
                    <div class="content">
                      <div class="summary">
                        <a class="user" href="ViewProfile.php?id='.$value['UserID'].'">
                          '.ucfirst($value['Username']).'
                        </a>
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
                      </div>
                    </div>
                  </div>
                  ';
                }

                echo '<div class="ui section divider"></div>';

              endforeach;
            } else {
              echo '
              <div class="ui blue message">
              <h2 class="ui header">
                <i class="frown outline icon"></i>
                <div class="content">
                  It\'s lonely here!
                  <div class="sub header">Go follow some people! <a href="ViewProfile.php?id=17">( This guy for example (click) )</a> </div>
                </div>
              </h2>
              </div>
                ';
            }
            ?>
          </div>

        </div>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    <script src="js/CreatePostScript.js" charset="utf-8"></script>
    <script src="js/SearchBarScript.js" charset="utf-8"></script>
    <script src="js/TopButton.js" charset="utf-8"></script>
    <script src="js/LikeManager.js" charset="utf-8"></script>
    <script src="js/CommentManager.js" charset="utf-8"></script>
    <script type="text/javascript">
      $('.ui.dropdown').dropdown();
    </script>
  </body>
</html>
