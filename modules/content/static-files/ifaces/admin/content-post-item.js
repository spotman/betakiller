require([
  "jquery",
  "content.api.rpc.definition" // ,"materialize.cards"
], function($, api) {

  $(function() {

    var $form = $('#post-form');
    var $transitionButtons = $(".transition-button");
    var $saveButton = $(".save-post-button");

    $transitionButtons.click(function(e) {
      e.preventDefault();
      $transitionButtons.attr('disabled', 'disabled');

      var $this = $(this),
          id = $this.data("id"),
          method = $this.data("api-method");

      api.post[method](id)
        .done(function() {
          location.reload();
        })
        .fail(function(message) {
          alert(message || 'Oops! Something went wrong...');
          $transitionButtons.removeAttr('disabled');
        });
    });

    $form.submit(function(e) {
      e.preventDefault();
      $saveButton.attr('disabled', 'disabled');

      var formData = {};

      $form.serializeArray().map(function(item){
        formData[item.name] = item.value;
      });

      api.post.update(formData)
        .done(function() {
        })
        .fail(function(message) {
          alert(message || 'Oops! Something went wrong...');
        })
        .always(function() {
          $saveButton.removeAttr('disabled');
        });
    });

  });

});
