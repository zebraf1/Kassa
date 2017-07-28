(function() {
    // setup Polymer options
    window.Polymer = {lazyRegister: true, dom: 'shadow'};

    // load webcomponents polyfills
    var webComponentsSuported = 'registerElement' in document
        && 'import' in document.createElement('link')
        && 'content' in document.createElement('template');

    if (!webComponentsSuported) {
        var script = document.createElement('script');
        script.async = true;
        script.src = 'bower_components/webcomponentsjs/webcomponents-lite.min.js';
        document.head.appendChild(script);
    }
})();
