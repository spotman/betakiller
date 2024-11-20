$(function () {
  $('.back-button').on('click', function (e) {
    e.preventDefault();

    window.history.back();
  });
});
