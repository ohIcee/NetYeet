var lastOpenedPostCommentsID;

// TODO COMMENT LIKES

function OpenPostComments(postID) {
  lastOpenedPostCommentsID = postID;

  $("#CommentsModalSegment").addClass('loading');
  $("#PostComments").modal({ observeChanges: true }).modal('show');

  GetPostComments(postID);
}

function GetPostComments(postID) {
  $.ajax({
    url: 'php/HelperFunctions.php',
    type: "POST",
    data: {
      ajaxCommand: 'GetPostComments',
      PostID: postID
    },
    success: function(data) {
      data = JSON.parse(data);
      if (data.length > 0) {
        ShowComments(data, postID);
      } else {
        $("#CommentsArea").html('<div class="ui blue icon message"><i class="comments icon"></i><div class="content"><div class="header">No Comments!</div>Be the first to comment!</div></div>');
      }
      $("#CommentsModalSegment").removeClass("loading");
    }
  });
}

function ShowComments(comments, postID) {
  ResetComments();

  var commentBranch = "";

  $(comments).each(function(index) {
    var currentComment = comments[index];
    commentBranch += NewComment(currentComment);
  });

  $("#CommentsArea").html(commentBranch);
}

function NewComment(comment) {

  var content = comment.Content;

  // Replace reply tags with stylized label tags
  var regex = />>[0-9]*/g;
  var tag = content.match(regex);
  if (tag != null) {
    $(tag).each(function(index) {
      content = content.replace(tag[index], '<div class="ui red horizontal label">'+tag[index]+'</div>')
    });
  }

  var newcomm = "";
  newcomm += "<div class='comment'>";
  newcomm +=  "<a class='avatar'><img style='max-width: 100%; height: auto' src='UserImageUploads/"+comment.ProfilePictureName+"'></a>";
  newcomm +=  "<div class='content'>";
  newcomm +=    "<a class='author'>"+comment.Username+"</a>";
  newcomm +=    "<div class='metadata'>"+comment.DateCommented;
  newcomm +=      "<div class='ui label'>";
  newcomm +=        ">>";
  newcomm +=        "<div class='detail'>"+comment.ID+"</div>";
  newcomm +=      "</div>";
  newcomm +=     "</div>";
  newcomm +=    "<div class='text'>"+content+"</div>";
  newcomm +=    "<div class='actions'>";
  newcomm +=      "<a onclick='GetCommentID("+comment.ID+")' class='ui brown tiny header'>Reply</a>";
  if (Number(loggedUserID) == Number(comment.CommenterID)) {
    newcomm +=      "<a onclick='ShowConfirmDeleteComment("+comment.ID+")'>Delete Comment</a>";
  }
  newcomm +=    "</div>";
  newcomm +=  "</div>";
  newcomm += "</div>"
  return newcomm;
}

function ShowConfirmDeleteComment(id) {

  //$('#CommentRemovalConfirmModal').modal('show');

  DeleteComment(id);

  function DeleteComment() {

    console.log("DELETING");

    $.ajax({
      url: 'php/HelperFunctions.php',
      type: "POST",
      data: {
        ajaxCommand: 'DeleteComment',
        CommentID: id
      },
      success: function(data) {
        console.log(data);
        GetPostComments(lastOpenedPostCommentsID);
        ResetComments();
      }
    });
  }

  /*$('#CommentRemovalConfirmModal').modal({
    onApprove: function() {
      DeleteComment();
    }
  });*/

}

function ResetComments() {
  $("#CommentsArea").html("");
}

function PostComment() {
  var content = $("#CommentTextArea").val();

  $.ajax({
    url: 'php/HelperFunctions.php',
    type: "POST",
    data: {
      ajaxCommand: 'PostComment',
      PostID: lastOpenedPostCommentsID,
      CommentContent: content
    },
    success: function(data) {
      $("#CommentTextArea").val("");
      OpenPostComments(lastOpenedPostCommentsID);
    }
  });

}

function GetCommentID(id) {
  $("#CommentTextArea").val($("#CommentTextArea").val() + " >>" + id + " ");
  $("#CommentTextArea").focus();
}
