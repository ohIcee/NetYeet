var postType;
var postPrivacy;
var postImage;

$(document).ready(function() {
  $("#CreatePostImageInput").on('change', imageSelected);
});

function imageSelected() {

  prevImgSrc = $("#ProfilePictureImagePreview").attr('src');

  var addImageInput = $("#CreatePostImageInput");
  var files = addImageInput.prop('files');

  if (files[0] == null) {
    $("#CreatePostImagePreview").attr('src', prevImgSrc);
    return;
  }

  if (files[0].size / 1024 > 2047) {
    $("#ImageSizeError").show();
    addImageInput.val(null);
    imageSelected();
  }

  if (files && files[0]) {
    $("#ImageSizeError").hide();
    var reader = new FileReader();

    reader.onload = function (e) {
      $("#CreatePostImagePreview").attr('src', e.target.result).width(500).height(500);
    };

    reader.readAsDataURL(addImageInput.prop('files')[0]);
  }
}
