<?php

namespace App\Services;

use Illuminate\Support\Str;
use Closure;

class TranslationsService
{
    public function __construct(
        private readonly ProfileAccessService $profileService
    ) {}

    public function getTranslations(array $subjectsWithPrefixes = []): array
    {
        $subjectsWithPaths = [
            'settings' => ['device', 'setting'],
            'initial' => ['device', 'initial'],
            'call' => ['call'],
            'form' => ['device', 'field'],
            'alert' => ['alert', 'type'],
        ];

        $profileData = $this->profileService->getProfileData();
        $languages = array_merge(['default'], array_keys($profileData['languages']));
        $translationsBuilder = $this->getTranslationsBuilder();

        $requestedSubjects = !empty($subjectsWithPrefixes)
            ? array_intersect_key($subjectsWithPaths, $subjectsWithPrefixes)
            : $subjectsWithPaths;

        return $this->buildTranslations(
            $requestedSubjects,
            $languages,
            $profileData['translations'],
            $subjectsWithPrefixes,
            $translationsBuilder
        );
    }

    public function getSettingTranslations(string $locale = 'default', string $prefix = ''): array
    {
        return $this->getLocalizedTranslations(
            $this->getTranslations(['settings' => $prefix]),
            $locale,
            fn($field) => __('settings.label.device_' . $field)
        );
    }

    public function getFieldTranslations(string $locale = 'default', ?string $prefix = null): array
    {
        return $this->getLocalizedTranslations(
            $this->getTranslations(['form' => $prefix]),
            $locale,
            fn($field) => __('settings.label.device_field_' . Str::after($field, $prefix . '_'))
        );
    }

    public function getAlertTranslations(string $locale = 'default'): array
    {
        return $this->getLocalizedTranslations(
            $this->getTranslations(['alert' => '']),
            $locale,
            fn($field) => $field
        );
    }

    private function buildTranslations(
        array $subjects,
        array $languages,
        array $translations,
        array $prefixes,
        Closure $builder
    ): array {
        $output = [];

        foreach ($subjects as $subject => $path) {
            foreach ($languages as $lang) {
                $langTranslations = $this->arrayAccessor($path, $translations[$lang]);
                $langTranslations = $builder($langTranslations);

                foreach ($langTranslations as $key => $value) {
                    $prefix = $prefixes[$subject] ?? '';
                    $outputKey = !empty($prefix) ? "{$prefix}_{$key}" : $key;
                    $output[$outputKey][$lang] = $value;
                }
            }
        }

        return $output;
    }

    private function getLocalizedTranslations(
        array $translations,
        string $locale,
        callable $fallback
    ): array {
        $output = [];

        foreach ($translations as $field => $fieldTranslations) {
            $output[$field] = $fieldTranslations[$locale]
                ?? $fieldTranslations['default']
                ?? $fallback($field);
        }

        return $output;
    }

    private function arrayAccessor(array $path, ?array $array): ?array
    {
        foreach ($path as $key) {
            $array = $array[$key] ?? null;
            if ($array === null) {
                return null;
            }
        }
        return $array;
    }

    private function getTranslationsBuilder(): Closure
    {
        return function ($inputTranslations, $prefixKey = null) use (&$builder): array {
            $output = [];

            foreach ($inputTranslations as $key => $value) {
                $newKey = empty($prefixKey) ? $key : "{$prefixKey}_{$key}";

                if (is_array($value)) {
                    $output = array_merge($output, $builder($value, $newKey));
                } else {
                    $output[$newKey] = $value;
                }
            }

            return $output;
        };
    }
}