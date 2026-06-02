<?php

namespace App\Http\Controllers;

use App\Jobs\ExportJob;
use App\Services\Export\CommentsRowGenerator;
use App\Services\Export\DevicesRowGenerator;
use App\Services\Export\GatewaysRowGenerator;
use App\Services\Export\HistoryRowGenerator;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => 'required|string|in:devices,comments,history,gateways',
            'format'      => 'nullable|in:csv,xlsx',
            'delivery'    => 'nullable|in:browser,email',
            'params'      => 'nullable|array',
            'download_id' => 'nullable|string',
            'component_id'=> 'nullable|string',
        ]);

        $downloadId  = $data['download_id'] ?? (string) Str::uuid();
        $params      = $data['params'] ?? [];
        $format      = $data['format'] ?? 'csv';
        $delivery    = $data['delivery'] ?? 'browser';
        $exportType  = $data['type'];
        $componentId = $data['component_id'] ?? null;

        $this->validateParams($exportType, $params);

        ExportJob::dispatch(
            $exportType,
            $params,
            $format,
            $delivery,
            auth()->id(),
            $downloadId
        );

        return response()->json([
            'type'         => $exportType,
            'format'       => $format,
            'delivery'     => $delivery,
            'download_id'  => $downloadId,
            'component_id' => $componentId,
            'progress_url' => route('exports.progress', [
                'type'       => $exportType,
                'downloadId' => $downloadId,
                'format'     => $format,
            ]),
            'download_url' => route('exports.download', [
                'type'       => $exportType,
                'downloadId' => $downloadId,
                'format'     => $format,
            ]),
        ]);
    }

    public function progress(string $type, string $downloadId, Request $request): JsonResponse
    {
        $formatFilter = $request->query('format');
        /** @var ExportService $exportService */
        $exportService = app(ExportService::class);

        $progressFile = $exportService->progressFilePath($type, auth()->id(), $downloadId);
        $progress     = file_exists($progressFile) ? (int) file_get_contents($progressFile) : null;

        $ready      = false;
        $readyFormat = null;
        $formatsToCheck = $formatFilter ? [$formatFilter] : ['csv', 'xlsx'];

        foreach ($formatsToCheck as $fmt) {
            $path = $exportService->exportFilePath($type, $downloadId, $fmt);
            if (file_exists($path)) {
                $ready       = true;
                $readyFormat = $fmt;
                break;
            }
        }

        if ($ready && ($progress === null || $progress < 100)) {
            $ready       = false;
            $readyFormat = null;
        }

        return response()->json([
            'progress'     => $progress,
            'ready'        => $ready,
            'ready_format' => $readyFormat,
        ]);
    }

    public function download(string $type, string $downloadId, Request $request)
    {
        $format = $request->query('format', 'csv');

        /** @var ExportService $exportService */
        $exportService = app(ExportService::class);
        $path = $exportService->exportFilePath($type, $downloadId, $format);

        if (!file_exists($path)) {
            abort(404, 'Export file not found or not ready yet');
        }

        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function validateParams(string $type, array $params): void
    {
        $generator = app(match ($type) {
            'devices'  => DevicesRowGenerator::class,
            'comments' => CommentsRowGenerator::class,
            'history'  => HistoryRowGenerator::class,
            'gateways' => GatewaysRowGenerator::class,
            default    => null,
        });

        if (!$generator) {
            return;
        }

        $missing = array_diff($generator->requiredParams(), array_keys($params));
        if (!empty($missing)) {
            abort(422, "Missing required params for export type '{$type}': " . implode(', ', $missing));
        }
    }
}
