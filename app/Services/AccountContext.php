<?php

namespace App\Services;

/**
 * Lightweight singleton that provides the active account ID in any execution context.
 *
 * In HTTP requests the value is read from the session (via fallback), so no extra
 * setup is needed there.  In contexts where no session exists (queue workers, Artisan
 * commands) callers must call set() before any Eloquent query that relies on
 * DevicesByAccountScope or DeviceSitesByAccountScope.
 *
 * Always call reset() when the explicit context is no longer needed (e.g. in a
 * job's finally block) so the singleton does not leak state across jobs in the
 * same worker process.
 */
class AccountContext
{
    private ?int $accountId = null;

    public function set(?int $accountId): void
    {
        $this->accountId = $accountId;
    }

    public function get(): ?int
    {
        return $this->accountId ?? session('account.id');
    }

    public function reset(): void
    {
        $this->accountId = null;
    }
}
