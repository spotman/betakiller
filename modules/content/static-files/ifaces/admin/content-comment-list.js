require([
  "jquery",
  "content.api.rpc.definition",
  "materialize.cards"
], function($, api) {

  $(function() {

    var $transitionButtons = $(".transition-button");

    $transitionButtons.click(function(e) {
      e.preventDefault();
      $transitionButtons.attr('disabled', 'disabled');

      var $this = $(this),
          id = $this.data("id"),
          method = $this.data("api-method"),
          targetLabel = $this.data("target-label"),
          $card = $this.closest('.card'),
          $overlay = $card.find('.card-overlay'),
          $overlayLabel = $overlay.find('.label');

      api.comment[method](id)
        .done(function() {
          $overlayLabel.text(targetLabel);
          $card.addClass('processed');
        })
        .fail(function(message) {
          alert(message || 'Oops! Something went wrong...');
        })
        .always(function() {
          $transitionButtons.removeAttr('disabled');
        });

    });

  });

});
