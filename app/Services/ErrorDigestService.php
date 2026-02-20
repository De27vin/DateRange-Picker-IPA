<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Notifications\ErrorDigest;
use Illuminate\Support\Facades\Notification;

class ErrorDigestService
{
    private $logPath;
    private $trackingFilePath;

    public function __construct()
    {
        $this->logPath = storage_path('logs');
        $this->trackingFilePath = storage_path('app/last_error_notification.txt');
    }

    private function findLogFiles(): array
    {
        $files = [];

        // Check for single log file
        if (file_exists($this->logPath . '/laravel.log')) {
            $files[] = $this->logPath . '/laravel.log';
        }

        // Check for daily log files
        $dailyLogs = glob($this->logPath . '/laravel-*.log');
        if (!empty($dailyLogs)) {
            $files = array_merge($files, $dailyLogs);
        }

//        \Log::info("Found log files: " . implode(', ', $files));
        return $files;
    }

    public function getRecentErrors(Carbon $since): Collection
    {
//        \Log::info("Looking for errors since: " . $since->toDateTimeString());

        $errors = collect();
        $logFiles = $this->findLogFiles();

        if (empty($logFiles)) {
//            \Log::warning("No log files found!");
            return $errors;
        }

        foreach ($logFiles as $logFile) {
            // Debug log file processing
//            \Log::info("Processing log file: " . $logFile);

            if (preg_match('/laravel-(\d{4}-\d{2}-\d{2})\.log/', $logFile, $matches)) {
                $fileDate = Carbon::createFromFormat('Y-m-d', $matches[1]);
//                \Log::info("Log file date: " . $fileDate->toDateTimeString());
                if ($fileDate->startOfDay()->lt($since->startOfDay())) {
//                    \Log::info("Skipping old file: " . $logFile);
                    continue;
                }
            }

            $content = file_get_contents($logFile);
            $newErrors = $this->parseLogContent($content, $since);
//            \Log::info("Found " . $newErrors->count() . " errors in " . $logFile);
            $errors = $errors->merge($newErrors);
        }

//        \Log::info("Total errors found: " . $errors->count());
        return $errors->sortBy('timestamp');
    }

    private function parseLogContent(string $content, Carbon $since): Collection
    {
        $lines = explode("\n", $content);
        $errors = collect();
        $currentError = null;
        $stackTrace = [];

        foreach ($lines as $lineNumber => $line) {
            if (empty($line)) continue;

            // Check if line starts with a timestamp
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                // If we were collecting a stack trace, add it to the previous error
                if ($currentError && !empty($stackTrace)) {
                    $currentError['stack_trace'] = implode("\n", $stackTrace);
                    $errors->push($currentError);
                    $stackTrace = [];
                }

                try {
                    $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $matches[1]);

                    // Debug timestamp comparison
//                    \Log::debug("Comparing timestamps: Log: " . $timestamp->toDateTimeString() .
//                             " Since: " . $since->toDateTimeString() .
//                             " Is Greater: " . ($timestamp->greaterThanOrEqualTo($since) ? 'Yes' : 'No'));

                    // Only process errors since the specified time
                    if ($timestamp->greaterThanOrEqualTo($since)) {
                        // Extract error details
                        if (strpos($line, '.ERROR') !== false) {
                            preg_match('/\.ERROR: (.*?)( \{.*\})?$/', $line, $errorMatches);

                            if (!empty($errorMatches)) {
                                $currentError = [
                                    'timestamp' => $timestamp,
                                    'message' => $errorMatches[1] ?? 'Unknown error',
                                    'context' => json_decode($errorMatches[2] ?? '{}', true),
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
//                    \Log::error("Error parsing timestamp at line " . $lineNumber . ": " . $e->getMessage());
                }
            } elseif ($currentError && strpos($line, 'Stack trace:') !== false) {
                // Start collecting stack trace
                continue;
            } elseif ($currentError && !empty($line)) {
                // Add line to stack trace
                $stackTrace[] = $line;
            }
        }

        // Don't forget the last error if it has a stack trace
        if ($currentError && !empty($stackTrace)) {
            $currentError['stack_trace'] = implode("\n", $stackTrace);
            $errors->push($currentError);
        }

        return $errors;
    }

    public function getLastNotificationTime(): Carbon
    {
        return Carbon::now()->subHours(22);
//        if (!file_exists($this->trackingFilePath)) {
//            return Carbon::now()->subHours(6);
//        }

//        $timestamp = file_get_contents($this->trackingFilePath);
//        return Carbon::createFromTimestamp($timestamp);
    }

    public function updateLastNotificationTime(): void
    {
        file_put_contents($this->trackingFilePath, Carbon::now()->timestamp);
    }

    public function sendErrorDigest(): bool
    {
        $lastDigestTime = $this->getLastNotificationTime();
        $errors = $this->getRecentErrors($lastDigestTime);

        if ($errors->isEmpty()) {
            return false;
        }

        try {
            $recipients = config('logging.error_digest_recipients', []);

            Notification::route('mail', $recipients)->notify(new ErrorDigest($errors, '1 day'));

            $this->updateLastNotificationTime();

            return true;
        } catch (\Exception $e) {
//            \Log::error('Failed to send error digest: ' . $e->getMessage());
            return false;
        }
    }
}