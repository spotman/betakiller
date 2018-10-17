$(function () {
  var $container = $(".error-500");

  $container.addClass("countdown-started");

  var timeout = setTimeout(function () {
    location.reload();
  }, 45000);

  $('.cancel-refresh').click(function (e) {
    e.preventDefault();

    $(this).attr('disabled', 'disabled');

    timeout && clearTimeout(timeout);
    $container.addClass("countdown-cancelled");
  });
});
