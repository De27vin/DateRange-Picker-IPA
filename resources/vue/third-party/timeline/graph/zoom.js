import * as d3 from 'd3'

export default (config) => {
    let {
        timeScale,
        view,
        draw,
        width,
        startDate,
        endDate
    } = config

    const minZoom = 1;
    const maxZoom = 100000000;

    const extent = [[0, 0], [width, 0]];

    return d3.zoom()
        .scaleExtent([minZoom, maxZoom])
        .translateExtent(extent)
        .extent(extent)
        .on('zoom', () => {
            let { k, x } = d3.event.transform;

            let newScale = d3.zoomIdentity
                .translate(x, 0)
                .scale(k)
                .rescaleX(timeScale);

            if (newScale.domain()[0] < startDate) {
                newScale.domain([startDate, newScale.domain()[1]]);
            }
            if (newScale.domain()[1] > endDate) {
                newScale.domain([newScale.domain()[0], endDate]);
            }

            view.call(draw(newScale));
        });
}