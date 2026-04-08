<template>
    <div class="chart-container">
        <div class="top-controls">
            <div class="date-picker-wrap">
                <date-picker
                    v-model="dateRange"
                    range
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

            <div class="resolution-selector"></div>
        </div>

        <button class="filter-btn" @click="showFilters = true">
            Filters
        </button>

        <canvas ref="chart"></canvas>

        <div v-if="showFilters" class="filter-overlay">
            <div class="filter-modal">
                <button class="close-btn" @click="showFilters = false">x</button>

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
import { formatChartLabel } from '../../../js/utils/timeseriesDisplay'
import { buildLiveSeriesRow, normalizeSeriesRows } from '../../../js/utils/timeseriesSeries'
import {
    disableFutureUtc,
    toIso8601Utc,
    toYmdUtc,
    validateAndNormalizeRange,
} from '../../../js/utils/timeseriesRangeValidation'
import DatePicker from 'vue2-datepicker'
import 'vue2-datepicker/index.css'

const ALERT_DEFS = [
    { key: 'active_alarm', label: 'Active alarm' },
    { key: 'battery_malfunction', label: 'Battery malfunction' },
    { key: 'battery_low', label: 'Battery low' },
    { key: 'button_malfunction', label: 'Button malfunction' },
    { key: 'charge_malfunction', label: 'Charge malfunction' },
    { key: 'database_malfunction', label: 'Database malfunction' },
    { key: 'disk_low', label: 'Disk low' },
    { key: 'object_door_failure', label: 'Object door failure' },
    { key: 'elevator_failure', label: 'Elevator failure' },
    { key: 'gateway_malfunction', label: 'Gateway malfunction' },
    { key: 'identity_mismatch', label: 'Identity mismatch' },
    { key: 'line_alarm', label: 'Line alarm' },
    { key: 'object_is_under_maintenance', label: 'Object under maintenance' },
    { key: 'microphone_malfunction', label: 'Microphone malfunction' },
    { key: 'network_malfunction', label: 'Network malfunction' },
    { key: 'periodical_call_overdue', label: 'Periodical call overdue' },
    { key: 'pin_mismatch', label: 'Pin mismatch' },
    { key: 'power_malfunction', label: 'Power malfunction' },
    { key: 'ram_low', label: 'Ram low' },
    { key: 'reserved_device', label: 'Reserved device' },
    { key: 'serial_port_malfunction', label: 'Serial port malfunction' },
    { key: 'shaft_failure', label: 'Shaft failure' },
    { key: 'low_signal', label: 'Low signal' },
    { key: 'sip_registration_failure', label: 'SIP registration failure' },
    { key: 'speaker_malfunction', label: 'Speaker malfunction' },
    { key: 'technician_check_overdue', label: 'Technician check overdue' },
    { key: 'voice_alarm', label: 'Voice alarm' },
]

function interpolateColor(start, end, factor) {
    return start.map((s, i) => Math.round(s + factor * (end[i] - s)))
}

function gradientColor(index, total) {
    const start = [23, 44, 81]
    const end = [162, 35, 42]

    if (total <= 1) {
        return `rgba(${start.join(',')}, 1)`
    }

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
        start.setHours(0, 0, 0, 0)

        const end = new Date(today)
        end.setHours(23, 0, 0, 0)

        return {
            _chart: null,
            series: [],
            seriesResolution: '1h',
            showFilters: false,
            filterAlerts: ALERT_DEFS,
            dateRange: [start, end],
            dateError: '',
            fetchError: '',
            lastValidRange: [new Date(start), new Date(end)],
            selectedAlerts: [],
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
        disableFuture(date) {
            return disableFutureUtc(date)
        },

        toYmd(date) {
            return toYmdUtc(date)
        },

        toIso(date) {
            return toIso8601Utc(date)
        },

        buildLabel(ts, resolution, isSingleDay) {
            return formatChartLabel(ts, resolution, isSingleDay, {
                displayMode: 'local',
            })
        },

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

                this.seriesResolution = res?.data?.meta?.resolution ?? '1h'
                this.series = normalizeSeriesRows(res?.data?.data, ALERT_DEFS.map((alert) => alert.key))
                this.injectLiveData()
                this.renderChart()
            } catch (e) {
                console.error('Timeseries fetch failed:', e)
                this.series = []
                this.fetchError = e?.response?.status === 422 ? 'Invalid date range' : 'Failed to load data'
                this.injectLiveData()
                this.renderChart()
            }
        },

        injectLiveData() {
            this.series = this.series.filter((x) => x.timestamp !== null)
            this.series.push(buildLiveSeriesRow({
                active_alarm: this.liveActiveAlarm,
                battery_malfunction: this.liveBatteryMalfunction,
                battery_low: this.liveBatteryLow,
                button_malfunction: this.liveButtonMalfunction,
                charge_malfunction: this.liveChargeMalfunction,
                database_malfunction: this.liveDatabaseMalfunction,
                disk_low: this.liveDiskLow,
                object_door_failure: this.liveObjectDoorFailure,
                elevator_failure: this.liveElevatorFailure,
                gateway_malfunction: this.liveGatewayMalfunction,
                identity_mismatch: this.liveIdentityMismatch,
                line_alarm: this.liveLineAlarm,
                object_is_under_maintenance: this.liveObjectIsUnderMaintenance,
                microphone_malfunction: this.liveMicrophoneMalfunction,
                network_malfunction: this.liveNetworkMalfunction,
                periodical_call_overdue: this.livePeriodicalCallOverdue,
                pin_mismatch: this.livePinMismatch,
                power_malfunction: this.livePowerMalfunction,
                ram_low: this.liveRamLow,
                reserved_device: this.liveReservedDevice,
                serial_port_malfunction: this.liveSerialPortMalfunction,
                shaft_failure: this.liveShaftFailure,
                low_signal: this.liveLowSignal,
                sip_registration_failure: this.liveSipRegistrationFailure,
                speaker_malfunction: this.liveSpeakerMalfunction,
                technician_check_overdue: this.liveTechnicianCheckOverdue,
                voice_alarm: this.liveVoiceAlarm,
            }))
        },

        initSelectedAlerts() {
            this.selectedAlerts = ALERT_DEFS.slice(0, 5).map((alert) => alert.key)
        },

        renderChart() {
            const data = this.series
            const ctx = this.$refs.chart.getContext('2d')
            const isSingleDay = this.toYmd(new Date(this.lastValidRange[0])) === this.toYmd(new Date(this.lastValidRange[1]))
            const labels = data.map((item) => {
                if (item.timestamp === null) {
                    return 'Live'
                }

                return this.buildLabel(item.timestamp, this.seriesResolution, isSingleDay)
            })

            if (this._chart) this._chart.destroy()

            this._chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: ALERT_DEFS
                        .filter((alert) => this.selectedAlerts.includes(alert.key))
                        .map((alert, index, arr) => {
                            const color = gradientColor(index, arr.length)

                            return {
                                label: alert.label,
                                data: data.map((point) => point[alert.key] ?? 0),
                                borderColor: color,
                                pointBackgroundColor: color,
                                backgroundColor: color.replace(', 1)', ', 0)'),
                                fill: true,
                                tension: 0,
                                pointRadius: 4,
                            }
                        }),
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
    right: 0;
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
    padding: 0 10px;
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
.single-panel-range .mx-range-wrapper .mx-calendar + .mx-calendar {
    display: none;
}

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
