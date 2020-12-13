var TopButton = $("#ToTopButton");

$(document).scroll(function() {
  if ($(document).scrollTop() > 500 && TopButton.css("display", "none")) {
    TopButton.show();
  }
  if ($(document).scrollTop() < 500 && TopButton.css("display", "block")) {
    TopButton.hide();
  }
});

function ScrollToTop() {
  $("html, body").animate({ scrollTop: 0 }, 1000, "easeOutQuint");
}
