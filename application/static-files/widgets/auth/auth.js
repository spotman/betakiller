require([
  "jquery",
  "auth.login"
], function ($, login) {

  $(function () {

    login.initialize(
      $(".widget-auth")
    );

  });

});
