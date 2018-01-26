require([
  'jquery',
  'error.api.rpc.definition'
], function($, api) {

  $(function() {
    const $throwExceptionButton = $('.throw-exception-button');

    function processApiCall($button, apiMethodPromise) {
      $button.attr('disabled', 'disabled');

      apiMethodPromise
        .done(function () {})
        .fail(function(message) {
          console.log(message || 'Oops! Something went wrong...')
        })
        .always(function() {
          $button.removeAttr('disabled');
        });
    }

    $throwExceptionButton.click(function(e) {
      e.preventDefault();

      const $this = $(this),
            code = $this.data('code');

      processApiCall($this, api.phpException.throwHttpException(code));
    });

  });

});
