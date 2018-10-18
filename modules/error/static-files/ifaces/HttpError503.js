$(function () {
  var $container = $(".error-503"),
      $progress  = $container.find('.progress-bar');

  var duration = $container.data('duration'),
      counter  = 0;

  // Randomize end time by 20 seconds to prevent server overload on page reload
  duration += Math.floor(Math.random() * 20);

  console.log(duration);

  setInterval(function () {
    counter++;

    var size = ((counter / duration) * 100).toFixed(2);
    $progress.css({maxWidth: size + "%"});

    if (counter > duration) {
      location.reload();
    }
  }, 1000);
});
