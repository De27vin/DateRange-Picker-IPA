require('./bootstrap');
require('./globalUtils');
// require('../../../vendor/bastinald/laravel-livewire-loader/resources/js/loader');
// require('livewire-sortable')

// import '@ryangjchandler/spruce'
// import Fuse from 'fuse.js'
import 'alpine-magic-helpers'
import 'alpinejs'
import 'livewire-sortable'
window.Sortable = require('sortablejs').default
const notification = document.querySelector('div.notify')

if (notification) {
    setTimeout(() => {
        notification.remove()
    }, notify.timeout )
}
Alpine.start();
