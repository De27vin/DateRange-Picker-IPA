<?php

namespace App\Console\Commands;

use App\Services\ErrorDigestService;
use Illuminate\Console\Command;

class SendErrorDigest extends Command
{
    protected $signature = 'errors:send-digest';
    protected $description = 'Send digest of errors that occurred since last notification';

    protected $errorDigestService;

    public function __construct(ErrorDigestService $errorDigestService)
    {
        parent::__construct();
        $this->errorDigestService = $errorDigestService;
    }

    public function handle()
    {
        if ($this->errorDigestService->sendErrorDigest()) {
            $this->info('Error digest sent successfully.');
        } else {
            $this->info('No new errors to report or sending failed.');
        }
    }
}