window.addEventListener("load", function () {
  var content = document.getElementById("cookies_notification");
  window.cookieconsent.initialise({
    content:    {
      header:  content.getAttribute('data-header'),
      message: content.getAttribute('data-message'),
      dismiss: content.getAttribute('data-dismiss'),
      link:    content.getAttribute('data-link'),
      href:    content.getAttribute('data-href'),
      target:  content.getAttribute('data-target'),
    },
    "palette": {
      "popup": {
        "background": "#efefef",
        "text": "#404040"
      },
      "button": {
        "background": "#f7902c"
      }
    },
    "theme": "classic",
    "position": "bottom-right"
  });
});
