//event listener: DOM ready
function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload !== 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      if (oldonload) {
        oldonload();
      }
      func();
    }
  }
}

//call plugin function after DOM ready
addLoadEvent(function(){
  var container = document.getElementById('outdated');
  var langName = container.getAttribute('data-lang_name');
  outdatedBrowser({
    bgColor: '#f25648',
    color: '#ffffff',
    lowerThan: 'transform',
    languagePath: '/assets/static/outdated-browser/widgets/dist/lang/'+langName+'.html'
  })
});
