define([
  "jquery"
], function ($) {

  $.fn.JSON = function () {
    var CSRFTokenKey = 'csrf-key';
    var $this = $(this);

    var $CSRFTokenElements = getElementSCRFTokenElements($this);

    function getElementSCRFTokenElements(container) {
      // Searching for CSRF field inside of current jQuery-objects set
      var $csrf = container.find('input[name="' + CSRFTokenKey + '"]');
      return $csrf.length ? $csrf : null;
    }

    /**
     * Basic wrapper for jQuery XHR
     *
     * @param {Deferred} xhr
     * @returns {*}
     */
    this.wrap = function (xhr) {
      var deferred = $.Deferred();

      function successCallback(message, args) {
        args = args || [];
        // Add message to arguments
        [].unshift.call(args, message);
        // console.log('successCallback', args);
        deferred.resolveWith(xhr, args);
      }

      function errorCallback(message, args) {
        args = args || [];
        // Add message to arguments
        [].unshift.call(args, message);
        // console.log('errorCallback', args);
        deferred.rejectWith(xhr, args);
      }

      xhr.done(function (r) {
        // console.log('xhr done', arguments);

        if (!r || !r.response) {
          errorCallback(null);
          return;
        }

        // Refresh CSRF field value (if field exists)
        if ($CSRFTokenElements && r.hasOwnProperty(CSRFTokenKey)) {
          $CSRFTokenElements.val(r[CSRFTokenKey]);
        }

        var message = r.message;

        switch (r.response) {
          case "ok":
            successCallback(message, arguments);
            break;

          case "error":
            errorCallback(message, arguments);
            break;

          case "auth":
            window.top.location.href = '/logout';
            break;

          case "redirect":
            window.location.href = message;
            break;

          case "refresh":
            window.location.reload();
            break;

          default:
            errorCallback(null, arguments);
            throw new Error("Unknown response [" + r.response + "]");
        }
      });

      xhr.fail(function () {
        // console.log('xhr fail', arguments);
        errorCallback(null, arguments);
      });

      return deferred;
    };

    /**
     * Basic request helper
     *
     * @param {String} type
     * @param {String} url
     * @param {String=} [data]
     * @param {Object=} [options]
     * @returns {Deferred}
     */
    this.request = function (type, url, data, options) {
      data = data || {};
      options = options || {};

      // Add CSRF value if element needs CSRF check and there is CSRF token element
      if (!data[CSRFTokenKey] && $CSRFTokenElements) {
        data[CSRFTokenKey] = $CSRFTokenElements.first().val();
      }

      var config = {
        url: url,
        data: data,
        type: type,
        dataType: "json"
      };

      return this.wrap(
        $.ajax($.extend(config, options))
      );
    };


    /**
     * POST-request to universal JSON gateway
     *
     * @param {String} url
     * @param {String=} [data]
     * @param {Object=} [options]
     * @returns {Deferred}
     */
    this.post = function (url, data, options) {
      return this.request("post", url, data, options);
    };

    // console.log(arguments);

    // Basic usage is $("#form-id").JSON(url, data, options)
    // Wrapper usage is $("#form-id").JSON(xhr)
    if (arguments.length) {
      // console.log(arguments);
      if (typeof arguments[0] === "string") {
        return this.post.apply(this, arguments);
      } else {
        return this.wrap.apply(this, arguments);
      }
    }
  };

  /**
   * Downloads file by HTTP, cross-domain
   * @param url
   * @param data
   * @param load_callback
   */
  $.downloadFile = function (url, data, load_callback) {
    if (typeof(data) === "function") {
      load_callback = data;
    }

    // Формируем уникальное имя айфрейма
    var iframe_name = "download-iframe-" + (new Date()).getTime();

    // Создаём iframe
    var iframe = $('<iframe>')
      .attr("name", iframe_name)
      .appendTo("body")
      .hide();

    // Вешаем обработчик, который будет вызван после загрузки файла
    iframe.load(function () {
      // Вызываем коллбек, если он был указан
      load_callback && load_callback();

      // Уничтожаем айфрейм, чтобы не мусорить в DOM
      iframe.remove();
    });

    // Если нужно отправить POST на url
    if (data) {
      // Создаём форму с данными
      var form = $("<form>")
        .attr("action", url)
        .attr("method", "post")
        .attr("target", iframe_name);

      // Добавляем скрытые поля с данными в форму
      for (var key in data) {
        $("<input type='hidden' />")
          .appendTo(form)
          .attr("name", key)
          .attr("value", data[key]);
      }

      // Отправляем форму в iframe и уничтожаем её
      form.appendTo("body").submit().remove();
    } else {
      // Просто загружаем ресурс в айфрейм
      iframe.attr("src", url);
    }
  };

});
