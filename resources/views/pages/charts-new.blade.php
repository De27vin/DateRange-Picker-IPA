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
    $chartMapper = app(\App\Services\TimeseriesSnapshotChartMapper::class);
    $alertsStats = array_fill_keys(array_values($chartMapper->alertLiveStatKeysBySeriesKey()), 0);

    try {
      $alertsService = new \App\Services\DeviceAlertsService();
      $alertCounts = $alertsService->getAllAlertCounts(session('account.id')) ?? [];
      foreach ($chartMapper->alertLiveStatKeysBySeriesKey() as $seriesKey => $liveKey) {
        $rawType = $chartMapper->alertTypeCodeForSeriesKey($seriesKey);
        $alertsStats[$liveKey] = $rawType !== null ? ($alertCounts[$rawType] ?? 0) : 0;
      }
    } catch (\Throwable $ex) {
      // keep zero-valued keys for the frontend fallback
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
  window.ALERTS_STATS = {!! json_encode($alertsStats) !!};
  // SERVICE_STATS contains service-level percentages and raw check counts
  window.SERVICE_STATS = {!! json_encode(['periodicalCalls' => $periodicalCalls ?? 0, 'localChecks' => $localChecks ?? 0]) !!};
  </script>

    <script src="{{ mix('/vue/vue-charts.js') }}"></script>
  </div>
@endsection
