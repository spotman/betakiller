define([
  "jquery",
  "twig.original"
], function(jQuery, Twig){

  Twig.extendFunction("image", function(attributes, data, forceSize) {

      jQuery.extend(attributes, data);

      if (forceSize) {
          delete attributes['width'];
          delete attributes['height'];
      }

      attributes.title = attributes.title || attributes.alt;

      var html = '<img';

      jQuery.each(attributes, function(key, value) {
          if (value) {
            html += ' ' + key + '="' + value + '"';
          }
      });

    return html + ' />';
  });

  return Twig;
});
