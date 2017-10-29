module.exports = {
    staticFileGlobs: [
        '/index.html',
        '/elements/*',
        '/js/*',
        '/images/icons/*',
        '/manifest.json',
        '/bower_components/webcomponentsjs/webcomponents-loader.js',
        '/bower_components/webcomponentsjs/custom-elements-es5-adapter.js'
    ],
    navigateFallbackWhitelist: [/^\/$/]
};
