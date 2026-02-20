<?php

namespace App\PasswordBroker;

use App\Models\Email;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Support\Carbon;

class CustomDatabaseTokenRepository extends DatabaseTokenRepository
{
    public function recentlyCreatedTokenByEmail(string $email)
    {
        $emailId = Email::whereEmailAddress($email)->first()->email_id;

        $record = (array) $this->getTable()->where(
            'pr_email_id', $emailId
        )->first();

        return $record && $this->tokenRecentlyCreated($record['pr_created']);
    }

    public function createFromEmail(string $email)
    {
        $emailId = Email::whereEmailAddress($email)->first()->email_id;

        $this->deleteExistingEmailId($emailId);

        $token = $this->createNewToken();

        $this->getTable()->insert([
            'pr_email_id' => $emailId,
            'pr_token' => $this->hasher->make($token),
            'pr_created' => new Carbon
        ]);

        return $token;
    }

    protected function deleteExistingEmailId(int $emailId)
    {
        return $this->getTable()->where('pr_email_id', $emailId)->delete();
    }

    public function existsByEmail(string $email, $token)
    {
        $emailId = Email::whereEmailAddress($email)->first()->email_id;

        $record = (array) $this->getTable()->where(
            'pr_email_id', $emailId
        )->first();

        return $record &&
            ! $this->tokenExpired($record['pr_created']) &&
            $this->hasher->check($token, $record['pr_token']);
    }

    public function deleteExistingByEmail(string $email)
    {
        $emailId = Email::whereEmailAddress($email)->first()->email_id;

        return $this->getTable()->where('pr_email_id', $emailId)->delete();
    }

    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);

        $this->getTable()->where('pr_created', '<', $expiredAt)->delete();
    }
}
