<?php

namespace App\Console\Commands;

use App\Mail\SendInvoices;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use SimpleExcel\SimpleExcel;

class InvoiceToExcelExport extends Command
{
    protected $signature = 'invoice:export-excel
                            {--account= : Account name to export invoice for. If not specified all existing accounts will be taken to export}
                            {--month= : Month to take for export in a format YYYY-MM. If not specified last finished calendar month will be taken to export}';

    protected $description = 'Command exports invoices from db to excel and sends email';

    private $month;
    private $invoicePeriodStart;
    private $invoicePeriodEnd;
    private $invoiceDirectory;
    private $accounts = [];
    private $invoiceData = [];
    private $invoiceHeaders = [
        'start',
        'end',
        'anumber',
        'bnumber',
        'type',
        'session',
        'path',
        'answered'
    ];

    public function __construct()
    {
        parent::__construct();
        ini_set('memory_limit', '1G');
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        $this->info('Setting invoice generation context');
        $this->setInvoiceContext();
        $this->setDirectoryContext();
        $this->info('Importing invoice data from database');
        $this->importInvoicesFromDB();
        $this->info('Saving invoice data into xml files');
        $this->exportInvoicesToExcel();
        $this->info('Sending generated invoice files to email');
        $this->sendInvoicesToEmail();
        $this->info('Finished task');
    }

    protected function setInvoiceContext(): void
    {
        $this->setAccountContext();
        $this->setPeriodContext();
    }

    protected function importInvoicesFromDB(): void
    {
        foreach ($this->accounts as $account) {
            $this->invoiceData[$account] = $this->makeInvoiceQueryForAccount($account)->get();
        }
    }

    protected function exportInvoicesToExcel(): void
    {
        $excel = new SimpleExcel('xml');

        foreach ($this->invoiceData as $account => $invoiceData) {
            $invoiceData = $this->convertToArray($invoiceData);

            if (empty($invoiceData)) {
                $this->warn("No data for account {$account}. Omitting invoice generation for this client.");
                continue;
            }
            if (! $this->validateHeaders($invoiceData)) {
                $this->warn("Headers are invalid for account: {$account}. Omitting invoice generation for this client.");
                continue;
            }

            $excel->writer->setData(array_merge([$this->invoiceHeaders], $invoiceData));
            $excel->writer->saveFile("{$account}.xml", $this->invoiceDirectory . "{$account}.xml");
        }
    }

    protected function sendInvoicesToEmail(): void
    {
        try {
            $attachments = array_diff(scandir($this->invoiceDirectory), ['.', '..']);
            $attachments = array_map(function ($file) {
                return $this->invoiceDirectory . $file;
            }, $attachments);

//            $recipient = env('SEND_INVOICES_EMAIL');
            $recipients = ['accounts@serv24.com', 'alejandro.monje@serv24.com', 'jacek.dziurdzikowski@serv24.com'];
//            $this->info("Attempting to send email to: " . $recipients);
            $this->info("Attempting to send email to: " . implode(', ', $recipients));

            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new SendInvoices($this->month, $attachments));
            }

//            if (Mail::failures()) {
//                throw new RuntimeException('Failed to send emails: ' . implode(', ', Mail::failures()));
//            }

            $this->info('Emails sent successfully');
        } catch (\Exception $e) {
            $this->error('Failed to send emails: ' . $e->getMessage());
            throw $e;
        }
    }
    protected function makeInvoiceQueryForAccount(string $account): Builder
    {
        $table = 'sessions';

        $select = "MAX(CASE WHEN et_type = 'ANSWER' THEN event_value ELSE session_start END) as `start`,
           MAX(CASE WHEN et_type = 'END' THEN event_value ELSE session_end END) as `end`,
           MAX(CASE WHEN et_type = 'ANUMBER' THEN REPLACE(event_value, '+', '00') END) as `anumber`,
           MAX(CASE WHEN et_type = 'BNUMBER' THEN REPLACE(event_value, '+', '00') END) as `bnumber`,
           st_type as `type`,
           session_id as `session`,
           sp_type as `path`,
           MAX(CASE WHEN et_type = 'ANSWER' OR sp_type = 'SMS' THEN 1 ELSE 0 END) as `answered`";

        $joins = [
            ['session_paths', 'session_sp_id', '=', 'sp_id'],
            ['session_directions', 'session_sd_id', '=', 'sd_id'],
            ['session_types', 'session_st_id', '=', 'st_id'],
            ['accounts', 'session_account_id', '=', 'account_id'],
            ['events', 'event_session_id', '=', 'session_id'],
            ['event_types', [
                ['event_et_id', '=', 'et_id'],
                ['et_type', 'IN', ['ANUMBER','BNUMBER','ANSWER']],
            ]],
        ];

        $where = "account_slug = '{$account}'
          and session_start >= '{$this->invoicePeriodStart}'
          and session_start < '{$this->invoicePeriodEnd}'
          and sd_type = 'OUTBOUND'";

        $groupBy = "session_id";
        $orderBy = "session_id";

        $query = DB::table($table)
            ->select(DB::raw($select))
            ->whereRaw(DB::raw($where));
        foreach ($joins as $join) {
            if (is_array($join[1])) {
                $query->join($join[0], function (JoinClause $joiner) use ($join) {
                    $joiner->on(...$join[1][0])->whereIn($join[1][1][0], $join[1][1][2]);
                });
            } else {
                $query->join(...$join);
            }
        }
        $query->groupBy($groupBy);
        $query->orderBy($orderBy);

        return $query;
    }

    protected function convertToArray(Collection $collection): array
    {
        $array = $collection->toArray();
        foreach ($array as &$item) {
            $item = (array) $item;
        }
        return $array;
    }

    protected function validateHeaders(array $array): bool
    {
        $firstItem = reset($array);
        if (is_array($firstItem) && $this->invoiceHeaders === array_keys($firstItem)) {
            return true;
        }
        return false;
    }

    protected function setAccountContext(): void
    {
        $account = $this->option('account');
        if ($account) {
            $account = DB::table('accounts')->where('account_name', $account)->pluck('account_slug')->toArray();
            if (count($account) !== 1) {
                throw new InvalidArgumentException("Account: {$account} not found");
            }
            $this->accounts = [$account];
        } else {
            $this->accounts = DB::table('accounts')->pluck('account_slug')->toArray();
        }
    }

    protected function setPeriodContext(): void
    {
        $month = $this->option('month');
        if ($month) {
            if (!preg_match("/^[0-9]{4}(-|\/)([1-9]|0[1-9]|1[0-2])$/", $month)) {
                throw new InvalidArgumentException('Invalid month format - please provide format YYYY-MM');
            }
            $this->month = $month;
            $this->invoicePeriodStart = date("Y-n-j", strtotime("first day of {$month}"));
            $this->invoicePeriodEnd = date("Y-n-j", strtotime("first day of {$month} + 1 month"));
        } else {
            $this->month = date("Y-n", strtotime("previous month"));
            $this->invoicePeriodStart = date("Y-n-j", strtotime("first day of previous month"));
            $this->invoicePeriodEnd = date("Y-n-j", strtotime("first day of this month"));
        }
    }

    protected function setDirectoryContext(): void
    {
        $this->invoiceDirectory = env('SEND_INVOICES_DIR') . $this->month . DIRECTORY_SEPARATOR;
        if (!is_dir($this->invoiceDirectory)) {
            if (!mkdir($this->invoiceDirectory, 0754, true) && !is_dir($this->invoiceDirectory)) {
                throw new RuntimeException("Cannot create invoices directory: {$this->invoiceDirectory}");
            }
        }
    }
}
