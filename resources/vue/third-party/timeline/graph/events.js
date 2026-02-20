import * as d3 from 'd3'

export default config => selection => {
    let {
        timeScale,
        onEventClick,
        axisOffset,
        identifier,
        translations
    } = config

    let eventsData = selection.data()[0][0].slice().sort((a, b) => a.start - b.start);
    let events = selection.selectAll('g.event').data(eventsData)

    let distance = null
    const browser = getBrowser()
    if (browser === 'Firefox') {
        distance = 36
    } else {
        distance = 50
    }
    

    let g = events
        .enter()
        .append('g')
        .classed('event', true)
        .attr('transform', d => `translate(${timeScale(d.start)} ${d.position * distance})`)
        .on('click', onEventClick)

    const tooltip = d3.select('#'+identifier+'_tooltip').append('div')
        .attr('class', 'tooltip')
        .style('position', 'absolute')
        .style('padding', '10px')
        .style('background', 'white')
        .style('border', '1px solid gray')
        .style('border-radius', '5px')
        .style('pointer-events', 'none')
        .style('opacity', 0)
        .style('z-index', 10)
        .style('white-space', 'nowrap')

    // g.append('rect')
    //     .attr('width', d => d.end ? timeScale(d.end)-timeScale(d.start) : 10)
    //     .attr('height', 20)
    //     .attr('fill', d => d.color)
    //     .attr('ry', 6)

    // g.append('line')
    //     .attr('x1', 0)
    //     .attr('y1', 0)
    //     .attr('x2', 0)
    //     .attr('y2', d => axisOffset - (d.position * distance) + ((4-d.position)*19) + 12)
    //     .attr('stroke', d => d.color)

    const circleSelection = g.append('circle')
        .attr('cy', 0)
        .attr('fill', 'white')
        .attr('stroke', d => d.color)
        .style('cursor', 'pointer')
        .on('mouseover', function(d, i, nodes) {
            const circle = nodes[i];
            const rect = circle.getBoundingClientRect();

            tooltip.transition().style('opacity', 1);
            tooltip.html(`
                <strong>${translations?.[d.type] || d.type}</strong><br/>
                ${translations?.['Start'] || 'Start'}: ${formatDate(d.start)}<br/>
                ${translations?.['End'] || 'End'}: ${formatDate(d.end)}<br/>
                ${translations?.['Duration'] || 'Duration'}: ${d.durationCustom}<br/>
                ${translations?.['Session ID'] || 'Session ID'}: ${d.ses_id}
            `)
            .style('left', `${d3.event.pageX - (100000/d3.event.pageX)}px`)
            .style('top', `${d.position*distance - 220}px`)
        })
        .on('mouseout', function() {
            tooltip.transition().style('opacity', 0)
        })
        .on('click', function(d) {
            window.open(d.url, '_blank');
        })

    const textSelection = g.append('text')
        .attr('dy', 7)
        .attr("text-anchor", "middle")
        .attr('fill', d => d.color)
        .style('pointer-events', 'none')
        .classed('f7-icons', true)
        .text(d => d.icon)

    circleSelection.attr('r', 15)
    circleSelection.attr('stroke-width', 2)
    textSelection.style('font-size', '1.4rem')

    if (browser === 'Firefox') {
        textSelection.attr('dy', 5)
    } else {
        textSelection.attr('dy', 8)
    }

    events
        .attr('transform', d => `translate(${timeScale(d.start)} ${d.position * distance})`)
        .selectAll('rect')
        .attr('width', d => d.end ? timeScale(d.end)-timeScale(d.start) : 10)
}

function formatDate(date) {
  let day = date.getDate().toString().padStart(2, '0');
  let month = (date.getMonth() + 1).toString().padStart(2, '0');
  let year = date.getFullYear();
  let hours = date.getHours().toString().padStart(2, '0');
  let minutes = date.getMinutes().toString().padStart(2, '0');
  let seconds = date.getSeconds().toString().padStart(2, '0');

  let timezoneOffset = -date.getTimezoneOffset() / 60;
  let timezone = `GMT${timezoneOffset >= 0 ? '+' : ''}${timezoneOffset}`;

  let options = { timeZoneName: 'long' };
  let formattedTimezone = new Intl.DateTimeFormat('en-US', options).formatToParts(date).find(part => part.type === 'timeZoneName').value;

  return `${day}.${month}.${year} ${hours}:${minutes}:${seconds} ${timezone} (${formattedTimezone})`;
}