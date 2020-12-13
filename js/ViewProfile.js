$(document).ready(function() {
  $('.ui.dropdown').dropdown();

  LoadFollowers();
  LoadFollowing();
});

function FollowUser(id) {
  window.location.href = "ViewProfile.php?id="+id+"&follow";
}

function UnfollowUser(id) {
  window.location.href = "ViewProfile.php?id="+id+"&unfollow";
}

function ShowConfirmRemovePost(userID, postID) {
  $('#PostRemovalConfirmModal').modal('show');

  function RemovePost() {
    window.location.href = "ViewProfile.php?id="+userID+"&deletepost="+postID;
  }

  $('#PostRemovalConfirmModal').modal({
    onApprove: function() {
      RemovePost();
    }
  });
}

function LoadFollowers() {

  $.ajax({
    url: 'php/HelperFunctions.php',
    type: 'POST',
    data: {
      ajaxCommand: 'GetFollowers',
      id: profileID
    },
    success: function(data) {
      data = JSON.parse(data);
      $(data).each(function(index) {
        $('#FollowersList').append(NewUser(data[index]));
      });
      $("#followersButton").removeClass("loading");
      $("#followersButton").removeClass("disabled");
    }
  });

  function NewUser(user) {
    var newus = "";
    newus += "<a href='?id="+user.ID+"' class='item'>";
    newus +=  "<img class='ui avatar image' src='UserImageUploads/"+user.ProfilePictureName+"'></img>";
    newus +=  "<div class='content'><div class='header'>"+user.Username+"</div></div>";
    newus += "</a>";
    return newus;
  }
}

function LoadFollowing() {
  $.ajax({
    url: 'php/HelperFunctions.php',
    type: 'POST',
    data: {
      ajaxCommand: 'GetFollowingUserInfo',
      id: profileID
    },
    success: function(data) {
      data = JSON.parse(data);
      $(data).each(function(index) {
        $('#FollowingUsersList').append(NewUser(data[index]));
      });
      $("#followingButton").removeClass("loading");
    }
  });

  function NewUser(user) {
    var newus = "";
    newus += "<a href='?id="+user.ID+"' class='item'>";
    newus +=  "<img class='ui avatar image' src='UserImageUploads/"+user.ProfilePictureName+"'></img>";
    newus +=  "<div class='content'><div class='header'>"+user.Username+"</div></div>";
    newus += "</a>";
    return newus;
  }

}

function ShowFollowers() {
  $('#FollowersModal').modal('show');
}

function ShowFollowingUsers() {
  $('#FollowingModal').modal('show');
}
