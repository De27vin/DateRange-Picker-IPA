<?php
namespace App\Services;

use App\Models\Session;
use Illuminate\Support\Facades\File;

class FileRecordsService
{
    public function getRecordingPath(Session $session, string $accountSlug, string $extension = '.wav'): string
    {
        $startEventTimestamp = $session->events->firstWhere('event_type.et_type', 'START')?->event_timestamp;
        $anumberEventValue = $session->events->firstWhere('event_type.et_type', 'ANUMBER')?->event_value;
        $bnumberEventValue = $session->events->firstWhere('event_type.et_type', 'BNUMBER')?->event_value;

        if (!empty($startEventTimestamp) && !empty($anumberEventValue) && !empty($bnumberEventValue)) {
            return sprintf('/mnt/%s/%s/_%s_%s_%s_%s_%s%s',
                $session->session_host,
                $accountSlug,
                strtolower($session->session_type->st_type),
                str_replace(' ', '_', $startEventTimestamp),
                $session->session_uuid,
                $anumberEventValue,
                $bnumberEventValue,
                $extension
            );
        }

        return '';
    }

    public function getExistingRecording(Session $session, string $accountSlug): ?string
    {
        $path = $this->getRecordingPath($session, $accountSlug);
        if (!$path) return null;
        return File::exists($path) ? $path : null;
    }
}
