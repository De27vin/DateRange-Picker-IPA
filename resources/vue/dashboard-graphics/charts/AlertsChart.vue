<template>
    <div class="chart-container">
        <div class="top-controls">
            <!-- Date range picker -->
            <div class="date-picker-wrap">
                <date-picker 
                  v-model="dateRange" range 
                  :clearable="false" 
                  :editable="false" 
                  format="DD.MM.YYYY"
                  value-type="date" 
                  :disabled-date="disableFuture" 
                  :append-to-body="false"
                  range-separator=" - " 
                  input-class="date-range-input"
                  popup-class="single-panel-range force-below-popup" 
                  @change="onDateRangeChange"
                />
                <div v-if="dateError" class="date-error">{{ dateError }}</div>
                <div v-if="fetchError" class="date-error">{{ fetchError }}</div>
            </div>

            <div class="resolution-selector">

            </div>
        </div>

        <!-- Filter button -->
        <button class="filter-btn" @click="showFilters = true">
            Filters
        </button>

        <canvas ref="chart"></canvas>

        <!-- Filters pop-up -->
        <div v-if="showFilters" class="filter-overlay">
            <div class="filter-modal">
                <button class="close-btn" @click="showFilters = false">✕</button>

                <div class="filter-grid">
                    <div v-for="(alert, i) in filterAlerts" :key="i" class="filter-cell">
                        <template v-if="alert">
                            <input type="checkbox" :value="alert.key" v-model="selectedAlerts" />
                            <span>{{ alert.label }}</span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import Chart from 'chart.js'
import { normalizeHourlyTimeseries } from '../../../../utils/timeseries'
import { aggregateTimeseries, pickResolution } from '../../../js/utils/timeseriesAggregation'
import {
    daysInRangeUtc,
    disableFutureUtc,
    toIso8601Utc,
    toYmdUtc,
    validateAndNormalizeRange,
} from '../../../js/utils/timeseriesRangeValidation'
import DatePicker from 'vue2-datepicker'
import 'vue2-datepicker/index.css'


const ALERT_DEFS = [
    { key: 'Active_alarm', label: 'Active alarm', color: '#ff7a18', prop: 'liveActiveAlarm' },
    { key: 'Battery_malfunction', label: 'Battery malfunction', color: '#e11d48', prop: 'liveBatteryMalfunction' },
    { key: 'Battery_low', label: 'Battery low', color: '#10b981', prop: 'liveBatteryLow' },
    { key: 'Button_malfunction', label: 'Button malfunction', color: '#3b82f6', prop: 'liveButtonMalfunction' },
    { key: 'Charge_malfunction', label: 'Charge malfunction', color: '#8b5cf6', prop: 'liveChargeMalfunction' },
    { key: 'Database_malfunction', label: 'Database malfunction', color: '#f59e0b', prop: 'liveDatabaseMalfunction' },
    { key: 'Disk_low', label: 'Disk low', color: '#ef4444', prop: 'liveDiskLow' },
    { key: 'Object_door_failure', label: 'Object door failure', color: '#22c55e', prop: 'liveObjectDoorFailure' },
    { key: 'Elevator_failure', label: 'Elevator failure', color: '#3b82f6', prop: 'liveElevatorFailure' },
    { key: 'Gateway_malfunction', label: 'Gateway malfunction', color: '#8b5cf6', prop: 'liveGatewayMalfunction' },
    { key: 'Identity_mismatch', label: 'Identity mismatch', color: '#f59e0b', prop: 'liveIdentityMismatch' },
    { key: 'Line_alarm', label: 'Line alarm', color: '#ef4444', prop: 'liveLineAlarm' },
    { key: 'Location_alarm', label: 'Location alarm', color: '#22c55e', prop: 'liveLocationAlarm' },
    { key: 'Object_is_under_maintenance', label: 'Object under maintenance', color: '#3b82f6', prop: 'liveObjectIsUnderMaintenance' },
    { key: 'Microphone_malfunction', label: 'Microphone malfunction', color: '#8b5cf6', prop: 'liveMicrophoneMalfunction' },
    { key: 'Network_malfunction', label: 'Network malfunction', color: '#f59e0b', prop: 'liveNetworkMalfunction' },
    { key: 'Periodical_call_overdue', label: 'Periodical call overdue', color: '#ef4444', prop: 'livePeriodicalCallOverdue' },
    { key: 'Pin_mismatch', label: 'Pin mismatch', color: '#22c55e', prop: 'livePinMismatch' },
    { key: 'Power_malfunction', label: 'Power malfunction', color: '#3b82f6', prop: 'livePowerMalfunction' },
    { key: 'Ram_low', label: 'Ram low', color: '#8b5cf6', prop: 'liveRamLow' },
    { key: 'Reserved_device', label: 'Reserved device', color: '#f59e0b', prop: 'liveReservedDevice' },
    { key: 'Serial_port_malfunction', label: 'Serial port malfunction', color: '#ef4444', prop: 'liveSerialPortMalfunction' },
    { key: 'Shaft_failure', label: 'Shaft failure', color: '#22c55e', prop: 'liveShaftFailure' },
    { key: 'Low_signal', label: 'Low signal', color: '#3b82f6', prop: 'liveLowSignal' },
    { key: 'Sip_registration_failure', label: 'Sip registration failure', color: '#8b5cf6', prop: 'liveSipRegistrationFailure' },
    { key: 'Speaker_malfunction', label: 'Speaker malfunction', color: '#f59e0b', prop: 'liveSpeakerMalfunction' },
    { key: 'Technician_check_overdue', label: 'Technician check overdue', color: '#ef4444', prop: 'liveTechnicianCheckOverdue' },
    { key: 'Voice_alarm', label: 'Voice alarm', color: '#22c55e', prop: 'liveVoiceAlarm' },
]

// Function: Interpolate between two colors of the Serv24 colour gradient
function interpolateColor(start, end, factor) {
    return start.map((s, i) =>
        Math.round(s + factor * (end[i] - s))
    )
}

// Function: Generate gradient color between start and end colors
function gradientColor(index, total) {
    const start = [23, 44, 81] // Blue
    const end = [162, 35, 42] // Red

    if (total <= 1) return `rgba(${start.join(',')},1)`

    const factor = index / (total - 1)
    const [r, g, b] = interpolateColor(start, end, factor)

    return `rgba(${r}, ${g}, ${b}, 1)`
}

export default {
    name: 'AlertsChart',
    components: { DatePicker },

    props: {
        'live-active-alarm': Number,
        'live-battery-malfunction': Number,
        'live-battery-low': Number,
        'live-button-malfunction': Number,
        'live-charge-malfunction': Number,
        'live-database-malfunction': Number,
        'live-disk-low': Number,
        'live-object-door-failure': Number,
        'live-elevator-failure': Number,
        'live-gateway-malfunction': Number,
        'live-identity-mismatch': Number,
        'live-line-alarm': Number,
        'live-location-alarm': Number,
        'live-object-is-under-maintenance': Number,
        'live-microphone-malfunction': Number,
        'live-network-malfunction': Number,
        'live-periodical-call-overdue': Number,
        'live-pin-mismatch': Number,
        'live-power-malfunction': Number,
        'live-ram-low': Number,
        'live-reserved-device': Number,
        'live-serial-port-malfunction': Number,
        'live-shaft-failure': Number,
        'live-low-signal': Number,
        'live-sip-registration-failure': Number,
        'live-speaker-malfunction': Number,
        'live-technician-check-overdue': Number,
        'live-voice-alarm': Number,
    },

    data() {
      const today = new Date()
      const start = new Date(today)
      start.setDate(today.getDate() - 6)
      start.setHours(0,0,0,0)

      const end = new Date(today)
      end.setHours(23,0,0,0)
        return {
            _chart: null,
            rawSeries: [],
            series: [],
            seriesResolution: '1h',
            showFilters: false,

            // Alerts for UI filters-button
            filterAlerts: ALERT_DEFS,

            fullSeries: [],

            // date range
            dateRange: [start, end],
            dateError: '',
            fetchError: '',
            lastValidRange: [new Date(start), new Date(end)],

            selectedAlerts: []
        }
    },

    async mounted() {
        await this.loadData()
        this.initSelectedAlerts()
        this.renderChart()
    },

    watch: {
        selectedAlerts() {
            this.renderChart()
        },
    },

    methods: {

        // Method: Disable future dates in date picker
        disableFuture(date) {
            return disableFutureUtc(date)
        },

        toYmd(date) {
            return toYmdUtc(date)
        },

        toIso(date) {
            return toIso8601Utc(date)
        },

        daysInRange(start, end) {
            return daysInRangeUtc(start, end)
        },

        buildLabel(ts, resolution, isSingleDay) {
            if (!ts) return 'Live'

            const d = new Date(ts)
            const hh = String(d.getHours()).padStart(2, '0')
            const dd = String(d.getDate()).padStart(2, '0')
            const mm = String(d.getMonth() + 1).padStart(2, '0')

            if (resolution === '1d' || resolution === '1w') return `${dd}.${mm}`
            if (isSingleDay && (resolution === '1h' || resolution === '6h')) return `${hh}:00`
            return `${dd}.${mm} ${hh}:00`
        },

        // Method: Handle date range changes
        async onDateRangeChange(value) {
            const [startRaw, endRaw] = value || this.dateRange || []
            const normalized = validateAndNormalizeRange(startRaw, endRaw)
            if (!normalized.ok) {
                this.dateError = normalized.error
                return
            }

            const { startUtc: start, endUtc: end } = normalized
            this.dateError = ''
            this.lastValidRange = [new Date(start), new Date(end)]
            await this.loadData()
        },

        async loadData() {
            const [startRaw, endRaw] = this.dateRange || []
            const normalized = validateAndNormalizeRange(startRaw, endRaw)
            if (!normalized.ok) {
                this.dateError = normalized.error
                return
            }

            const { startUtc: start, endUtc: end } = normalized
            this.dateError = ''

            try {
                this.fetchError = ''
                const res = await axios.get('/api/timeseries', {
                    params: {
                        chart: 'AlertsChart',
                        start: this.toIso(start),
                        end: this.toIso(end),
                    }
                })
                const sorted = (res.data.data ?? []).slice().sort((a, b) => String(a.ts).localeCompare(String(b.ts)))
                const normalized = normalizeHourlyTimeseries(sorted, {
                    fill: 'null',
                    min: 0,
                    max: 100,
                })

                this.rawSeries = normalized
                this.fullSeries = normalized
                this.rebuildAggregatedSeries(start, end)
                this.injectLiveData()
                this.renderChart()
            } catch (e) {
                console.error('Timeseries fetch failed:', e)
                this.rawSeries = []
                this.series = []
                this.fullSeries = []
                this.fetchError = e?.response?.status === 422 ? 'Invalid date range' : 'Failed to load data'
                this.injectLiveData()
                this.renderChart()
            }
        },

        rebuildAggregatedSeries(start, end) {
            const rangeDays = this.daysInRange(start, end)
            this.seriesResolution = pickResolution(rangeDays)
            const aggregated = aggregateTimeseries(this.rawSeries, { start, end })
                .sort((a, b) => String(a.ts).localeCompare(String(b.ts)))

            this.series = aggregated.map((row) => {
                const v = Math.max(0, Math.min(100, Number(row.value) || 0))
                const base = { timestamp: row.ts }
                for (let i = 0; i < ALERT_DEFS.length; i++) {
                    const alert = ALERT_DEFS[i]
                    const factor = 0.55 + (((i * 7) % 10) / 10) // 0.55 .. 1.45
                    base[alert.key] = Math.max(0, Math.min(100, Math.round(v * factor)))
                }
                return base
            })
        },

        injectLiveData() {
            this.series = this.series.filter(x => x.timestamp !== null)
            this.series.push({
                Active_alarm: Number(this.liveActiveAlarm) || 0,
                Battery_malfunction: Number(this.liveBatteryMalfunction) || 0,
                Battery_low: Number(this.liveBatteryLow) || 0,
                Button_malfunction: Number(this.liveButtonMalfunction) || 0,
                Charge_malfunction: Number(this.liveChargeMalfunction) || 0,
                Database_malfunction: Number(this.liveDatabaseMalfunction) || 0,
                Disk_low: Number(this.liveDiskLow) || 0,
                Object_door_failure: Number(this.liveObjectDoorFailure) || 0,
                Elevator_failure: Number(this.liveElevatorFailure) || 0,
                Gateway_malfunction: Number(this.liveGatewayMalfunction) || 0,
                Identity_mismatch: Number(this.liveIdentityMismatch) || 0,
                Line_alarm: Number(this.liveLineAlarm) || 0,
                Location_alarm: Number(this.liveLocationAlarm) || 0,
                Object_is_under_maintenance: Number(this.liveObjectIsUnderMaintenance) || 0,
                Microphone_malfunction: Number(this.liveMicrophoneMalfunction) || 0,
                Network_malfunction: Number(this.liveNetworkMalfunction) || 0,
                Periodical_call_overdue: Number(this.livePeriodicalCallOverdue) || 0,
                Pin_mismatch: Number(this.livePinMismatch) || 0,
                Power_malfunction: Number(this.livePowerMalfunction) || 0,
                Ram_low: Number(this.liveRamLow) || 0,
                Reserved_device: Number(this.liveReservedDevice) || 0,
                Serial_port_malfunction: Number(this.liveSerialPortMalfunction) || 0,
                Shaft_failure: Number(this.liveShaftFailure) || 0,
                Low_signal: Number(this.liveLowSignal) || 0,
                Sip_registration_failure: Number(this.liveSipRegistrationFailure) || 0,
                Speaker_malfunction: Number(this.liveSpeakerMalfunction) || 0,
                Technician_check_overdue: Number(this.liveTechnicianCheckOverdue) || 0,
                Voice_alarm: Number(this.liveVoiceAlarm) || 0,
                timestamp: null,
            })
        },

        // Method: Initially select 5 alerts
        initSelectedAlerts() {
            this.selectedAlerts = ALERT_DEFS.slice(0, 5).map(a => a.key)
        },

        renderChart() {
            const data = this.series

            const ctx = this.$refs.chart.getContext('2d')

            // Generate labels based on resolved data
            const isSingleDay = this.toYmd(new Date(this.lastValidRange[0])) === this.toYmd(new Date(this.lastValidRange[1]))
            const labels = data.map(item => {
                if (item.timestamp === null) return 'Live'
                return this.buildLabel(item.timestamp, this.seriesResolution, isSingleDay)
            })

            if (this._chart) this._chart.destroy()

            this._chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: ALERT_DEFS
                        .filter(a => this.selectedAlerts.includes(a.key))
                        .map((a, index, arr) => {
                            const color = gradientColor(index, arr.length)

                            return {
                                label: a.label,
                                data: data.map(x => x[a.key] ?? 0),
                                borderColor: color,
                                pointBackgroundColor: color,
                                backgroundColor: color.replace(', 1)', ', 0.25)'),
                                fill: true,
                                tension: 0,
                                pointRadius: 4
                            }
                        })
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: { enabled: true },
                    title: {
                        display: true,
                        text: 'Alerts',
                        fontSize: 16,
                        fontStyle: 'bold',
                        color: '#beb4b6',
                        padding: 10
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#beb4b6',
                                font: { size: 13, weight: '500' }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#beb4b6', font: { size: 12, weight: '500' } },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        },
                        y: {
                            ticks: { color: '#beb4b6', font: { size: 12, weight: '500' }, beginAtZero: true },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        }
                    }
                }
            })
        }
    }
}
</script>

<style scoped>
.chart-container {
    width: 100%;
    height: 420px;
    position: relative;
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(11, 18, 51, 0.5);
    overflow: visible;
    animation: chartEnter 700ms cubic-bezier(.2, .9, .2, 1) both;
}

canvas {
    width: 100% !important;
    height: 100% !important;
    display: block;
    background: transparent;
}

@keyframes chartEnter {
    0% {
        opacity: 0;
        transform: translateY(8px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}


/* Filter Button Styles */
.filter-btn {
    position: absolute;
    top: 4px;
    right: 12px;
    z-index: 10;
    background: rgba(24, 44, 81, 0.15);
    border: 1px solid rgba(53, 64, 85, 0.4);
    color: #354055;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
}

.filter-btn:hover {
    background: rgba(24, 44, 81, 0.25);
}

.filter-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.filter-modal {
    position: relative;
    width: 80%;
    max-width: 1300px;
    height: 95%;
    background: rgba(24, 44, 81, 0.25);
    border-radius: 12px;
    padding-left: 24px;
    padding-right: 24px;
    padding-bottom: 24px;
    padding-top: 12px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
}

.close-btn {
    position: absolute;
    top: -6px;
    right: 0px;
    background: transparent;
    border: none;
    color: #cbd5e1;
    font-size: 20px;
    cursor: pointer;
}

.close-btn:hover {
    color: #f43f5e;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    grid-template-rows: repeat(6, 1fr);
    gap: 12px;
    margin-top: 24px;
    height: calc(100% - 32px);
}

.filter-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.05);
    padding: 0px 10px;
    border-radius: 6px;
    color: #e5e7eb;
    font-size: 12px;
}

.filter-cell input[type="checkbox"] {
    width: 16px;
    height: 16px;
    color: #354055;
    cursor: pointer;
    border-radius: 4px;
}

/* Date Picker Styles */
.top-controls {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 30;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.date-picker-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.resolution-selector {
    position: relative;
    margin: 0;
    padding: 0;
    z-index: 20;
}
</style>

<style>
/* Custom style to show only one calendar panel */
.single-panel-range .mx-range-wrapper .mx-calendar+.mx-calendar {
    display: none;
}

/* Date range button styling */
.date-range-input {
    color: #000000 !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    height: 30px !important;
    padding: 4px 30px 4px 8px !important;
    border-radius: 8px !important;
}

.force-below-popup.mx-datepicker-popup {
    top: calc(100% + 6px) !important;
    bottom: auto !important;
}

.date-error {
    margin-top: 4px;
    font-size: 12px;
    color: #b91c1c;
}
</style>
