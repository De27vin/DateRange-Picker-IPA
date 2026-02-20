<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendInvoices extends Mailable
{
    use Queueable, SerializesModels;

    private $month;
    private $invoices = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $month, array $invoices)
    {
        $this->month = $month;
        $this->invoices = $invoices;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->view('emails.empty_content');
        $this->subject("Invoices from {$this->month}");

        foreach ($this->invoices as $invoice) {
            $this->attach($invoice);
        }

        return $this;
    }
}
