import * as d3 from 'd3'

function format(date) {
    if (d3.timeDay(date) < date) {
        return d3.timeFormat('%H:%M:%S')(date)
    }

    if (d3.timeMonth(date) < date) {
        return d3.timeFormat('%b %d')(date)
    }

    if (d3.timeYear(date) < date) {
        return d3.timeFormat('%B')(date)
    }

    return d3.timeFormat('%Y')(date)
}

export default (config) => selection => {
    let {
        timeScale,
        axisOffset,
    } = config

    let axe = selection.selectAll('.axe').data(d => d)

    let ay = d3.axisBottom()
            .scale(timeScale)
            .tickFormat(d => format(d))

    let axisOffsetX = 0

    const browser = getBrowser()
    if (browser === 'Firefox') {
        axisOffset = 0.71 * axisOffset
        axisOffsetX = axisOffsetX - 5
    }



    console.log('pixelRatio', window.devicePixelRatio)

    axe.enter()
        .append('g')
        .attr('transform', `translate(${axisOffsetX}, ${axisOffset})`)
        .classed('axe', true)
        .call(ay)

    axe.call(ay)
}