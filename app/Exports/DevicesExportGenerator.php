<?php

namespace App\Exports;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Generator-based Excel export that can update a simple progress file while rows are being yielded. This
 * allows the frontend to poll for export progress even while the spreadsheet is still being built.
 */
class DevicesExportGenerator implements FromGenerator, WithHeadings, ShouldAutoSize, WithStyles
{
    private $query;
    private $headers;
    private $callback;
    private $progressFile;
    private $totalRows;

    public function __construct($query, $headers, callable $callback, ?string $progressFile = null, int $totalRows = 0)
    {
        $this->query = $query;
        $this->headers = $headers;
        $this->callback = $callback;
        $this->progressFile = $progressFile;
        $this->totalRows = $totalRows;

        // Initialise progress file if provided
        if ($this->progressFile) {
            @file_put_contents($this->progressFile, '0');
        }
    }

    public function generator(): Generator
    {
        $processed = 0;
        foreach ($this->query->cursor() as $item) {
            $result = call_user_func($this->callback, $item, $this->headers);
            
            // If callback returns an array of rows (for site exports), yield each one
            if (is_array($result) && isset($result[0]) && is_array($result[0])) {
                foreach ($result as $row) {
                    yield $row;

                    // update progress for each yielded site-device row
                    $processed++;
                    $this->updateProgress($processed);
                }
            } else {
                // Single row
                yield $result;

                $processed++;
                $this->updateProgress($processed);
            }
        }

        // Progress reaches 100 after Excel::store finishes (handled in job).
    }

    private function updateProgress(int $processed): void
    {
        if (!$this->progressFile || $this->totalRows === 0) {
            return;
        }

        // write only every 50 rows to reduce IO
        if ($processed % 50 !== 0 && $processed !== $this->totalRows) {
            return;
        }

        $percent = (int) round(($processed / $this->totalRows) * 100);
        @file_put_contents($this->progressFile, $percent);
    }

    public function headings(): array
    {
        // The headers property may contain an associative array where the keys are the internal field names
        // and the values are the translated column titles. For the spreadsheet heading row we only need the
        // visible labels, so we return the values while preserving their original order.
        return array_values($this->headers);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}