require([
  'jquery',
  'error.api.rpc.definition',
  'materialize'
], function ($, api, M) {

  $(function () {
    const $resolveButton = $('.resolve-error-button'),
          $ignoreButton  = $('.ignore-error-button'),
          $deleteButton  = $('.delete-error-button');

    var elems = document.querySelectorAll('.collapsible');
    M.Collapsible.init(elems);

    function processActionButtonClick($button, apiMethod) {
      $button.attr('disabled', 'disabled');

      const hash = $button.data('hash');

      apiMethod(hash)
        .done(function () {
          const text = $button.data('done-text'),
                icon = $button.data('done-icon');

          $button.find('.text').text(text);
          $button.find('.icon').text(icon);
        })
        .fail(function (message) {
          alert(message || 'Oops! Something went wrong...');
          $button.removeAttr('disabled');
        });
    }

    $resolveButton.on('click', function (e) {
      e.preventDefault();
      processActionButtonClick($(this), api.phpException.resolve);
    });

    $ignoreButton.on('click', function (e) {
      e.preventDefault();
      processActionButtonClick($(this), api.phpException.ignore);
    });

    $deleteButton.on('click', function (e) {
      e.preventDefault();
      processActionButtonClick($(this), api.phpException.delete);
    });
  });

});
