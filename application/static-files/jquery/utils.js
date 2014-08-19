(function( $ ){

    $.JSON = new function()
    {
        this.handler = function(r, success_callback, error_callback)
        {
            if ( ! r || ! r.response )
            {
                error_callback && error_callback(null);
                throw new Error("Empty response");
            }

            switch ( r.response )
            {
                case "ok":
                    success_callback && success_callback(r.message);
                    break;

                case "error":
                    error_callback && error_callback(r.message);
                    break;

//                case "auth":
//                    window.top.location.href = '/logout';
//                    break;
//
//                case "refresh":
//                    window.top.location.href = '/';
//                    break;

                default:
                    error_callback && error_callback(null);
                    throw new Error("Unknown response [" + r.response + "]");
            }

        };

        this.request = function(type, url, data, success_callback, error_callback, options)
        {
            if ( typeof(data) == "function" )
            {
                options = error_callback;
                error_callback = success_callback;
                success_callback = data;
            }

            options = options || {};

            var _handler = this.handler;

            var config = {
                url: url,
                data: data,
                // dataType: "json",
                type: type,
                success: function(data)
                {
                    try
                    {
                        _handler(data, success_callback, error_callback);
                    }
                    catch(e)
                    {
                        console.error(e.message, url, r, data);
                    }
                }
            };

            return $.ajax( $.extend(config, options))
                .fail(function(){ error_callback && error_callback(null) });
        };


        /**
         * POST-request to universal JSON gateway
         *
         * @param url
         * @param data
         * @param success_callback
         * @param error_callback
         * @param options
         * @returns {deferred}
         */
        this.post = function(url, data, success_callback, error_callback, options)
        {
            return this.request("post", url, data, success_callback, error_callback, options);
        };

        /**
         * GET-request to universal JSON gateway
         *
         * @param url
         * @param data
         * @param success_callback
         * @param error_callback
         * @param options
         * @returns {*}
         */
        this.get = function(url, data, success_callback, error_callback, options)
        {
            return this.request("get", url, data, success_callback, error_callback, options);
        };

    };


    // Хелперы к jquery.pnotify
    $.notify = {

        // песочный цвет
        notice: function(text)
        {
            return $.pnotify({
                history: false,
                text: text
            });
        },

        // голубой цвет
        info: function(text)
        {
            return $.pnotify({
                history: false,
                text: text,
                type: 'info'
            });
        },

        // зелёный цвет
        success: function(text)
        {
            return $.pnotify({
                history: false,
                text: text,
                type: 'success'
            });
        },

        // красный цвет
        error: function(text, permanent)
        {
            return $.pnotify({
                history: false,
                text: text,
                type: 'error',
                hide: !permanent,
                closer: !permanent,
                sticker: !permanent
            });
        }

    };

    $.alert = function(message, callback)
    {
        bootbox.alert(message, callback);
    };

    $.confirm = function(message, ok_callback, cancel_callback)
    {
        bootbox.confirm(message, function(result)
        {
            if ( result )
            {
                ok_callback();
            }
            else
            {
                cancel_callback && cancel_callback();
            }
            // console.log(result);
        });
    };

    /**
     * Загружает файл по HTTP без ограничений домена
     * @param url
     * @param data
     * @param load_callback
     */
    $.downloadFile = function(url, data, load_callback)
    {
        if ( typeof(data) == "function" )
        {
            load_callback = data;
        }

        // Формируем уникальное имя айфрейма
        var iframe_name = "download-iframe-" + (new Date()).getTime();

        console.log("iframe name " + iframe_name);

        // Создаём iframe
        var iframe = $('<iframe>')
            .attr("name", iframe_name)
            .appendTo("body")
            .hide();

        // Вешаем обработчик, который будет вызван после загрузки файла
        iframe.load(function()
        {
            // Вызываем коллбек, если он был указан
            load_callback && load_callback();

            // Уничтожаем айфрейм, чтобы не мусорить в DOM
            iframe.remove();
        });

        // Если нужно отправить POST на url
        if ( data )
        {
            // Создаём форму с данными
            var form = $("<form>")
                .attr("action", url)
                .attr("method", "post")
                .attr("target", iframe_name);

            // Добавляем скрытые поля с данными в форму
            for ( var key in data )
            {
                $("<input type='hidden' />")
                    .appendTo(form)
                    .attr("name", key)
                    .attr("value", data[key]);
            }

            // Отправляем форму в iframe и уничтожаем её
            form.appendTo("body").submit().remove();
        }
        else
        {
            // Просто загружаем ресурс в айфрейм
            iframe.attr("src", url);
        }
    };

})( jQuery );
