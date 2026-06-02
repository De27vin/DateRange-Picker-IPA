<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExportReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private string $filePath,
        private string $exportType,
        private string $format
    ) {}

    public function build(): self
    {
        $filename = 'export_' . $this->exportType . '_' . date('Y-m-d') . '.' . $this->format;

        $mime = $this->format === 'csv'
            ? 'text/csv'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return $this
            ->subject('Your ' . ucfirst($this->exportType) . ' export is ready')
            ->view('emails.export_ready', [
                'exportType' => $this->exportType,
                'format'     => strtoupper($this->format),
            ])
            ->attach($this->filePath, [
                'as'   => $filename,
                'mime' => $mime,
            ]);
    }
}
