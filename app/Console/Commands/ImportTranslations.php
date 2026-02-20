<?php

namespace app\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;


// USE THIS IMPORT TO IMPORT FROM EXCEL TO JSON
class ImportTranslations extends Command
{
    protected $signature = 'translations:import {excel}';
    protected $description = 'Import translations from Excel file';

    private $columnMap = [
        'Identifier' => 4,  // Column E
        'EN' => 8,         // Column I
        'DE' => 9,         // Column J
        'FR' => 10,         // Column K
        'ES' => 11,         // Column L
        'IT' => 12,         // Column M
        'PT' => 13          // Column N
    ];

    public function handle()
    {
        $this->info('Starting translation import...');

        try {
            $excelPath = $this->argument('excel');
            $collection = Excel::toCollection(null, $excelPath);
            $data = $collection->first();

            if ($data->isEmpty()) {
                $this->error('Excel file is empty');
                return 1;
            }

            // Skip the header row
            $data = $data->slice(1);

            // Process each language
            foreach (['EN', 'DE', 'FR', 'ES', 'IT', 'PT'] as $lang) {
                $translations = [];
                foreach ($data as $row) {
                    $identifier = trim($row[$this->columnMap['Identifier']]);
                    $translation = trim($row[$this->columnMap[$lang]]);

                    if (!empty($identifier) && !empty($translation)) {
                        $translations[$identifier] = $translation;
                    }
                }

                // Save translations
                $this->saveTranslations(strtolower($lang), $translations);
                $this->info("Processed " . strtolower($lang) . " translations: " . count($translations));
            }

            $this->info('Translation import completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function saveTranslations($lang, $translations)
    {
        $langPath = resource_path("lang/$lang.json");

        // Create language directory if it doesn't exist
        if (!File::exists(dirname($langPath))) {
            File::makeDirectory(dirname($langPath), 0755, true);
        }

        ksort($translations);

        // Save translations
        File::put(
            $langPath,
            json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}