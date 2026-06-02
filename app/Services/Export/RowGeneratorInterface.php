<?php

namespace App\Services\Export;

interface RowGeneratorInterface
{
    /**
     * Return column headers for this export type.
     * Called by ExportJob before generate() so the header reaches ExportService::writeFile().
     */
    public function getHeader(array $params): array;

    /**
     * Return the list of $params keys that must be present before the job runs.
     * ExportController calls this to validate input before dispatching the job.
     * Return an empty array if all params are optional.
     */
    public function requiredParams(): array;

    /**
     * The implementation may write intermediate progress (0–99) to $progressFile.
     * ExportJob always writes the final 100 after this method returns.
     * Small generators that do not track progress should ignore $progressFile.
     */
    public function generate(array $params, string $progressFile): \Generator;
}
