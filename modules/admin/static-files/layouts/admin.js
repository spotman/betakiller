require([
  "jquery",
  "materialize"
], function ($, M) {

  $(function () {
    var elems = document.querySelectorAll('.sidenav');
    M.Sidenav.init(elems);

    // Keep automatic initialization after moving to v1.0
    var collapsibleElem = document.querySelector('.collapsible');
    M.Collapsible.init(collapsibleElem);
  });

});
