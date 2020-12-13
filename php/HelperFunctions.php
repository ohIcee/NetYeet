<?php

require_once 'DBConnect.php';

// Used for Live user search
if (isset($_POST['ajaxCommand'])) {
	if ($_POST['ajaxCommand'] == "GetSearchResults") {
		echo GetSearchResults();
	}
	if ($_POST['ajaxCommand'] == "GetProfilePreferences") {
		echo json_encode(GetProfilePreferences($_SESSION['loggedUserID']));
	}
	if ($_POST['ajaxCommand'] == "LikePost") {
		echo LikePost();
	}
	if ($_POST['ajaxCommand'] == "UnlikePost") {
		echo UnlikePost();
	}
	if ($_POST['ajaxCommand'] == "GetPost") {
		echo GetPost($_POST['PostID']);
	}
	if ($_POST['ajaxCommand'] == 'GetPostComments') {
		echo json_encode(GetPostComments($_POST['PostID']));
	}
	if ($_POST['ajaxCommand'] == 'PostComment') {
		echo json_encode(PostComment());
	}
	if ($_POST['ajaxCommand'] == 'DeleteComment') {
		echo DeleteComment();
	}
	if ($_POST['ajaxCommand'] == 'GetFollowers') {
		echo json_encode(GetFollowers());
	}
	if ($_POST['ajaxCommand'] == 'GetFollowingUserInfo') {
		echo json_encode(GetFollowingUserInfo());
	}
}

function GetFollowers() {
	global $db;

	$sql = "SELECT users.ID, UserFollows.UserID, users.Username, users.ProfilePictureName FROM UserFollows INNER JOIN users ON users.ID=UserFollows.UserID WHERE UserFollows.FollowID=:id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $_POST['id']);
	$result = $stmt->execute();
	$rows = $stmt->fetchAll();
	return $rows;
}

function GetFollowingUserInfo() {
	global $db;

	$users = GetFollowingUsers($_POST['id']);

	$sql = "SELECT ID, Username, ProfilePictureName FROM users WHERE ";

	$UsersInfo = array();
	foreach ($users as $key => $value) {
		if ($key == 0) {
			$sql .= "ID=:id".$key;
		} else {
			$sql .= " OR ID=:id".$key;
		}
	}

	$stmt = $db->prepare($sql);

	foreach ($users as $key => $value) {
		$stmt->bindValue(':id'.$key, $value);
	}

	$result = $stmt->execute();
	$rows = $stmt->fetchAll();

	return $rows;
}

function DeleteComment() {
	global $db;

	// REMOVE FROM DB
	$sql = "DELETE FROM PostComments WHERE ID=:id AND CommenterID=:userid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $_POST['CommentID']);
	$stmt->bindValue(':userid', $_SESSION['loggedUserID']);
	$result = $stmt->execute();

	return $result;
}

function GetPostCommentCount($PostID) {
	global $db;

	$sql = "SELECT COUNT(*) as commentCount FROM PostComments WHERE PostID=:postid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':postid', $PostID);
	$stmt->execute();
	$rows = $stmt->fetch(PDO::FETCH_ASSOC);

	return $rows;
}

function PostComment() {
	global $db;

	$sql = "INSERT INTO PostComments (CommenterID, PostID, Content) VALUES (:commenterid, :postid, :content)";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':commenterid', $_SESSION['loggedUserID']);
	$stmt->bindValue(':postid', $_POST['PostID']);
	$stmt->bindValue(':content', $_POST['CommentContent']);
	$result = $stmt->execute();

	if ($result) {
		echo "SUCCESS";
	} else {
		echo "ERR";
	}
}

function LikePost() {
	global $db;

	$sql = "INSERT INTO LikedPosts (PostID, UserID) VALUES (:postid, :userid)";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':postid', $_POST['postID']);
	$stmt->bindValue(':userid', $_SESSION['loggedUserID']);
	$result = $stmt->execute();

	if ($result) {
		echo "SUCCESS";
	} else {
		echo "ERR";
	}
}

function UnlikePost() {
	global $db;

	$sql = "DELETE FROM LikedPosts WHERE PostID=:postid AND UserID=:userid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':postid', $_POST['postID']);
	$stmt->bindValue(':userid', $_SESSION['loggedUserID']);
	$result = $stmt->execute();

	if ($result) {
		echo "SUCCESS";
	} else {
		echo "ERR";
	}
}

function GetPostLikes($PostID) {
	global $db;

	$sql = "SELECT COUNT(*) as likeCount FROM LikedPosts WHERE PostID=:postid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':postid', $PostID);
	$stmt->execute();
	$rows = $stmt->fetch(PDO::FETCH_ASSOC);

	return $rows;
}

function GetPostComments($PostID) {
	global $db;

	$sql = "SELECT PostComments.CommenterID, PostComments.ID, users.Username, users.ProfilePictureName, PostComments.Content, PostComments.DateCommented FROM PostComments INNER JOIN users ON PostComments.CommenterID=users.ID WHERE PostComments.PostID=:postid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':postid', $PostID);
	$stmt->execute();
	$rows = $stmt->fetchAll();

	return $rows;
}

// USELESS
function GetComment($CommentID) {
	global $db;

	$sql = "SELECT PostComments.ID, users.Username, users.ProfilePictureName, PostComments.Content, PostComments.DateCommented FROM PostComments INNER JOIN users ON PostComments.CommenterID=users.ID WHERE PostComments.ID=:commentid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':commentid', $CommentID);
	$stmt->execute();
	$rows = $stmt->fetch(PDO::FETCH_ASSOC);

	return $rows;
}

function CompressImage($source, $destination, $quality) {

	$info = getimagesize($source);

	if ($info['mime'] == 'image/jpeg')
		$image = imagecreatefromjpeg($source);

	elseif ($info['mime'] == 'image/gif')
		$image = imagecreatefromgif($source);

	elseif ($info['mime'] == 'image/png')
		$image = imagecreatefrompng($source);

	imagejpeg($image, $destination, $quality);

	return $destination;
}

function GetFollowingUsers($id) {
	global $db;

	$sql = "SELECT FollowID FROM UserFollows WHERE UserID=:id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $id);
	$result = $stmt->execute();
	$rows = $stmt->fetchAll();

	$IDs = array();
	foreach ($rows as $key => $value) {
		array_push($IDs, $value['FollowID']);
	}

	return $IDs;
}

function GetSearchResults() {
	global $db;

	$sql = "SELECT ID, Username, ProfilePictureName FROM users WHERE Username LIKE :searchval AND Active=1 LIMIT 5";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':searchval', '%'.$_POST['searchValue'].'%');
	$stmt->execute();
	$rows = $stmt->fetchAll();

	return json_encode($rows);
}

function GetProfileInfo($profileID) {
	global $db;

	$sql = "SELECT ID, Username, Email, DOB, Gender, ProfilePictureName, JoinDate, Bio, Location, Website FROM users WHERE ID=:id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $profileID);
	$stmt->execute();
	$rows = $stmt->fetch(PDO::FETCH_ASSOC);

	return $rows;
}

function GetProfilePreferences($profileID) {
	global $db;

	$sql = "SELECT ShowEmail, ShowDOB, ShowGender, ShowLocation, Location, ShowWebsite FROM users WHERE ID=:id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $profileID);
	$stmt->execute();
	$rows = $stmt->fetch(PDO::FETCH_ASSOC);

	return $rows;
}

function GetUserPosts($ID) {
	global $db;

	$sql = "SELECT posts.ID, posts.PublishDate, posts.Content, posts.Type, posts.ImageName, posts.Privacy, users.Username FROM posts INNER JOIN users ON posts.PosterID = users.ID WHERE posts.PosterID = :posterid ORDER BY PublishDate DESC";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':posterid', $ID);
	$stmt->execute();
	$rows = $stmt->fetchAll();

	return $rows;
}

function GetPost($PostID) {
	global $db;

	$sql = "SELECT ID, PublishDate, Content, Type, ImageName, Privacy FROM posts WHERE ID=:postid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':postid', $PostID);
	$stmt->execute();
	$rows = $stmt->fetch(PDO::FETCH_ASSOC);

	return $rows;
}

function GetUserLikedPosts($ID) {
	global $db;

	$sql = "SELECT PostID FROM LikedPosts WHERE UserID=:userid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':userid', $ID);
	$stmt->execute();
	$rows = $stmt->fetchAll();

	return $rows;
}

function GetFollowingUsersPosts() {
	global $db;

	$IDs = GetFollowingUsers($_SESSION['loggedUserID']);

	$sql = "SELECT posts.ID, posts.PublishDate, posts.Content, posts.Type, posts.ImageName, posts.Privacy, users.ID as UserID, users.Username, users.ProfilePictureName FROM posts INNER JOIN users ON posts.PosterID = users.ID WHERE ";

	foreach ($IDs as $key => $value) {
		if ($key == 0) {
			$sql .= "posts.PosterID=:own or posts.PosterID=:u".$key;
		} else {
			$sql .= " or posts.PosterID=:u".$key;
		}
	}

	$sql .= " ORDER BY PublishDate DESC";
	$stmt = $db->prepare($sql);
	$stmt->bindValue('own', $_SESSION['loggedUserID']);

	foreach ($IDs as $key => $value) {
		$stmt->bindValue(':u'.$key, $value);
	}

	$result = $stmt->execute();
	$rows = $stmt->fetchAll();

	return $rows;
}

function GetUserInfo($ID) {
	global $db;

	$info = array();

	$sql = "SELECT COUNT(*) as FollowingCount FROM UserFollows WHERE UserID=:id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $ID);
	$result = $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	array_push($info, $row);

	$sql = "SELECT COUNT(*) as Followers FROM UserFollows WHERE FollowID=:id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $ID);
	$result = $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	array_push($info, $row);

	$sql = "SELECT COUNT(*) as LikedPosts FROM LikedPosts WHERE UserID=:id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':id', $ID);
	$result = $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	array_push($info, $row);

	$sql = "SELECT COUNT(*) as isFollowing FROM UserFollows WHERE UserID=:loggedid AND FollowID=:id";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':loggedid', $_SESSION['loggedUserID']);
	$stmt->bindValue(':id', $ID);
	$result = $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	array_push($info, $row);

	return $info;
}

function FollowUser($LoggedID, $FollowID) {
	global $db;

	$sql = "INSERT INTO UserFollows(UserID, FollowID) VALUES (:loggedid, :followid)";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':loggedid', $LoggedID);
	$stmt->bindValue(':followid', $FollowID);
	$stmt->execute();

	header("Location: ViewProfile.php?id=".$FollowID);
}

function UnfollowUser($LoggedID, $FollowID) {
	global $db;

	$sql = "DELETE FROM UserFollows WHERE UserID=:loggedid AND FollowID=:followid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':loggedid', $LoggedID);
	$stmt->bindValue(':followid', $FollowID);
	$stmt->execute();

	header("Location: ViewProfile.php?id=".$FollowID);
}

function DeletePost($postID) {
	global $db;

	// SELECT IMAGE NAME
	$sql = "SELECT ImageName FROM posts WHERE PosterID=:userid AND ID=:postid";
	$stmt = $db->prepare($sql);
	$stmt->bindValue(':userid', $_SESSION['loggedUserID']);
	$stmt->bindValue(':postid', $postID);
	$result = $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($result) {
		// REMOVE FROM DB
		$sql = "DELETE FROM posts WHERE PosterID=:userid AND ID=:postid";
		$stmt = $db->prepare($sql);
		$stmt->bindValue(':userid', $_SESSION['loggedUserID']);
		$stmt->bindValue(':postid', $postID);
		$result = $stmt->execute();

		// REMOVE FROM SERVER
		if ($result) {
			unlink('UserImageUploads/' . $row['ImageName']);
		}
	}

	header("Location: ViewProfile.php?id=".$_SESSION['loggedUserID']);
}
?>
