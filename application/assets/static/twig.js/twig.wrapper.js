define([
  "twig.extensions"
], function(Twig) {

    // Prototype
    var p = function() {
        var template;

        this.fromHTML = function(html) {
            template = Twig.twig({ data: html });
            return this;
        };

        this.render = function(data) {
            return template.render(data);
        };
    };

    return {
        factory: function(template) {
            var instance = new p();

            return instance.fromHTML(template);
        }
    };

});
