define([
    "jquery",
    "underscore"
], function($, _)
{
    if ( ! $.fn.select2 )
        throw new Error("Connect select2 to page first");

    return function()
    {
        var node,
            self = this;

        function initUserValues(options) {
            options.createSearchChoice = function (term, data) {
                if ( $(data).filter(function() { return this.text.localeCompare(term) === 0; }).length === 0 ) {
                    node.data("text", term);
                    return { id: 0, text: term + " (ваш вариант)", label: term };
                }
            };

            options.createSearchChoicePosition = 'bottom';
        }

        function initSelection(options, initialValueData) {
            var valuesToInit = _.isArray(initialValueData)
                ? _.pluck(initialValueData, "id")
                : initialValueData.id;

            node.val(valuesToInit);

            self.data(initialValueData);

            options.initSelection = function(el, callback)
            {
                callback(initialValueData);
            };
        }

        this.init = function (selector, query_callback, initialValueData, enableUserValues, options)
        {
            options = options || {};

            node = $(selector);

            options.query = query_callback;

            if (initialValueData)
                initSelection(options, initialValueData);

            if (enableUserValues)
                initUserValues(options);

            node.select2(options);
        };

        // Получаем/устанавливаем ID выбранного элемента (или массив ID, если выбранных элементов несколько)
        this.value = function(value)
        {
            return ( typeof(value) == "undefined" )
                ? node.select2("val")
                : node.select2("val", value);
        };

        // Получаем/устанавливаем хеш выбранного элемента (или массив хешей, если выбранных элементов несколько)
        this.data = function(value)
        {
            var data = ( typeof(value) == "undefined" )
                ? node.select2("data")
                : node.select2("data", value);

            return data || { id: null, text: null, label: null };
        };

        this.readonly = function(value)
        {
            node.select2("readonly", !!value);
        };

        this.triggerChange = function()
        {
            node.trigger("change");
        };

        this.onChange = function(callback)
        {
            node.on("change", function()
            {
                callback(self.value(), self.data());
            });
        };

        this.setItems = function(data, initialValueData, allowUserValues)
        {
            var options = {
                data: data
            };

            if (initialValueData)
                initSelection(options, initialValueData);

            if (allowUserValues)
                initUserValues(options);

            node.select2(options);

            this.triggerChange();
        };

    };

});
