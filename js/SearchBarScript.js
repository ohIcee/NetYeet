$('#TopSearchInput').keyup(function() {
  var searchVal = $('#TopSearchInput').val();
  if (searchVal.length > 0) {
      GetSearchResults(searchVal);
    }
});

function GetSearchResults(val) {
  $.ajax({
    url: 'php/HelperFunctions.php',
    type: "POST",
    data: {
      ajaxCommand: 'GetSearchResults',
      searchValue: val
    },
    success: function(data) {
      //console.log("data:\n" + data );
      data = JSON.parse(data);

      $(".ui.search").search({
        type: 'list',
        searchFields: [
          'Username'
        ],
        fullTextSearch: true,
        apiSettings: {
          responseAsync: function mockResponseAsync(settings, callback) {
            if (settings.urlData.query) {
              (function() {
                var result = {
                  "results": {}
                };

                data.filter(function(user) {
                  return user.Username.toLowerCase().includes(settings.urlData.query.toLowerCase());
                }).forEach(function(item) {
                  //console.log(item);
                  result.results['category' + item.Username.toString()] = {
                    "name": item.Username.toString(),
                    "results": [item]
                  };
                });
                callback(result);
              })();
            } else callback({});
          },
          throttle: 0
        },
        templates: {
          message: function message(type, _message) {
            var html = '<div class="message empty"><div class="header">No users found</div><div class="description">Your search was not successful</div></div>';
            return html;
          },
          list: function list(response) {
            var html = '<div class="ui middle aligned selection list">';
            Object.keys(response.results).forEach(function(key) {
              //html += '<div class="category"><a class="result"><div class="content"><div class="title">' + response.results[key].results[0].Username + '</div></div></a></div>';
              var onclickRedirect = "window.location.href='ViewProfile.php?id=" + response.results[key].results[0].ID + "'";
              html += ''
              + '<div class="item" style="padding: 12px" onclick='+onclickRedirect+'>'
                + '<img class="ui avatar image" src="UserImageUploads/'+response.results[key].results[0].ProfilePictureName+'">'
                + '<div class="content">'
                  + '<a class="header">' + response.results[key].results[0].Username + '</a>'
                + '</div>'
              + '</div>';
            });
            html += '</div>';
            return html;
          }
        }
      });

    }
  });
}
