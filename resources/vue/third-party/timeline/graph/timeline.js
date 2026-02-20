import * as d3 from 'd3'

import events from './events'
import axis from './axis'
import zoom from './zoom'
import cursor from './cursor'
import layout from './layout'

//import { toRoman } from 'roman-numerals'

export default (config) => {
    function init(selection) {

        let {
            viewWidth = 800,
            viewHeight = 200,
            widthResizable = true,
            heightResizable = true,
            margin,
            onEventClick,
            showCursor = true,
            levels,
            identifier,
            startDate,
            endDate,
            translations,
        } = config


        // console.log('timeline init')
        selection.selectAll('svg').remove()

        let data = selection.data()

        let events = data[0]
        layout.generate(events, levels)
        console.table(events)

        if (widthResizable) {
            viewWidth = selection.node().clientWidth
        }
        if (heightResizable) {
            viewHeight = selection.node().clientHeight
        }

        let width = viewWidth - margin.right - margin.left
        let height = viewHeight - margin.top - margin.bottom

        let svg = selection
                    .append('svg')
                    .classed('timeline', true)
                    .datum(data)
                    .attr('width', width + margin.right + margin.left)
                    .attr('height', height + margin.top + margin.bottom)
                    .attr('viewBox', margin.left + ' 0 ' + width + ' ' + height)

        let endDateCopy = new Date(endDate)

        // endDateCopy.setDate(endDate.getDate() + 1);
        endDateCopy.setHours(endDate.getHours() + 12);

        let timeScale = d3.scaleTime()
            // .domain([
            //     d3.min(events.map(e => e.start)),
            //     d3.max(events.map(e => e.end))
            // ])
            .domain([startDate, endDateCopy])
            .range([0, width])
            // .range([width, 0])

        let graph = svg
                    .append('g')
                    .classed('graph', true)
                    .attr('transform', `translate(${margin.left},${margin.top})`)

        let view = graph.append('g')
            .classed('view', true)

        svg.call(zoom({
            timeScale,
            view,
            draw,
            width,
            startDate,
            endDateCopy
        }))

        view.call(draw(timeScale, onEventClick, height, showCursor, identifier, translations))
    }

    function chart(selection) {
        console.log('timeline constructor')
        chart._init = () => init(selection)
        chart._init()

        if (config.widthResizable) {
            global.addEventListener('resize', chart._init, true)
        }
    }

    function draw(timeScale, onEventClick, height, showCursor, identifier, translations) {
        let axisOffset = height - 22

        return selection => {
            selection
                .data(selection.data())
                .call(events({
                    timeScale,
                    onEventClick,
                    axisOffset,
                    identifier,
                    translations
                }))
                .call(axis({
                    timeScale,
                    axisOffset
                }))
                // .call(cursor({
                //     showCursor,
                //     timeScale,
                //     height,
                // }))
        }
    }

    return chart

}