<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class AnalyzeTranslations extends Command
{
    protected $signature = 'translations:analyze_old 
        {--clean : Usuń nieużywane tłumaczenia}
        {--show-details : Pokaż szczegółową listę wszystkich znalezionych tłumaczeń}';

    protected $description = 'Analizuje i znajduje nieużywane tłumaczenia w aplikacji';

    private $usedTranslations = [];
    private $definedTranslations = [];
    private $unusedTranslations = [];
    private $missingTranslations = [];

    public function handle()
    {
        $this->info('Rozpoczynam analizę tłumaczeń...');

        // 1. Zbierz wszystkie zdefiniowane tłumaczenia
        $this->collectDefinedTranslations();

        // 2. Przeanalizuj kod w poszukiwaniu użytych tłumaczeń
        $this->findUsedTranslations();

        // 3. Znajdź nieużywane i brakujące tłumaczenia
        $this->analyzeTranslations();

        // 4. Wyświetl raport
        $this->displayReport();

        // 5. Opcjonalnie wyczyść nieużywane tłumaczenia
        if ($this->option('clean')) {
            $this->cleanUnusedTranslations();
        }
    }

    private function collectDefinedTranslations()
    {
        $langPath = resource_path('lang');

        foreach (File::directories($langPath) as $langDir) {
            $locale = basename($langDir);

            foreach (File::allFiles($langDir) as $file) {
                $group = basename($file->getFilename(), '.php');
                try {
                    $translations = require $file->getPathname();

                    if (!is_array($translations)) {
                        $this->error("Błąd: Plik {$file->getPathname()} nie zwraca tablicy.");
                        $this->line("Zwrócony typ: " . gettype($translations));
                        continue;
                    }

                    $this->flattenTranslations($translations, $locale, $group);
                } catch (\Throwable $e) {
                    $this->error("Błąd podczas wczytywania pliku {$file->getPathname()}:");
                    $this->error($e->getMessage());
                    continue;
                }
            }
        }
    }

    private function flattenTranslations($array, $locale, $group, $prefix = '')
    {
        if (!is_array($array)) {
            $this->warn("Ostrzeżenie: Otrzymano " . gettype($array) . " zamiast tablicy dla klucza: {$group}" . ($prefix ? ".{$prefix}" : ""));
            return;
        }

        foreach ($array as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $this->flattenTranslations($value, $locale, $group, $fullKey);
            } elseif (is_string($value) || is_numeric($value)) {
                $this->definedTranslations[$locale][$group . '.' . $fullKey] = (string)$value;
            } else {
                $this->warn("Ostrzeżenie: Nieobsługiwany typ wartości (" . gettype($value) . ") dla klucza: {$group}.{$fullKey}");
            }
        }
    }

    private function findUsedTranslations()
    {
        $finder = new Finder();
        $finder->files()
               ->in(base_path())
               ->exclude(['vendor', 'storage', 'bootstrap/cache'])
               ->name('*.php')
               ->name('*.blade.php');

        foreach ($finder as $file) {
            $content = $file->getContents();

            // Szukaj różnych wzorców używania tłumaczeń
            $this->findTranslationPatterns($content);
        }

        // Usuń duplikaty
        $this->usedTranslations = array_unique($this->usedTranslations);
    }

    private function findTranslationPatterns($content)
    {
        // Wzorce do wyszukiwania:
        $patterns = [
            // __('key')
            '/\b__\([\'"]([^\'"]+)[\'"]\)/',

            // trans('key')
            '/\btrans\([\'"]([^\'"]+)[\'"]\)/',

            // @lang('key')
            '/@lang\([\'"]([^\'"]+)[\'"]\)/',

            // {{ __('key') }}
            '/\{\{\s*__\([\'"]([^\'"]+)[\'"]\)\s*\}\}/',

            // Lang::get('key')
            '/Lang::get\([\'"]([^\'"]+)[\'"]\)/',

            // trans_choice('key', $count)
            '/trans_choice\([\'"]([^\'"]+)[\'"]\s*,/',

            // __choice('key', $count)
            '/__choice\([\'"]([^\'"]+)[\'"]\s*,/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $key) {
                    $this->usedTranslations[] = $key;
                }
            }
        }
    }

    private function analyzeTranslations()
    {
        foreach ($this->definedTranslations as $locale => $translations) {
            foreach ($translations as $key => $value) {
                if (!in_array($key, $this->usedTranslations)) {
                    $this->unusedTranslations[$locale][] = $key;
                }
            }
        }

        foreach ($this->usedTranslations as $key) {
            foreach (array_keys($this->definedTranslations) as $locale) {
                if (!isset($this->definedTranslations[$locale][$key])) {
                    $this->missingTranslations[$locale][] = $key;
                }
            }
        }
    }

    private function displayReport()
    {
        $this->info("\nRaport analizy tłumaczeń:");

        foreach ($this->definedTranslations as $locale => $translations) {
            $this->line("\nJęzyk: " . $locale);
            $this->line("Całkowita liczba tłumaczeń: " . count($translations));

            if (isset($this->unusedTranslations[$locale])) {
                $unusedCount = count($this->unusedTranslations[$locale]);
                $this->warn("Nieużywane tłumaczenia: {$unusedCount} (" .
                    round(($unusedCount / count($translations)) * 100, 2) . "%)");

                if ($this->option('show-details') && $unusedCount > 0) {
                    $this->line("\nLista nieużywanych tłumaczeń:");
                    foreach ($this->unusedTranslations[$locale] as $key) {
                        $this->line(" - " . $key . " = " . $this->definedTranslations[$locale][$key]);
                    }
                }
            }

            if (isset($this->missingTranslations[$locale])) {
                $missingCount = count($this->missingTranslations[$locale]);
                $this->error("Brakujące tłumaczenia: {$missingCount}");

                if ($this->option('show-details') && $missingCount > 0) {
                    $this->line("\nLista brakujących tłumaczeń:");
                    foreach ($this->missingTranslations[$locale] as $key) {
                        $this->line(" - " . $key);
                    }
                }
            }
        }
    }

    private function cleanUnusedTranslations()
    {
        if (!$this->confirm('Czy na pewno chcesz usunąć nieużywane tłumaczenia?')) {
            return;
        }

        foreach ($this->unusedTranslations as $locale => $keys) {
            $langPath = resource_path("lang/{$locale}");

            foreach ($keys as $key) {
                list($file, $translationKey) = explode('.', $key, 2);
                $filePath = "{$langPath}/{$file}.php";

                if (File::exists($filePath)) {
                    $translations = require $filePath;
                    if (!is_array($translations)) {
                        $this->error("Pomijam plik {$filePath} - nie zwraca tablicy");
                        continue;
                    }

                    $this->removeTranslationKey($translations, $translationKey);

                    // Zapisz zaktualizowane tłumaczenia
                    $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
                    File::put($filePath, $content);
                }
            }
        }

        $this->info('Nieużywane tłumaczenia zostały usunięte.');
    }

    private function removeTranslationKey(&$array, $key)
    {
        $parts = explode('.', $key);
        $last = array_pop($parts);

        foreach ($parts as $part) {
            if (!isset($array[$part]) || !is_array($array[$part])) {
                return;
            }
            $array = &$array[$part];
        }

        unset($array[$last]);
    }
}