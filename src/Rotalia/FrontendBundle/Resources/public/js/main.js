(function() {
	// setup Polymer options
	window.Polymer = {lazyRegister: true, dom: 'shadow'};

	//hide the skeleton
	window.addEventListener('kassa-app-loaded', function() {
		document.body.className = '';
	});

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

	// load pre-caching service worker
	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register('service-worker.js').then(function(reg) {
			reg.onupdatefound = function() {
				var installingWorker = reg.installing;
				installingWorker.onstatechange = function() {
					switch (installingWorker.state) {
							case 'installed':
								if (navigator.serviceWorker.controller) {
									console.log('SW: New or updated content is available.');
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
		}).catch(function(e) {
			console.error('SW: Error during service worker registration:', e);
		});
	}
})();
