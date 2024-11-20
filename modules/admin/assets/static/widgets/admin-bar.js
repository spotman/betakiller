require([
  'jquery',
  'materialize'
], function($) {

  $(function() {
    var $bar = $('#admin-bar'),
        $createDropdownTrigger = $bar.find('a[data-target="admin-bar-create-dropdown"]');

    $createDropdownTrigger.dropdown({
      hover: true,
      constrainWidth: false,
      coverTrigger: false
    });
  });

});
