define([], function() {
  const target = window.top;

  function sendMessage(name, data) {
    let message = data || {};
    message.name = name;

    target.postMessage(message, '*'); // TODO security
    console.log('postMessage sent', message);
  }

  return {
    insertText: function(text) {
      sendMessage("CKEditorInsertText", {text: text});
    },
    insertShortcode: function(tagName, attributes) {
      sendMessage("CKEditorInsertShortcode", {
        tagName: tagName,
        attributes: attributes
      });
    }
  };
});
