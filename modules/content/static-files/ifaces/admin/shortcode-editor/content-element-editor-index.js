require([
  "jquery",
  "ckeditor-post-message",
  "materialize.buttons",
], function($, ckeditorPostMessage) {

  $(function() {
    $(".editor-content-element-listing-item .insert-handler").click(function (e) {
      e.preventDefault();

      const $handler = $(this),
            id = $handler.data('id'),
            tagName = $handler.data('tag-name');

      ckeditorPostMessage.insertShortcode(tagName, {
        id: id
      });
    });
  });

});
