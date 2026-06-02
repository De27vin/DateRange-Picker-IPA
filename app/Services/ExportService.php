<?php

namespace App\Services;

use App\Exports\GeneratorExport;
use App\Mail\ExportReady;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    public function progressFilePath(string $type, int $userId, string $downloadId): string
    {
        return storage_path('framework/cache/export_' . $type . '_' . $userId . '_' . $downloadId . '.txt');
    }

    public function exportFilePath(string $type, string $downloadId, string $format): string
    {
        $dir = storage_path('app/exports');
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        return $dir . '/' . $type . '_' . $downloadId . '.' . $format;
    }

    public function initProgress(string $progressFile): void
    {
        @file_put_contents($progressFile, '0');
        @chmod($progressFile, 0600);
    }

    public function finalizeProgress(string $progressFile): void
    {
        @file_put_contents($progressFile, '100');
        @chmod($progressFile, 0600);
    }

    public function writeFile(\Generator $rows, array $header, string $format, string $filePath): void
    {
        if ($format === 'xlsx') {
            Excel::store(
                new GeneratorExport($rows, array_values($header)),
                'exports/' . basename($filePath),
                'local'
            );
        } else {
            $tempPath = $filePath . '.tmp';
            $file = fopen($tempPath, 'w');
            // UTF-8 BOM — Excel opens CSV without mangling special characters
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, array_values($header), ',', '"', '\\', "\n");
            foreach ($rows as $row) {
                $line = implode(',', array_map(
                    fn ($val) => '"' . str_replace('"', '""', (string) $val) . '"',
                    $row
                ));
                fwrite($file, $line . "\n");
            }
            fclose($file);
            @rename($tempPath, $filePath);
        }
    }

    public function sendByEmail(string $filePath, string $exportType, string $format, int $userId): void
    {
        $user = User::find($userId);
        if (!$user || !($email = $user->getPrimaryEmail())) {
            return;
        }

        try {
            Mail::to($email)->send(new ExportReady($filePath, $exportType, $format));
        } catch (\Throwable $e) {
            Log::warning('ExportReady email failed', [
                'exportType' => $exportType,
                'userId'     => $userId,
                'error'      => $e->getMessage(),
            ]);
        } finally {
            @unlink($filePath);
        }
    }
}
