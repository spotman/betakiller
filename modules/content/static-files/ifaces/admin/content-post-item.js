require([
  "jquery",
  "content.api.rpc.definition", // ,"materialize.cards"
  'materialize.forms',
  'materialize.charCounter'
], function ($, api) {

  $(function () {

    var $form = $('#post-form');
    var $transitionButtons = $(".transition-button");
    var $saveButton = $(".save-post-button");

    var postID = $form.find('[name="id"]').val();
    var isUpdateAllowed = $form.data('update-allowed');

    var $category = $('#article-category');
    var $content = $('#article-content');

    if ($category.length) {
      $category.material_select();
    }

    if ($content.length) {
      var customTags = $content.data('custom-tags').split(',');
      var customTagsRules = $.map(customTags, function (tag) {
        return tag + "[*]"
      });

      //console.log('custom tags rules are', customTagsRules);

      $.each(customTags, function (index, name) {
        CKEDITOR.dtd[name] = {id: 1};
        CKEDITOR.dtd.$empty[name] = 1;
        CKEDITOR.dtd.$removeEmpty[name] = 0;
        CKEDITOR.dtd.$nonEditable[name] = 1;
        CKEDITOR.dtd.$inline[name] = 1;
      });

      // TODO Move this to dedicated AMD module
      $content.ckeditor({
        contentEntityName: 'ContentPost',
        contentEntityItemID: postID,
        allowedContent: "p(*); strong; i; em; span{*}; table(*); thead; tbody; tr; th[style]; td{*}; ul(*); ol(*); li; a[href,title]; h2; h3; h4; " + customTagsRules.join(';')
      });

      // TODO Fix editor toolbar position
      //$.when($content.ckeditor().promise).then(function () {
      //  var editor = $content.ckeditor().editor;
      //
      //  var $editor = $(editor.container.$),
      //      $toolbar = $editor.find('.cke_top').first();
      //
      //  var toolbarWidth = $toolbar.outerWidth(),
      //      toolbarHeight = $toolbar.outerHeight();
      //
      //  // Create placeholder for simpler toolbar manipulation
      //  var $toolbarPlaceholder = $('<div>').css({
      //    width: toolbarWidth + "px",
      //    height: toolbarHeight + "px"
      //  });
      //
      //  $toolbarPlaceholder.insertAfter($toolbar);
      //
      //  // Move toolbar to form
      //  $form.css({position: "relative"}).append($toolbar);
      //
      //  $toolbar.css({
      //    display: "block",
      //    position: "absolute",
      //    top: "60px",
      //    left: 0,
      //    zIndex: 1000,
      //    width: toolbarWidth + "px",
      //    height: toolbarHeight + "px"
      //  });
      //
      //});
    }

    function savePost(doneCallback) {
      if (!isUpdateAllowed) {
        doneCallback();
        return;
      }

      $saveButton.attr('disabled', 'disabled');

      var formData = {};

      $form.serializeArray().map(function (item) {
        formData[item.name] = item.value;
      });

      api.post.update(formData)
        .done(function () {
          doneCallback();
        })
        .fail(function (message) {
          alert(message || 'Oops! Something went wrong...');
          $saveButton.removeAttr('disabled');
        });
    }

    function processTransition($button) {
      var id     = $button.data("id"),
          method = $button.data("api-method");

      api.post[method](id)
        .done(function () {
          location.reload();
        })
        .fail(function (message) {
          alert(message || 'Oops! Something went wrong...');
          $transitionButtons.removeAttr('disabled');
        });
    }

    $transitionButtons.click(function (e) {
      e.preventDefault();
      $transitionButtons.attr('disabled', 'disabled');

      var $button  = $(this),
          autosave = ($button.data("autosave") === true);

      // Autosave only for selected transitions (except fix, pause, etc)
      if (autosave && isUpdateAllowed) {
        // Save post before processing transition
        savePost(function () {
          processTransition($button);
        });
      } else {
        // Immediately process transition
        processTransition($button);
      }
    });

    $form.submit(function (e) {
      e.preventDefault();

      if (!isUpdateAllowed) {
        return;
      }

      savePost(function () {
        $saveButton.removeAttr('disabled');
      });
    });

    var $bar           = $('#admin-bar'),
        $previewButton = $bar.find('.preview-entity-button');

    // Save post before sending user to a preview
    if ($previewButton.length) {
      $previewButton.click(function (e) {
        e.preventDefault();

        savePost(function () {
          location.href = $previewButton.data('href');
        });

        return false;
      });
    }

  });

});
