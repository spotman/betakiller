require([
  "jquery",
  "content.api.rpc.definition",
  'materialize',
  'ckeditor.jquery'
], function ($, api) {

  const shortcodeToCustomTag = {
    "image": "photo"
  };

  $(function () {

    const $form = $('#post-form');
    const $transitionButtons = $(".transition-button");
    const $saveButton = $(".save-post-button");

    const isUpdateAllowed = $form.data('update-allowed');

    const $category = $('#article-category');
    const $content = $('#article-content');

    if (!$content.length) {
      throw new Error('Missing content element');
    }

    if ($category.length) {
      $category.select();
    }

    // TODO Move this to dedicated AMD module

    const shortcodes = $content.data('shortcodes').split(',');
    const customTagsRules = $.map(shortcodes, function (shortcode) {
      return getShortcodeCustomTag(shortcode) + "[*]"
    });

    // Replace original shortcodes with CKEditor custom tags
    $content.val(convertShortcodesToCustomTags($content.val(), shortcodes));

    //console.log('custom tags rules are', customTagsRules);

    $.each(shortcodes, function (index, name) {
      window.CKEDITOR.dtd[name] = {id: 1};
      window.CKEDITOR.dtd.$empty[name] = 1;
      window.CKEDITOR.dtd.$removeEmpty[name] = 0;
      window.CKEDITOR.dtd.$nonEditable[name] = 1;
      window.CKEDITOR.dtd.$inline[name] = 1;
    });

    const entitySlug = $content.data('entity-slug'),
          entityItemId = $content.data('entity-item-id');

    if (!entitySlug || !entityItemId) {
      throw new Error('Entity slug or entity item id is missing');
    }

    $content.ckeditor({
      contentEntityName: entitySlug,
      contentEntityItemID: entityItemId,
      allowedContent: "p(*); strong; i; em; span{*}; table(*); thead; tbody; tr[style]; th[style]; td{*}; ul(*); ol(*); li; a[href,title]; h2; h3; h4; " + customTagsRules.join(';')
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

    function savePost(doneCallback) {
      if (!isUpdateAllowed) {
        doneCallback();
        return;
      }

      $saveButton.attr('disabled', 'disabled');

      const formData = {};

      $form.serializeArray().map(function (item) {
        formData[item.name] = item.value;
      });

      // Replace CKEditor custom tags with original shortcodes
      formData.content = convertCustomTagsToShortcodes(formData.content, shortcodes);

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
      const id     = $button.data("id"),
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

      const $button  = $(this),
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

    const $bar           = $('#admin-bar'),
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

  function getShortcodeCustomTag(shortcodeName) {
    return shortcodeToCustomTag[shortcodeName] || shortcodeName;
  }

  function convertShortcodesToCustomTags(input, shortcodes) {
    const matches = input.match(/\[(\S+)\s*([^\/\]]*)\/\]/ig);

    $.each(matches, function(i, original) {
      $.each(shortcodes, function (j, shortcodeName) {
        const regex = new RegExp("\\[" + shortcodeName);

        if (regex.test(original)) {
          const tagName = getShortcodeCustomTag(shortcodeName);
          const updated = original
            .replace("[" + shortcodeName, "<" + tagName)
            .replace("[/" + shortcodeName, "</" + tagName)
            .replace("/]", "/>");

          input = input.replace(original, updated);
        }
      });
    });

    return input;
  }

  function convertCustomTagsToShortcodes(input, shortcodes) {
    const matches = input.match(/\<(\S+)\s*([^\/\>]*)\/\>/ig);

    $.each(matches, function(i, original) {
      $.each(shortcodes, function (j, shortcodeName) {
        const tagName = getShortcodeCustomTag(shortcodeName);
        const regex = new RegExp("\\<" + tagName);

        if (regex.test(original)) {
          const updated = original
            .replace("<" + tagName, "[" + shortcodeName)
            .replace("</" + tagName, "[/" + shortcodeName)
            .replace("/>", "/]");

          input = input.replace(original, updated);
        }
      });
    });

    return input;
  }

});
