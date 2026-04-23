@extends('layouts.app')

@section('content')
  <div class="mx-auto w-full px-5">
  @php
    // provide equipment counts for the charts page (used by EquipmentChart.vue)
    $enabled = \App\Models\Device::enabled()->get()?->count() ?? 0;
    $disabled = \App\Models\Device::disabled()->get()?->count() ?? 0;

    // provide alarm counts; inbound calls now come from active ALARM sessions
    try {
      $alertsService = new \App\Services\DeviceAlertsService();
      $grouped = $alertsService->getGroupedAlertsCounts(session('account.id')) ?? [];
      $inbound = (int) \Illuminate\Support\Facades\DB::table('sessions as s')
        ->join('session_types as st', 's.session_st_id', '=', 'st.st_id')
        ->where('s.session_account_id', session('account.id'))
        ->where('st.st_type', 'ALARM')
        ->whereNull('s.session_end')
        ->where(function ($query): void {
          $query->whereNotNull('s.session_device_id')
            ->orWhereExists(function ($sub): void {
              $sub->from('sessions as child')
                ->join('session_types as child_st', 'child.session_st_id', '=', 'child_st.st_id')
                ->whereColumn('child.session_ref_id', 's.session_id')
                ->where('child_st.st_type', 'AGENT')
                ->whereNotNull('child.session_device_id');
            });
        })
        ->count();
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
      $Database_malfunction = $alertCounts['DB'] ?? 0;
      $Disk_low             = $alertCounts['DISK'] ?? 0;
      $Object_door_failure  = $alertCounts['LOCATION'] ?? 0;
      $Elevator_failure     = $alertCounts['ELEVATOR'] ?? 0;
      $Gateway_malfunction  = $alertCounts['GATEWAY'] ?? 0;
      $Identity_mismatch    = $alertCounts['IDENTITY'] ?? 0;
      $Line_alarm           = $alertCounts['LINE'] ?? 0;
      $Object_is_under_maintenance = $alertCounts['MAINTENANCE'] ?? 0;
      $Microphone_malfunction = $alertCounts['MIC'] ?? 0;
      $Network_malfunction  = $alertCounts['NETWORK'] ?? 0;
      $Periodical_call_overdue = $alertCounts['PERIODICAL'] ?? 0;
      $Pin_mismatch         = $alertCounts['PIN'] ?? 0;
      $Power_malfunction    = $alertCounts['POWER'] ?? 0;
      $Ram_low              = $alertCounts['RAM'] ?? 0;
      $Reserved_device      = $alertCounts['RESERVE'] ?? 0;
      $Serial_port_malfunction = $alertCounts['SERIAL'] ?? 0;
      $Shaft_failure        = $alertCounts['SHAFT'] ?? 0;
      $Low_signal           = $alertCounts['SIGNAL'] ?? 0;
      $Sip_registration_failure = $alertCounts['SIP'] ?? 0;
      $Speaker_malfunction  = $alertCounts['SPEAKER'] ?? 0;
      $Technician_check_overdue = $alertCounts['TECH'] ?? 0;
      $Voice_alarm          = $alertCounts['VOICE'] ?? 0;
      
    } catch (\Throwable $ex) {
      $Active_alarm = 0;
      $Battery_malfunction = 0;
      $Battery_low = 0;
      $Button_malfunction = 0;
      $Charge_malfunction = 0;
      $Database_malfunction = 0;
      $Disk_low = 0;
      $Object_door_failure = 0;
      $Elevator_failure = 0;
      $Gateway_malfunction = 0;
      $Identity_mismatch = 0;
      $Line_alarm = 0;
      $Object_is_under_maintenance = 0;
      $Microphone_malfunction = 0;
      $Network_malfunction = 0;
      $Periodical_call_overdue = 0;
      $Pin_mismatch = 0;
      $Power_malfunction = 0;
      $Ram_low = 0;
      $Reserved_device = 0;
      $Serial_port_malfunction = 0;
      $Shaft_failure = 0;
      $Low_signal = 0;
      $Sip_registration_failure = 0;
      $Speaker_malfunction = 0;
      $Technician_check_overdue = 0;
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
    'Database_malfunction' => $Database_malfunction,
    'Disk_low' => $Disk_low,
    'Object_door_failure' => $Object_door_failure,
    'Elevator_failure' => $Elevator_failure,
    'Gateway_malfunction' => $Gateway_malfunction,
    'Identity_mismatch' => $Identity_mismatch,
    'Line_alarm' => $Line_alarm,
    'Object_is_under_maintenance' => $Object_is_under_maintenance,
    'Microphone_malfunction' => $Microphone_malfunction,
    'Network_malfunction' => $Network_malfunction,
    'Periodical_call_overdue' => $Periodical_call_overdue,
    'Pin_mismatch' => $Pin_mismatch,
    'Power_malfunction' => $Power_malfunction,
    'Ram_low' => $Ram_low,
    'Reserved_device' => $Reserved_device,
    'Serial_port_malfunction' => $Serial_port_malfunction,
    'Shaft_failure' => $Shaft_failure,
    'Low_signal' => $Low_signal,
    'Sip_registration_failure' => $Sip_registration_failure,
    'Speaker_malfunction' => $Speaker_malfunction,
    'Technician_check_overdue' => $Technician_check_overdue,
    'Voice_alarm' => $Voice_alarm,
  ]) !!};
  // SERVICE_STATS contains service-level percentages and raw check counts
  window.SERVICE_STATS = {!! json_encode(['periodicalCalls' => $periodicalCalls ?? 0, 'localChecks' => $localChecks ?? 0]) !!};
  </script>

    <script src="{{ mix('/vue/vue-charts.js') }}"></script>
  </div>
@endsection
