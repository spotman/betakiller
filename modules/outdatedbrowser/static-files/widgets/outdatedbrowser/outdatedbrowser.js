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
  var outdated = document.getElementById('outdated');
  var langCode = outdated.getAttribute('data-lang_code');
  outdatedBrowser({
    bgColor: '#f25648',
    color: '#ffffff',
    lowerThan: 'transform',
    languagePath: '/assets/static/widgets/outdatedbrowser/vendor/lang/'+langCode+'.html'
  })
});
