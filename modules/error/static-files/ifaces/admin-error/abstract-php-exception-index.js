require([
  'jquery',
  'error.api.rpc.definition'
], function($, api) {

  $(function() {
    var $resolveButtons = $('.resolve-error-button'),
        $ignoreButtons = $('.ignore-error-button'),
        $deleteButtons = $('.delete-error-button');

    function processActionButtonClick($button, apiMethod) {
      $button.attr('disabled', 'disabled');

      var $item = $button.closest('.php-exception-item'),
          hash = $item.data('hash');

      apiMethod(hash)
        .done(function () {
          $item.hide(750, function() {
            $(this).remove();
          })
        })
        .fail(function(message) {
          alert(message || 'Oops! Something went wrong...')
          $button.removeAttr('disabled');
        });
    }

    $resolveButtons.on("click", function(e) {
      e.preventDefault();
      processActionButtonClick($(this), api.phpException.resolve);
    });

    $ignoreButtons.on("click", function(e) {
      e.preventDefault();
      processActionButtonClick($(this), api.phpException.ignore);
    });

    $deleteButtons.on("click", function(e) {
      e.preventDefault();
      processActionButtonClick($(this), api.phpException.delete);
    });

  });

});
