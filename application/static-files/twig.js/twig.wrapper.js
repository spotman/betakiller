define([
    "twig.original"
], function(Twig){

    // Factory
    return function()
    {
        // Prototype
        var p = function()
        {
            var template;

            this.fromHTML = function(html)
            {
                template = Twig.twig({ data: html });
                return this;
            };

            this.render = function(data)
            {
                return template.render(data);
            };
        };

        // New instance
        return new p;
    };

});