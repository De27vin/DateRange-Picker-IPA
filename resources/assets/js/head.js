window.latestTotalCount = null;

window.addEventListener('total_count_updated', (event) => {
    window.latestTotalCount = event.detail.total;
});