<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ErrorDigest extends Notification
{
    protected $errors;
    protected $period;

    public function __construct(Collection $errors, string $period)
    {
        $this->errors = $errors;
        $this->period = $period;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    private function getFirstStackTraceLine(string $stackTrace): string
    {
        $lines = explode("\n", $stackTrace);
        foreach ($lines as $line) {
            // Look for a line containing file path and line number
            if (preg_match('/#\d+\s+(.+?):(\d+)/', $line, $matches)) {
                return $matches[1] . ' line ' . $matches[2];
            }
        }
        return 'Stack trace not available';
    }

    public function toMail($notifiable)
    {
        $environment = env('REPORTING_ENVIRONMENT') ?: 'Unknown';

        $mailMessage = (new MailMessage)
            ->subject("Error Summary for {$this->period}. Environment: {$environment}")
            ->greeting("Error reporting for env: {$environment}")
            ->line("Here is a summary of errors that occurred in the last {$this->period}:");

        // Group errors by message
        $groupedErrors = $this->errors->groupBy('message');

        foreach ($groupedErrors as $message => $errors) {
            $mailMessage->line("\nError: " . $message);
            $mailMessage->line("Occurrences: " . $errors->count());

            // Show details of the most recent occurrence
            $latest = $errors->sortByDesc('timestamp')->first();
            $mailMessage->line("Latest occurrence: " . $latest['timestamp']->format('Y-m-d H:i:s'));

            if (!empty($latest['context']['userId'])) {
                $mailMessage->line("User ID: " . $latest['context']['userId']);
            }

//            if (!empty($latest['stack_trace'])) {
//                $mailMessage->line("Location: " . $this->getFirstStackTraceLine($latest['stack_trace']));
//            }

            $mailMessage->line("---");
        }

        return $mailMessage;
    }
}