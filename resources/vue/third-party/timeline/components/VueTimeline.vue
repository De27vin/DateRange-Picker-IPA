<template>
  <div>
    <div :id="identifier+'_tooltip'" style="position: relative;"></div>
    <div :id="identifier" @wheel="onWheel"></div>
  </div>
</template>

<script>
import * as d3 from 'd3'

import timeline from '../graph/timeline'

export default {
    props: {
        data: {
            type: Array,
            required: true
        },
        width: {
            type: Number
        },
        height: {
            type: Number
        },
        widthResizable: {
            type: Boolean,
            default: true
        },
        heightResizable: {
            type: Boolean,
            default: false
        },
        levels: {
            type: Number,
            default: 1
        },
        identifier: {
            type: String,
            default: 'timeline'
        },
        config: {
            type: Object,
            default: () => {}
        },
        start: {
            type: Date,
        },
        end: {
            type: Date,
        },
        translations: {
            type: Object,
        }
    },

    methods: {
      initTimeline(data, start, end) {
        d3.select('#'+this.identifier)
            .datum(data || this.data)
            .call(timeline({
                    widthResizable: this.widthResizable,
                    heightResizable: this.heightResizable,
                    levels: this.levels,
                    viewHeight: this.height,
                    viewWidth: this.width,
                    margin: {
                        top: 0,
                        bottom: 0,
                        left: 20,
                        right: 20
                    },
                    onEventClick: this.config?.onEventClick,
                    identifier: this.identifier,
                    startDate: start || this.start,
                    endDate: end || this.end,
                    translations: this.translations || {}
                }))
      },

      onWheel(event) {
        const timelineElement = document.getElementById(this.identifier);
        const zoomTransform = d3.zoomTransform(timelineElement);
        const { k } = zoomTransform;

        if (k <= 1) {
          event.preventDefault();
        }
      }
    },

  mounted() {
    this.initTimeline();

    const timelineElement = document.getElementById(this.identifier);
    timelineElement.addEventListener('wheel', this.onWheel, { passive: false });
  },

}
</script>

<style>

.timeline {
    background-color: white;
    border-radius: 5px;
    border: 1px solid transparent;
    overflow: hidden;
}

.axe {

}

.loading-indicator {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: rgba(255, 255, 255, 0.8);
  padding: 10px;
  border-radius: 5px;
  font-size: 1.2em;
  color: #000;
}

.timeline-tooltip {
    position: absolute;
    padding: 10px;
    background: lightgray;
    border: 1px solid gray;
    border-radius: 5px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s;
}
</style>