window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.moment = require('moment');
window.Pikaday = require("pikaday");
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

window.Pusher = require('pusher-js');

import Echo from 'laravel-echo';

function initializeEcho() {
    const reverbHost = document.querySelector('meta[name="reverb-host"]')?.getAttribute('content');
    const reverbPort = document.querySelector('meta[name="reverb-port"]')?.getAttribute('content');
    const reverbKey = document.querySelector('meta[name="reverb-key"]')?.getAttribute('content');

    if (!reverbHost || !reverbPort || !reverbKey) {
        console.error('Missing Reverb configuration in meta tags: host, port, or key');
        return false;
    }

    try {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: parseInt(reverbPort),
            wssPort: parseInt(reverbPort),
            forceTLS: window.location.protocol === 'https:',
            enabledTransports: ['ws', 'wss'],
        });

        console.log('Laravel Echo initialized with Reverb', {
            host: reverbHost,
            port: reverbPort,
            secure: window.location.protocol === 'https:'
        });

        // Listen to connection state changes
        window.Echo.connector.pusher.connection.bind('state_change', (states) => {
            if (states.current === 'connected') {
                console.log('Connected to WebSocket server');
            } else if (states.current === 'disconnected') {
                console.warn('Disconnected from WebSocket server');
            } else if (states.current === 'connecting') {
                console.log('Connecting to WebSocket server...');
            }
        });

        return true;
    } catch (error) {
        console.error('Failed to initialize Laravel Echo:', error);
        return false;
    }
}

// Initialize Echo immediately when DOM is ready, before Alpine starts
document.addEventListener('DOMContentLoaded', function() {
    initializeEcho();
});
