jQuery(function () {
  var bLazy = new Blazy({
    cloudinary: true,
    src: "data-src",
    success: function (element) {
      //console.log(element);
      setTimeout(function () {
        //alert("sgsg");
        // We want to remove the loader gif now.
        // First we find the parent container
        // then we remove the "loading" class which holds the loader image
        var parent = element.parentNode;
        parent.className = parent.className.replace(/\bloading\b/, '');
      }, 500);
    },
    error: function (ele, msg) {
      if (msg === 'missing') {
        // Data-src is missing
      }
      else if (msg === 'invalid') {
        // Data-src is invalid
      }
      //console.log(ele);
      ele.removeAttribute('src');
    }
  });
  /*window.bLazy = new Blazy({
   container: '.container',
   success: function (element) {
   console.log("Element loaded: ", element.nodeName);
   }
   });*/
});