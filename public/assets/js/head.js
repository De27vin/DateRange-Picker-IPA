/******/ (() => { // webpackBootstrap
/*!*************************************!*\
  !*** ./resources/assets/js/head.js ***!
  \*************************************/
window.latestTotalCount = null;
window.addEventListener('total_count_updated', function (event) {
  window.latestTotalCount = event.detail.total;
});
/******/ })()
;