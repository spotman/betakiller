require([
  "jquery",
  "ckeditor-post-message"
], function($, ckeditorPostMessage) {

  $(function() {

    $(".static-shortcode").click(function() {
      const $button = $(this),
            shortcode = $button.text();

      ckeditorPostMessage.insertText(shortcode);
    });

  });

});
