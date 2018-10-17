$(function () {
  var $container = $(".error-503"),
      $progress  = $container.find('.progress-bar');

  var duration = $container.data('duration'),
      counter  = 0;

  setInterval(function () {
    counter++;

    var size = ((counter / duration) * 100).toFixed(2);
    $progress.css({maxWidth: size + "%"});

    if (counter > duration) {
      location.reload();
    }
  }, 1000);
});
