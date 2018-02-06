require([
  "jquery",
  "fancybox.config",
  "fancybox"
], function($, config) {

  $(function() {
    $(".fancybox").fancybox(config);
  });

});
