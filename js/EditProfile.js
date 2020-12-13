var maxLength = 250;

$(document).ready(function() {
  $("#maxBioCharCount").html(maxLength);
  $("#ChangeProfileImageInput").on('change', imageSelected);
  SetCheckboxEvents();
  GetPreferences();
  CheckAvailableBioChars();
});

$("#bio").bind('input propertychange', function() {
  $('#bio').val(($(this).val()).substring(0, maxLength - 1));
  CheckAvailableBioChars();
});

function CheckAvailableBioChars() {
  var length = $("#bio").val().length;
  var availbleLength = maxLength-length;

  $("#avalBioCharCount").html(availbleLength);
}

function SetCheckboxEvents() {
  $('.ui.checkbox').checkbox();

  $("#ShowLocationCheckbox").checkbox({
    onChecked: function() {
      $("#LocationInputField").removeClass("disabled");
      $("#LocationInputField input").attr("tabIndex", "0");
    },
    onUnchecked: function() {
      $("#LocationInputField").addClass("disabled");
      $("#LocationInputField input").attr("tabIndex", "-1");
    }
  });

  $("#ShowWebsiteCheckbox").checkbox({
    onChecked: function() {
      $("#WebsiteInputField").removeClass("disabled");
      $("#WebsiteInputField input").attr("tabIndex", "0");
    },
    onUnchecked: function() {
      $("#WebsiteInputField").addClass("disabled");
      $("#WebsiteInputField input").attr("tabIndex", "-1");
    }
  });

}

function GetPreferences() {

  $.ajax({
    url: 'php/HelperFunctions.php',
    type: "POST",
    data: {
      ajaxCommand: 'GetProfilePreferences'
    },
    success: function(data) {
      ProcessPreferences(JSON.parse(data));
      $("#loader").removeClass("loading");
    }
  });

  function ProcessPreferences(preferences) {
    if (preferences.ShowEmail == 1) { $('#ShowEmailCheckbox').checkbox('check'); }
    if (preferences.ShowDOB == 1) { $('#ShowDOBCheckbox').checkbox('check'); }
    if (preferences.ShowGender == 1) { $('#ShowGenderCheckbox').checkbox('check'); }
    if (preferences.ShowLocation == 1) { $('#ShowLocationCheckbox').checkbox('check'); }
    else {
      $("#LocationInputField").addClass("disabled");
      $("#LocationInputField input").attr("tabIndex", "-1");
     }
    if (preferences.ShowWebsite == 1) { $('#ShowWebsiteCheckbox').checkbox('check'); }
    else {
      $("#WebsiteInputField").addClass("disabled");
      $("#WebsiteInputField input").attr("tabIndex", "-1");
    }
  }

}

var prevImgSrc = "";

function imageSelected() {

  prevImgSrc = $("#ProfilePictureImagePreview").attr('src');

  var addImageInput = $("#ChangeProfileImageInput");
  var files = addImageInput.prop('files');

  if (files[0] == null) {
    $("#ProfilePictureImagePreview").attr('src', 'UserImageUploads/default_profile_picture.jpg');
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
      $("#ProfilePictureImagePreview").attr('src', e.target.result);
    };

    reader.readAsDataURL(addImageInput.prop('files')[0]);
  }
}
