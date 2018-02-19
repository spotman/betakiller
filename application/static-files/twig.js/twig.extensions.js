define([
  "twig.original"
], function(Twig){

  Twig.extendFunction("image", function(attributes, data, forceSize) {
      for (var dataKey in data) {
        if (data.hasOwnProperty(dataKey) && data[dataKey]) {
          attributes[dataKey] = data[dataKey];
        }
      }

      if (forceSize) {
          delete attributes['width'];
          delete attributes['height'];
      }

      //attributes.title = attributes.title || attributes.alt;

      var html = '<img';

      for (var key in attributes) {
        if (attributes.hasOwnProperty(key) && attributes[key]) {
          html += ' ' + key + '="' + attributes[key] + '"';
        }
      }

    return html + ' />';
  });

  return Twig;
});
