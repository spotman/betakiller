require([
  "jquery",
  "ckeditor-post-message",
  "materialize",
], function($, ckeditorPostMessage) {

  $(function() {
    // TODO Floating buttons are not working after upgrade, maybe drop them and use dedicated buttons
    $('.editor-content-element-listing-item .fixed-action-btn').floatingActionButton({
      direction: "left"
    });

    $(".editor-content-element-listing-item .insert-handler").click(function (e) {
      e.preventDefault();

      const $handler = $(this),
            id = $handler.data('id'),
            tagName = $handler.data('tag-name');

      ckeditorPostMessage.insertShortcode(tagName, {id: id});
    });
  });

});
