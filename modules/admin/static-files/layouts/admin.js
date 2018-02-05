require([
  "jquery",
  "materialize"
], function($) {

  $(function() {
    $('.sidenav').sidenav();

    // Keep automatic initialization after moving to v1.0
    $('.collapsible').collapsible();
  });

});
