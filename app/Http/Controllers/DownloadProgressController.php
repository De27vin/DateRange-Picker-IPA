<?php

namespace App\Http\Controllers;


class DownloadProgressController extends Controller
{
    public function getExportHistoryProgress()
    {
        $progressFile = storage_path('framework/cache/export_history_' . auth()->id() . '.txt');
        if (file_exists($progressFile)) {
            return response()->json([
                'progress' => (int) file_get_contents($progressFile)
            ]);
        }
        return response()->json(['progress' => null]);
    }

    public function getExportDevicesProgress()
    {
        $downloadId = request()->input('id');
        $format     = request()->input('format');
        $fileSuffix = $downloadId ? '_' . $downloadId : '';
        $progressFile = storage_path('framework/cache/export_devices_' . auth()->id() . $fileSuffix . '.txt');

        $ready = false;
        $readyFormat = null;
        if ($downloadId) {
            $formatsToCheck = $format ? [$format] : ['xlsx', 'csv'];
            foreach ($formatsToCheck as $fmt) {
                $exportPath = storage_path('app/exports/devices_' . $downloadId . '.' . $fmt);
                if (file_exists($exportPath)) {
                    $ready = true;
                    $readyFormat = $fmt;
                    break;
                }
            }
        }

        $progress = null;
        if (file_exists($progressFile)) {
            $progress = (int) file_get_contents($progressFile);
        }

        // Guard: do not signal ready until progress reaches 100.
        // The file appears on disk as soon as the job renames its temp file —
        // before post-processing (e.g. email sending) completes and before
        // progress is written to 100. Without this check the browser would
        // download and delete the file while the job is still using it.
        if ($ready && $progress < 100) {
            $ready = false;
            $readyFormat = null;
        }

        return response()->json([
            'progress'     => $progress,
            'ready'        => $ready,
            'ready_format' => $readyFormat,
        ]);
    }
}
