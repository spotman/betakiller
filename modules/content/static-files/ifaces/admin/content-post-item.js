require([
  "jquery",
  "content.api.rpc.definition" // ,"materialize.cards"
], function($, api) {

  $(function() {

    var $form = $('#post-form');
    var $transitionButtons = $(".transition-button");
    var $saveButton = $(".save-post-button");

    function savePost(doneCallback) {
      $saveButton.attr('disabled', 'disabled');

      var formData = {};

      $form.serializeArray().map(function(item){
        formData[item.name] = item.value;
      });

      api.post.update(formData)
        .done(function() {
          doneCallback();
        })
        .fail(function(message) {
          alert(message || 'Oops! Something went wrong...');
          $saveButton.removeAttr('disabled');
        });
    }

    function processTransition($button) {
      var id = $button.data("id"),
          method = $button.data("api-method");

      api.post[method](id)
        .done(function() {
          location.reload();
        })
        .fail(function(message) {
          alert(message || 'Oops! Something went wrong...');
          $transitionButtons.removeAttr('disabled');
        });
    }

    $transitionButtons.click(function(e) {
      e.preventDefault();
      $transitionButtons.attr('disabled', 'disabled');

      var $button = $(this),
          autosave = ($button.data("autosave") === true);

      // Autosave only for selected transitions (except fix, pause, etc)
      if (autosave) {
        // Save post before processing transition
        savePost(function() {
          processTransition($button);
        });
      } else {
        // Immediately process transition
        processTransition($button);
      }
    });

    $form.submit(function(e) {
      e.preventDefault();

      savePost(function() {
        $saveButton.removeAttr('disabled');
      });
    });

  });

});
