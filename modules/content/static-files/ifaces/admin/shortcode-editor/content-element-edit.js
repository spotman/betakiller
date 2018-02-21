require([
  'jquery',
  'content.api.rpc',
  'ckeditor-post-message',
  'materialize'
], function ($, api, postMessage) {

  let dependencies = {};

  $(function () {
    const $form       = $('#content-element-shortcode-edit-form'),
          shortcodeName = $form.data('name'),
          $attributes = $form.find('.attribute-field');

    $form.find('select').formSelect();

    function findFieldByName(name) {
      const $field = $form.find('#attribute-field-' + name);

      if (!$field.length) {
        throw 'Missing attribute field ' + name + ']';
      }

      return $field;
    }

    function getAttributeName($field) {
      return $field.data('name');
    }

    // Find dependencies` targets
    $attributes.each(function () {
      var $this      = $(this),
          sourceName = getAttributeName($this),
          attrDeps   = $this.data('deps');

      if (!attrDeps) {
        return;
      }

      for (const targetName in attrDeps) {
        if (attrDeps.hasOwnProperty(targetName)) {
          const targetValue = attrDeps[targetName];

          if (!dependencies[targetName]) {
            dependencies[targetName] = {};
          }

          dependencies[targetName][targetValue] = sourceName;
        }
      }
    });

    function showHideDependencies($target) {
      const targetName   = getAttributeName($target),
            valuesToName = dependencies[targetName];

      for (var targetValue in valuesToName) {
        if (!valuesToName.hasOwnProperty(targetValue)) {
          continue;
        }

        const sourceName = valuesToName[targetValue],
              $source    = findFieldByName(sourceName);

        if (targetValue === getControlValue($target)) {
          // Show field
          $source.show();
        } else {
          // Hide field
          $source.hide();
        }
      }
    }

    function getControl($target) {
      const $control = $target.find('select, input');

      if (!$control.length) {
        throw 'Can not find control';
      }

      return $control;
    }

    function getControlValue($target) {
      const $control = getControl($target);

      if ($control.is('[type="checkbox"]')) {
        // Boolean attribute needs string value
        return $control.is(':checked') ? 'true' : 'false';
      }

      return $control.val();
    }

    // Bind event handlers to controls changes and show/hide dependent controls
    for (const targetName in dependencies) {
      if (dependencies.hasOwnProperty(targetName)) {
        const $target  = findFieldByName(targetName),
              $control = getControl($target);

        // Initial set
        showHideDependencies($target);

        // Bind events
        $control.on('change input', function () {
          showHideDependencies($target);
        });
      }
    }

    $form.submit(function (e) {
      e.preventDefault();

      const $button = $form.find('button[type="submit"]'),
            data = {};

      $button.attr('disabled', 'disabled');

      // Select only actual fields (they are visible in UI)
      $attributes.filter(':visible').each(function () {
        const $attr = $(this),
              name  = getAttributeName($attr);

        data[name] = getControlValue($attr);
      });

      console.log(data);

      api.shortcode.verify(shortcodeName, data)
        .done(function(response) {
          postMessage.insertShortcode(shortcodeName, response);
        })
        .fail(function(message) {
          alert(message);
        })
        .always(function() {
          $button.removeAttr('disabled');
        });
    });
  });

});
