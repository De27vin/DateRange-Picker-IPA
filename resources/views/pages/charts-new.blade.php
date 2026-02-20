@extends('layouts.app')

@section('content')
  <div class="mx-auto w-full px-5">
  @php
    // provide equipment counts for the charts page (used by EquipmentChart.vue)
    $enabled = \App\Models\Device::enabled()->get()?->count() ?? 0;
    $disabled = \App\Models\Device::disabled()->get()?->count() ?? 0;

    // provide alarm counts (use DeviceAlertsService to mirror Stats.php logic)
    try {
      $alertsService = new \App\Services\DeviceAlertsService();
      $grouped = $alertsService->getGroupedAlertsCounts(session('account.id')) ?? [];
      $inbound = $grouped['all']['VOICE'] ?? 0;
      $activeAlarms = isset($grouped['alarming']) ? array_sum($grouped['alarming']) : 0;
    } catch (\Throwable $ex) {
      $inbound = 0;
      $activeAlarms = 0;
    }

    // provide per-alert-type counts for the Alerts chart
    try {
      $alertsService = new \App\Services\DeviceAlertsService();
      $alertCounts = $alertsService->getAllAlertCounts(session('account.id')) ?? [];

      $Active_alarm = $alertCounts['ALARM'] ?? 0;
      $Battery_malfunction = $alertCounts['BATDEF'] ?? 0;
      $Battery_low = $alertCounts['BATLOW'] ?? 0;
      $Button_malfunction = $alertCounts['BUTTON'] ?? 0;
      $Charge_malfunction   = $alertCounts['CHARGE'] ?? 0;
      $Db_malfunction       = $alertCounts['DB'] ?? 0;
      $Disk_low             = $alertCounts['DISK'] ?? 0;
      $Gateway_malfunction  = $alertCounts['GATEWAY'] ?? 0;
      $Identity_issue       = $alertCounts['IDENTITY'] ?? 0;
      $Line_malfunction     = $alertCounts['LINE'] ?? 0;
      $Location_issue       = $alertCounts['LOCATION'] ?? 0;
      $Mic_malfunction      = $alertCounts['MIC'] ?? 0;
      $Network_malfunction  = $alertCounts['NETWORK'] ?? 0;
      $Pin_issue            = $alertCounts['PIN'] ?? 0;
      $Power_malfunction    = $alertCounts['POWER'] ?? 0;
      $Ram_malfunction      = $alertCounts['RAM'] ?? 0;
      $Reserve_issue        = $alertCounts['RESERVE'] ?? 0;
      $Serial_issue         = $alertCounts['SERIAL'] ?? 0;
      $Signal_issue         = $alertCounts['SIGNAL'] ?? 0;
      $Sip_malfunction      = $alertCounts['SIP'] ?? 0;
      $Speaker_malfunction  = $alertCounts['SPEAKER'] ?? 0;
      $Voice_alarm          = $alertCounts['VOICE'] ?? 0;
      
    } catch (\Throwable $ex) {
      $Active_alarm = 0;
      $Battery_malfunction = 0;
      $Battery_low = 0;
      $Button_malfunction = 0;
      $Charge_malfunction = 0;
      $Db_malfunction = 0;
      $Disk_low = 0;
      $Gateway_malfunction = 0;
      $Identity_issue = 0;
      $Line_malfunction = 0;
      $Location_issue = 0;
      $Mic_malfunction = 0;
      $Network_malfunction = 0;
      $Pin_issue = 0;
      $Power_malfunction = 0;
      $Ram_malfunction = 0;
      $Reserve_issue = 0;
      $Serial_issue = 0;
      $Signal_issue = 0;
      $Sip_malfunction = 0;
      $Speaker_malfunction = 0;
      $Voice_alarm = 0;
    }
    
    // provide service level numbers (automated / physical checks) as percentages (mirror Livewire Stats.php)
    try {
      $alertsService = new \App\Services\DeviceAlertsService();
      $grouped = $alertsService->getGroupedAlertsCounts(session('account.id')) ?? [];
      $localChecks = $grouped['all']['TECH'] ?? 0;
      $periodicalCalls = $grouped['all']['PERIODICAL'] ?? 0;
      $majorSum = isset($grouped['critical']) ? array_sum($grouped['critical']) : 0;
      $minorSum = isset($grouped['normal']) ? array_sum($grouped['normal']) : 0;
      $enabledCount = \App\Models\Device::enabled()->get()?->count() ?? 0;
      if ($enabledCount) {
        $serviceLevelCalls = (($enabledCount - $periodicalCalls - $majorSum) / $enabledCount) * 100;
        $serviceLevelChecks = (($enabledCount - $localChecks - $minorSum) / $enabledCount) * 100;
      } else {
        $serviceLevelCalls = 0;
        $serviceLevelChecks = 0;
      }
      $serviceLevelCalls = (int) round(max(0, $serviceLevelCalls));
      $serviceLevelChecks = (int) round(max(0, $serviceLevelChecks));
    } catch (\Throwable $ex) {
      $serviceLevelCalls = 0;
      $serviceLevelChecks = 0;
      $periodicalCalls = 0;
      $localChecks = 0;
    }
  @endphp

    <div id="vue-charts"></div>

  <script>
  window.EQUIPMENT_STATS = {!! json_encode(['enabled' => $enabled, 'disabled' => $disabled]) !!};
  window.ALARM_STATS = {!! json_encode(['inbound' => $inbound, 'active' => $activeAlarms]) !!};
  // ALERTS_STATS is a mapping of alert_type => device_count used by AlertsChart.vue
  window.ALERTS_STATS = {!! json_encode([
    'Active_alarm' => $Active_alarm,
    'Battery_malfunction' => $Battery_malfunction,
    'Battery_low' => $Battery_low,
    'Button_malfunction' => $Button_malfunction,
    'Charge_malfunction' => $Charge_malfunction,
    'Db_malfunction' => $Db_malfunction,
    'Disk_low' => $Disk_low,
    'Gateway_malfunction' => $Gateway_malfunction,
    'Identity_issue' => $Identity_issue,
    'Line_malfunction' => $Line_malfunction,
    'Location_issue' => $Location_issue,
    'Mic_malfunction' => $Mic_malfunction,
    'Network_malfunction' => $Network_malfunction,
    'Pin_issue' => $Pin_issue,
    'Power_malfunction' => $Power_malfunction,
    'Ram_malfunction' => $Ram_malfunction,
    'Reserve_issue' => $Reserve_issue,
    'Serial_issue' => $Serial_issue,
    'Signal_issue' => $Signal_issue,
    'Sip_malfunction' => $Sip_malfunction,
    'Speaker_malfunction' => $Speaker_malfunction,
    'Voice_alarm' => $Voice_alarm,
  ]) !!};
  // SERVICE_STATS contains service-level percentages and raw check counts
  window.SERVICE_STATS = {!! json_encode(['periodicalCalls' => $periodicalCalls ?? 0, 'localChecks' => $localChecks ?? 0]) !!};
  </script>

    <script src="{{ mix('/vue/vue-charts.js') }}"></script>
  </div>
@endsection
