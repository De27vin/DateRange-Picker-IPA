<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Locale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserContextService
{
    private const COOKIE_NAME = 'ucp_account';
    private const COOKIE_LIFETIME = 1000000;

    private ?array $cachedUserAccountIds = null;
    private $cachedUserAccounts = null;
    private array $cachedAccountsById = [];

    public function __construct(
        private readonly LanguageService $languageService
    ) {}

    public function setAccountCookie(int $accountId): void
    {
        Cookie::queue(cookie(
            self::COOKIE_NAME,
            encrypt($accountId),
            self::COOKIE_LIFETIME,
            '/',
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax')
        ));
    }

    public function getAccountCookie(): ?int
    {
        $rawCookie = Cookie::get(self::COOKIE_NAME);

        if (empty($rawCookie)) {
            return null;
        }

        try {
            return decrypt($rawCookie);
        } catch (\Throwable $e) {
            if (is_numeric($rawCookie)) {
                $accountId = (int) $rawCookie;
                $this->setAccountCookie($accountId);
                return $accountId;
            }

            Log::warning('Invalid account cookie', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            $this->clearAccountCookie();
            return null;
        }
    }

    public function clearAccountCookie(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    public function getSessionAccountId(): ?int
    {
        return session('account.id');
    }

    public function getSessionAccountSlug(): ?string
    {
        return session('account.slug');
    }

    public function getSessionAccountAll(): ?array
    {
        return session('account');
    }

    public function setAccountSession(int $accountId, string $slug): void
    {
        session([
            'account' => [
                'id' => $accountId,
                'slug' => $slug
            ]
        ]);
    }

    public function clearAccountSession(): void
    {
        session()->forget('account');
    }

    public function clearAccountContext(): void
    {
        $this->clearAccountCookie();
        $this->clearAccountSession();
    }

    public function ensureAccountContext(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $sessionAccountId = $this->getSessionAccountId();

        if (!$sessionAccountId) {
            $userAccounts = $this->getUserAccounts();

            if ($userAccounts->isEmpty()) {
                Log::warning('User context: no accounts assigned, cannot initialize', [
                    'user_id' => auth()->id()
                ]);
                return false;
            }

            $defaultAccount = $userAccounts->sortBy('account_id')->first();
            $this->switchAccount($defaultAccount->account_id);

            Log::info('User context: initialized with default account', [
                'user_id' => auth()->id(),
                'account_id' => $defaultAccount->account_id,
                'total_accounts' => $userAccounts->count(),
                'server' => gethostname()
            ]);

            return true;
        }

        return true;
    }

    public function syncAccountSessionFromCookie(): bool
    {
        $cookieAccount = $this->getAccountCookie();
        $sessionAccount = $this->getSessionAccountId();

        if ($cookieAccount && $cookieAccount != $sessionAccount) {
            $this->setAccountSessionData($cookieAccount);

            Log::debug('User context synced: session updated from cookie', [
                'user_id' => auth()->id(),
                'server' => gethostname()
            ]);

            return true;
        }

        return false;
    }

    public function syncAccountCookieFromSession(): bool
    {
        $cookieAccount = $this->getAccountCookie();
        $sessionAccount = $this->getSessionAccountId();

        if (!$cookieAccount && !empty($sessionAccount)) {
            $userAccountIds = $this->getUserAccountIds();

            if (in_array($sessionAccount, $userAccountIds)) {
                $this->setAccountCookie($sessionAccount);

                Log::debug('User context synced: cookie regenerated from session', [
                    'user_id' => auth()->id(),
                    'server' => gethostname()
                ]);

                return true;
            } else {
                Log::warning('User context: invalid account in session, clearing context', [
                    'user_id' => auth()->id()
                ]);
                $this->clearAccountSession();

                return false;
            }
        }

        return false;
    }

    public function setAccountSessionData(int $accountId): Account
    {
        $account = $this->getAccountById($accountId);

        if (!$account) {
            logoutUser();
        }

        if (!Auth::check()) {
            Log::error("User context: no accounts assigned, cannot initialize", []);
            throw new \Exception('Attempt to set account session data without active user session');
        }

        $userAccountIds = $this->getUserAccountIds();

        if (!in_array($accountId, $userAccountIds)) {
            Log::warning('Unauthorized account access attempt', [
                'user_id' => Auth::id(),
                'attempted_account_id' => $accountId
            ]);

            $this->logoutActiveUser();
            abort(403, 'Unauthorized account access');
        }

        $sessionAccount = [
            'id' => $accountId,
            'slug' => ($account->account_slug ? $account->account_slug : 'system')
        ];

        $profileData = $account->account_translation;
        session(['account' => $sessionAccount]);
        session(['translations' => $profileData['translations'], []]);
        session(['languages' => $profileData['languages'], []]);
        $userLanguage = $this->languageService->getUserLanguage(Auth::user(), $account);
        session(['locale' => $userLanguage]);

        session()->save();

        return $account;
    }

    public function switchAccount(int $accountId): Account
    {
        $account = $this->setAccountSessionData($accountId);
        $this->setAccountCookie($accountId);
        return $account;
    }

    public function initializeAccountContext(): array
    {
        try {
            $accountId = $this->getSessionAccountId();
            if (!$accountId) {
                throw new \Exception('No account in session');
            }

            $account = $this->getAccountById($accountId);
            if (!$account) {
                throw new \Exception('Account not found');
            }

            $locale = session('locale', 'en');
            $profile = $account->account_translation;
            $translations = session('translations.' . $locale, []);

            return [
                'account' => $account,
                'locale' => $locale,
                'profile' => $profile,
                'translations' => $translations
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getCurrentAccount(): ?Account
    {
        try {
            $accountId = $this->getSessionAccountId();
            if (!$accountId) {
                return null;
            }
            return $this->getAccountById($accountId);
        } catch (\Throwable $e) {
            Log::error('Failed to get current account', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function getUserAccounts()
    {
        if ($this->cachedUserAccounts === null) {
            $userAccounts = Auth::user()?->accounts ?? collect();
            $this->cachedUserAccounts = $userAccounts;
            $this->cachedUserAccountIds = $userAccounts->pluck('account_id')->toArray();
        }

        return $this->cachedUserAccounts;
    }

    public function logoutActiveUser(): void
    {
        $this->clearAccountCookie();
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    private function getUserAccountIds(): array
    {
        if ($this->cachedUserAccountIds === null) {
            $this->getUserAccounts();
        }

        return $this->cachedUserAccountIds;
    }

    private function getAccountById(int $accountId): ?Account
    {
        if (!isset($this->cachedAccountsById[$accountId])) {
            $this->cachedAccountsById[$accountId] = Account::query()
                ->where('account_id', $accountId)
                ->first();
        }

        return $this->cachedAccountsById[$accountId];
    }
}
