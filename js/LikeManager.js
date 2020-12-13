function ToggleLike(el, id) {
  $(el).hasClass('active') ? UnlikePost(el, id) : LikePost(el, id);
}

function LikePost(el, postid) {

  $.ajax({
    url: 'php/HelperFunctions.php',
    type: 'POST',
    data: {
      ajaxCommand: 'LikePost',
      postID: postid
    },
    success: function(data) {

      var likecountEl = $(el).find('.likecount');

      if (data == 'SUCCESS') {
        $(el).addClass('active');
        likecountEl.html( parseInt(likecountEl.html())+1 );
      }
    }
  });

}

function UnlikePost(el, postid) {

  $.ajax({
    url: 'php/HelperFunctions.php',
    type: 'POST',
    data: {
      ajaxCommand: 'UnlikePost',
      postID: postid
    },
    success: function(data) {

      var likecountEl = $(el).find('.likecount');

      if (data == 'SUCCESS') {
        $(el).removeClass('active');
        likecountEl.html( parseInt(likecountEl.html())-1 );
      }
    }
  });

}
