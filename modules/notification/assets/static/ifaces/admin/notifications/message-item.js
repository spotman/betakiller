require([
  "jquery",
], function ($) {
  $(function () {
    const $form = $('form#test-notification'),
          $userId = $form.find("input");

    $form.submit(function (e) {
      let testUserId = $userId.val();

      testUserId = prompt('User ID', testUserId);

      if (testUserId?.length > 0) {
        $userId.val(testUserId);
      } else {
        e.preventDefault();
      }
    });
  });
});
