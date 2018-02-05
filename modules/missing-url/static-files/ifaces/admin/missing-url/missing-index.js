require([
  "jquery",
  "missing-url.api.rpc.definition",
  "admin.notify",
  "materialize"
], function ($, rpc, notify, M) {

  $(function () {

    const $items = $('.missing-url-item');

    // Init tabs
    $items.find('.tabs').tabs({
      swipeable: true
    });

    // Init buttons
    $items.each(function () {
      const $item         = $(this),
            $form         = $item.find('form'),
            id            = $item.data('id'),
            $submitButton = $form.find('button[type="submit"]'),
            $deleteButton = $item.find('button.delete-missing-url');

      $form.submit(function (e) {
        e.preventDefault();

        const targetUrl = $form.find('input.target-url').val();

        $submitButton.add($deleteButton).attr('disabled', 'disabled');

        const data = {
          id: id,
          targetUrl: targetUrl
        };

        rpc.missingUrl.update(data)
          .done(function () {
            notify.success('Updated!');
          })
          .fail(function (message) {
            notify.error(message || 'Something went wrong!');
          })
          .always(function () {
            $submitButton.add($deleteButton).removeAttr('disabled', 'disabled');
          });

      });

      $deleteButton.click(function (e) {
        e.preventDefault();

        $deleteButton.add($submitButton).attr('disabled', 'disabled');

        rpc.missingUrl.delete(id)
          .done(function () {
            $item.slideUp(function() {
              $item.remove();
            });
          })
          .fail(function (message) {
            notify.error(message || 'Something went wrong!');
          })
          .always(function () {
            $deleteButton.add($submitButton).removeAttr('disabled', 'disabled');
          });

      });
    });

  });

});
