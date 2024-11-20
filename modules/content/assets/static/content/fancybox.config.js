define(function () {
  return {
    loop: true,
    buttons: [
      //'slideShow',
      'fullScreen',
      //'thumbs',
      //'share',
      //'download',
      //'zoom',
      'close'
    ],

    protect: true,

    image: {

      // Wait for images to load before displaying
      // Requires predefined image dimensions
      // If 'auto' - will zoom in thumbnail if 'width' and 'height' attributes are found
      preload: "auto"

    },
  };
});
