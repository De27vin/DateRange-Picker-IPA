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
        $fileSuffix   = $downloadId ? '_' . $downloadId : '';
        $progressFile = storage_path('framework/cache/export_devices_' . auth()->id() . $fileSuffix . '.txt');

        $ready = false;
        if ($downloadId) {
            $exportPath = storage_path('app/exports/devices_' . $downloadId . '.xlsx');
            $ready = file_exists($exportPath);
        }

        if (file_exists($progressFile)) {
            return response()->json([
                'progress' => (int) file_get_contents($progressFile),
                'ready'    => $ready
            ]);
        }

        return response()->json(['progress' => null, 'ready' => $ready]);
    }
}