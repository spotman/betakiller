define([
  "auth.widget"
], function (widget) {

  return {

    initialize: function ($container) {
      var readyCallback = function () {
      };

      var successfulCallback = function () {
        // Всё в порядке, перенаправляем пользователя
        location.reload(true);
      };

      widget.initialize(
        $container,
        readyCallback,
        successfulCallback
      );
    }

  };

});
