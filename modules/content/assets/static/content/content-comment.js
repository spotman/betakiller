require([
  "jquery",
  "jquery.utils",
  "materialize"
], function ($) {

  $(function () {
    var $commentsContainer = $('#comments-container'),
        $form              = $("#content-comment-form"),
        $name              = $form.find('input[name="name"]'),
        $email             = $form.find('input[name="email"]'),
        $message           = $form.find('textarea[name="message"]'),
        $entity            = $form.find('input[name="entity"]'),
        $entityItemId      = $form.find('input[name="entityItemId"]'),
        $parentInput       = $form.find('input[name="parent"]'),
        $submitButton      = $form.find('button[type="submit"]'),
        $cancelButton      = $form.find('.cancel-button'),
        $replyButtons      = $('.content-comment').find('.comment-reply-button');

    $(window).on("load", function () {
      var hash            = window.location.hash,
          commentIdPrefix = "content-comment-";

      // Detect link to comment and scroll
      if ($commentsContainer.length && hash && hash.indexOf(commentIdPrefix) >= 0) {
        var $commentItem = $(hash);

        if ($commentItem.length) {
          setTimeout(function () {
            $commentItem.addClass('highlight');
            var offset = $commentItem.offset().top - 60;
            console.log("scrolling to comment", hash, "with offset", offset);
            $('body').scrollTop(offset);
          }, 500);
        }
      }
    });

    $message.characterCounter();

    function setParent(id) {
      $parentInput.val(id);
    }

    function resetParent() {
      $parentInput.val(0);
    }

    // Clear parent comment id if message length = 0
    $message.on('input change', function () {
      if (!$message.val()) {
        resetParent();
      }
    });

    $replyButtons.click(function (e) {
      e.preventDefault();

      var $this                   = $(this),
          $commentBlock           = $this.closest('.content-comment'),
          parentID                = $commentBlock.data('id'),
          $parentComment          = $("#content-comment-" + parentID),
          parentCommentAuthorName = $parentComment.find('.comment-author').text();

      $form.appendTo($commentBlock);

      // Set parent comment id on "reply" click + put parent comment author name into message
      setParent(parentID);
      $message.val($message.val() + parentCommentAuthorName + ', ');
      $message.focus();
    });

    $cancelButton.click(function (e) {
      e.preventDefault();
      resetParent();
      $form.appendTo($('#content-comment-block'));
    });

    $form.submit(function (e) {
      e.preventDefault();

      $submitButton.attr("disabled", "disabled");

      var data = {
        name: $name.val(),
        email: $email.val(),
        message: $message.val(),
        parent: $parentInput.val(),
        entity: $entity.val(),
        entityItemId: $entityItemId.val()
      };

      $form.JSON($form.attr('action'), data)
        .done(function () {
          alert($form.data('success-text'));
          $form[0].reset();
        })
        .fail(function (message) {
          alert(message || $form.data('error-text'));
        })
        .always(function () {
          $submitButton.removeAttr("disabled");
        });
    });
  });

});
