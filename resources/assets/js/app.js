require('./bootstrap');
require('./globalUtils');
require('./realtime');
// require('../../../vendor/bastinald/laravel-livewire-loader/resources/js/loader');
// require('livewire-sortable')

// import '@ryangjchandler/spruce'
// import Fuse from 'fuse.js'
import 'alpine-magic-helpers'
import 'alpinejs'
import 'livewire-sortable'
window.Sortable = require('sortablejs').default
const notification = document.querySelector('div.notify')

require('./export-handler')

if (notification) {
    setTimeout(() => {
        notification.remove()
    }, notify.timeout )
}

Alpine.start();
