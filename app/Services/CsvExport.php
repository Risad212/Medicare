<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExport
{
    /**
     * Stream CSV content to the browser (UTF-8 BOM so Excel shows accents correctly).
     */
    public function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_map([$this, 'escapeCell'], (array) $row));
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Neutralise CSV formula injection: a cell starting with = + - @ (or a
     * tab/CR) would execute as a formula when the export is opened in Excel.
     * Names and phone numbers come from user input, so escape them.
     */
    private function escapeCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
