define([
  "materialize"
], function(M) {

  function show(message, timeout, classes) {
    M.toast({
      html: message,
      displayLength: timeout || 3000,
      classes: classes
    });
  }

  return {
    success: function(message, timeout) {
      show(message, timeout || 3000, 'green');
    },
    error: function(message, timeout) {
      show(message, timeout || 5000, 'red');
    }
  };
});
