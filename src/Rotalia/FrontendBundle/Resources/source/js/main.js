(function () {
    //hide the skeleton
    window.addEventListener('kassa-app-loaded', function () {
        document.body.className = '';
    });

    // load pre-caching service worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js').then(function (reg) {
            reg.onupdatefound = function () {
                let installingWorker = reg.installing;
                installingWorker.onstatechange = function () {
                    switch (installingWorker.state) {
                        case 'installed':
                            if (navigator.serviceWorker.controller) {
                                console.log('SW: New or updated content is available.');
                                sessionStorage.setItem('cacheUpdate', 'true');
                                location.reload();
                            } else {
                                console.log('SW: Content is now available offline!');
                            }
                            break;
                        case 'redundant':
                            console.error('SW: The installing service worker became redundant.');
                            break;
                    }
                }
            }
        }).catch(function (e) {
            console.error('SW: Error during service worker registration:', e);
        });
    }
})();
