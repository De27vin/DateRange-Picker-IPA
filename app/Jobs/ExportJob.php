<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AccountContext;
use App\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 1;

    public function __construct(
        private string $exportType,  // 'devices' | 'comments' | 'history' | 'gateways'
        private array  $params,      // serializable domain params for the generator
        private string $format,      // 'csv' | 'xlsx'
        private string $delivery,    // 'browser' | 'email'
        private int    $userId,
        private string $downloadId,
    ) {}

    public function handle(ExportService $exportService, AccountContext $accountContext): void
    {
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit', '1G');

        try {
            $accountContext->set($this->params['accountId'] ?? null);
            $user = User::find($this->userId);
            if ($user) {
                Auth::setUser($user);
            }

            $progressFile = $exportService->progressFilePath($this->exportType, $this->userId, $this->downloadId);
            $filePath     = $exportService->exportFilePath($this->exportType, $this->downloadId, $this->format);

            $exportService->initProgress($progressFile);

            [$header, $rows] = $exportService->makeExport($this->exportType, $this->params, $progressFile);

            $exportService->writeFile($rows, $header, $this->format, $filePath);
            $exportService->finalizeProgress($progressFile);

            if ($this->delivery === 'email') {
                $exportService->sendByEmail($filePath, $this->exportType, $this->format, $this->userId);
            }
        } finally {
            // Prevent account ID and authenticated user from leaking to subsequent jobs
            $accountContext->reset();
            Auth::forgetUser();
        }
    }
}
