<?php

namespace App\Services\Import;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class ImportParserService
{
    private const ALLOWED_EXTENSIONS = ['csv', 'xlsx', 'xls'];
    private const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB

    public function __construct()
    {
        ini_set('max_execution_time', 120);
    }

    public function parse(UploadedFile $file): array
    {
        // Validate file
        $this->validateFile($file);

        // Get extension
        $extension = strtolower($file->getClientOriginalExtension());

        // Parse based on extension
        if ($extension === 'csv') {
            return $this->parseCsv($file);
        } else {
            return $this->parseExcel($file);
        }
    }

    private function validateFile(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception(trans('Uploaded file is invalid'));
        }

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception(trans('File size exceeds maximum allowed size of :size MB', [
                'size' => self::MAX_FILE_SIZE / 1024 / 1024
            ]));
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new Exception(trans('Invalid file format. Allowed formats: :formats', [
                'formats' => implode(', ', self::ALLOWED_EXTENSIONS)
            ]));
        }

        // Check mime type
        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception(trans('Invalid file type'));
        }
    }

    private function parseCsv(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            // Read headers
            $headers = fgetcsv($handle, 0, ',');

            if ($headers === false || empty($headers)) {
                fclose($handle);
                throw new Exception(trans('CSV file is empty or has invalid headers'));
            }

            // Clean headers and remove BOM if present
            $headers = array_map(function($h) {
                // Remove UTF-8 BOM (0xEF 0xBB 0xBF) if present
                $h = str_replace("\xEF\xBB\xBF", '', $h);
                return trim($h);
            }, $headers);

            // Add headers as first row
            $rows[] = array_combine($headers, $headers);

            // Read data rows
            $rowNumber = 1;
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $rowNumber++;

                // Skip empty rows
                if (count(array_filter($data)) === 0) {
                    continue;
                }

                // Ensure data has same number of columns as headers
                if (count($data) < count($headers)) {
                    $data = array_pad($data, count($headers), '');
                } elseif (count($data) > count($headers)) {
                    $data = array_slice($data, 0, count($headers));
                }

                // Convert encoding if needed
                $data = array_map(function($value) {
                    return mb_convert_encoding($value, 'UTF-8', 'auto');
                }, $data);

                // Combine with headers
                $rows[] = array_combine($headers, $data);
            }

            fclose($handle);
        } else {
            throw new Exception(trans('Failed to open CSV file'));
        }

        return $rows;
    }

    private function parseExcel(UploadedFile $file): array
    {
        try {
            $path = $file->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();

            // Convert to array
            $data = $sheet->toArray(null, true, true, true);

            if (empty($data)) {
                throw new Exception(trans('Excel file is empty'));
            }

            // Get headers from first row
            $headers = array_shift($data);
            $headers = array_map(fn($h) => trim($h), array_filter($headers));

            if (empty($headers)) {
                throw new Exception(trans('Excel file has invalid headers'));
            }

            $rows = [];

            // Add headers as first row (for structure validation)
            $rows[] = array_combine($headers, $headers);

            // Process data rows
            foreach ($data as $rowData) {
                // Skip completely empty rows
                if (count(array_filter($rowData)) === 0) {
                    continue;
                }

                // Get only the columns we have headers for
                $rowData = array_slice($rowData, 0, count($headers));

                // Pad if necessary
                if (count($rowData) < count($headers)) {
                    $rowData = array_pad($rowData, count($headers), '');
                }

                // Clean and convert values
                $rowData = array_map(function($value) {
                    if ($value === null) {
                        return '';
                    }
                    return trim((string)$value);
                }, $rowData);

                // Combine with headers
                $rows[] = array_combine($headers, $rowData);
            }

            return $rows;

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            throw new Exception(trans('Failed to parse Excel file: :error', [
                'error' => $e->getMessage()
            ]));
        }
    }

    public function preview(UploadedFile $file, int $limit = 5): array
    {
        $allRows = $this->parse($file);

        // Return headers + limited data rows
        if (count($allRows) <= $limit + 1) {
            return $allRows;
        }

        return array_slice($allRows, 0, $limit + 1);
    }
}
