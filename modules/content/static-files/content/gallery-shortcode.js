require([
  "jquery",
  "fancybox.config",
  "fancybox",
  "slick",
], function($, fancyBoxConfig) {

  $(function() {
    var $contentGallerySlider = $('.content-gallery-slider');

    $contentGallerySlider.slick({
      dots: true,
      mobileFirst: true,
      slide: ".carousel-cell"
    });

    var fancyBoxOptions = $.extend(true, fancyBoxConfig, {
      // No hash navigation in gallery coz of errors due to loading page with anchor to non-initialized gallery
      hash: false,
    });

    console.log(fancyBoxOptions);

    $contentGallerySlider.each(function() {
      var $gallery = $(this);
      var isSliding = false;

      $gallery.on('beforeChange', function() {
        isSliding = true;
      });

      $gallery.on('afterChange', function() {
        isSliding = false;
      });

      var $items = $gallery.find('.carousel-cell');

      $gallery.on("click", ".carousel-cell", function(e) {
        e.preventDefault();

        var $originalItems = $items.not('.slick-cloned');

        // Hack for preventing Fancybox opening on Slick swipe
        if (!isSliding) {
          $.fancybox.open($originalItems, fancyBoxOptions, $originalItems.index(this));
        }

        return false;
      })
    });

    //$contentGallerySlider.on('afterChange', function(slick, $currentSlide) {
    //  // set image caption using img's alt
    //  var $this = $(this),
    //      $caption = $this.find('.image-caption');
    //
    //  console.log($this);
    //  console.log($currentSlide);
    //
    //  var text = $currentSlide.attr("title") || $currentSlide.attr("alt");
    //
    //  if (text) {
    //    $caption.text( text ).show();
    //  } else {
    //    $caption.text("").hide();
    //  }
    //});

  });

});
