<template>
  <div id="dashboard-grid">
  <div class="grid-box"><EquipmentChart :liveEnabled="equipmentStats.enabled" :liveDisabled="equipmentStats.disabled" :default-range="chartSettings.ranges.equipment" /></div>
  <div class="grid-box"><AlarmChart :live-inbound="alarmStats.inbound" :live-active="alarmStats.active" :default-range="chartSettings.ranges.alarms" /></div>
    <div class="grid-box"><AlertsChart
      :live-active-alarm="alertsStats.Active_alarm"
      :live-battery-malfunction="alertsStats.Battery_malfunction" 
      :live-battery-low="alertsStats.Battery_low" 
      :live-button-malfunction="alertsStats.Button_malfunction" 
      :live-charge-malfunction="alertsStats.Charge_malfunction"
      :live-database-malfunction="alertsStats.Database_malfunction"
      :live-disk-low="alertsStats.Disk_low"
      :live-object-door-failure="alertsStats.Object_door_failure"
      :live-elevator-failure="alertsStats.Elevator_failure"
      :live-gateway-malfunction="alertsStats.Gateway_malfunction"
      :live-identity-mismatch="alertsStats.Identity_mismatch"
      :live-line-alarm="alertsStats.Line_alarm"
      :live-object-is-under-maintenance="alertsStats.Object_is_under_maintenance"
      :live-microphone-malfunction="alertsStats.Microphone_malfunction"
      :live-network-malfunction="alertsStats.Network_malfunction"
      :live-periodical-call-overdue="alertsStats.Periodical_call_overdue"
      :live-pin-mismatch="alertsStats.Pin_mismatch"
      :live-power-malfunction="alertsStats.Power_malfunction"
      :live-ram-low="alertsStats.Ram_low"
      :live-reserved-device="alertsStats.Reserved_device"
      :live-serial-port-malfunction="alertsStats.Serial_port_malfunction"
      :live-shaft-failure="alertsStats.Shaft_failure"
      :live-low-signal="alertsStats.Low_signal"
      :live-sip-registration-failure="alertsStats.Sip_registration_failure"
      :live-speaker-malfunction="alertsStats.Speaker_malfunction"
      :live-technician-check-overdue="alertsStats.Technician_check_overdue"
      :live-voice-alarm="alertsStats.Voice_alarm"
      :default-range="chartSettings.ranges.alerts" /></div>
    <div class="grid-box"><ServiceLevelChart :live-periodic="serviceStats.periodicalCalls" :live-local="serviceStats.localChecks" :default-range="chartSettings.ranges.serviceLevel" /></div>
  </div>
</template>

<script>
import AlarmChart from './charts/AlarmChart.vue'
import AlertsChart from './charts/AlertsChart.vue'
import EquipmentChart from './charts/EquipmentChart.vue'
import ServiceLevelChart from './charts/ServiceLevelChart.vue'
import axios from 'axios'

const SYSTEM_CHART_SETTINGS = {
  ranges: {
    equipment: { amount: 3, unit: 'months' },
    alarms: { amount: 3, unit: 'months' },
    alerts: { amount: 3, unit: 'months' },
    serviceLevel: { amount: 3, unit: 'months' },
  },
}

export default {
  components: {     
    AlarmChart,
    AlertsChart,
    EquipmentChart,
    ServiceLevelChart
  },

  data() {
    return {
      chartSettings: SYSTEM_CHART_SETTINGS,
      equipmentStats: (typeof window !== 'undefined' && window.EQUIPMENT_STATS) ? window.EQUIPMENT_STATS : { enabled: 0, disabled: 0 },
      alarmStats: (typeof window !== 'undefined' && window.ALARM_STATS) ? window.ALARM_STATS : { inbound: 0, active: 0 },
      alertsStats: (typeof window !== 'undefined' && window.ALERTS_STATS) ? window.ALERTS_STATS : { 
        Active_alarm: 0, 
        Battery_malfunction: 0, 
        Battery_low: 0, 
        Button_malfunction: 0, 
        Charge_malfunction: 0,
        Database_malfunction: 0,
        Disk_low: 0,
        Object_door_failure: 0,
        Elevator_failure: 0,
        Gateway_malfunction: 0,
        Identity_mismatch: 0,
        Line_alarm: 0,
        Object_is_under_maintenance: 0,
        Microphone_malfunction: 0,
        Network_malfunction: 0,
        Periodical_call_overdue: 0,
        Pin_mismatch: 0,
        Power_malfunction: 0,
        Ram_low: 0,
        Reserved_device: 0,
        Serial_port_malfunction: 0,
        Shaft_failure: 0,
        Low_signal: 0,
        Sip_registration_failure: 0,
        Speaker_malfunction: 0,
        Technician_check_overdue: 0,
        Voice_alarm: 0 },
      serviceStats: (typeof window !== 'undefined' && window.SERVICE_STATS) ? window.SERVICE_STATS : { periodicalCalls: 0, localChecks: 0 }
    };
  },
  async mounted() {
    try {
      const response = await axios.get('/api/charts/settings')
      this.chartSettings = response?.data?.data || SYSTEM_CHART_SETTINGS
    } catch (error) {
      console.error('Charts settings fetch failed:', error)
      this.chartSettings = SYSTEM_CHART_SETTINGS
    }
  }
}
</script>

<style scoped>
#dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-top: 20px;
}

.grid-box {
  border: 1px solid #ccc;
  background-color: #fff;
  padding: 10px;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

</style>
