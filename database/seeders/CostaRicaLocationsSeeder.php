<?php

namespace Database\Seeders;

use App\Models\Canton;
use App\Models\District;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CostaRicaLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $basePath = database_path('seeders/data');

        $provinceRows = $this->readCsv($basePath.'/adm1-provincias.csv');
        $cantonRows = $this->readCsv($basePath.'/adm2-cantones.csv');
        $districtRows = $this->readCsv($basePath.'/adm3-distritos.csv');

        $provinceIdsByCode = [];
        foreach ($provinceRows as $row) {
            $province = Province::updateOrCreate(
                ['code' => (string) $row['codigo']],
                ['name' => $this->normalizeName((string) $row['nombre'])]
            );

            $provinceIdsByCode[$province->code] = $province->id;
        }

        $cantonIdsByCode = [];
        foreach ($cantonRows as $row) {
            $provinceCode = (string) $row['provincia'];
            if (! isset($provinceIdsByCode[$provinceCode])) {
                continue;
            }

            $canton = Canton::updateOrCreate(
                ['code' => (string) $row['codigo']],
                [
                    'province_id' => $provinceIdsByCode[$provinceCode],
                    'name' => $this->normalizeName((string) $row['nombre']),
                ]
            );

            $cantonIdsByCode[$canton->code] = $canton->id;
        }

        foreach ($districtRows as $row) {
            $cantonCode = (string) $row['canton'];
            if (! isset($cantonIdsByCode[$cantonCode])) {
                continue;
            }

            District::updateOrCreate(
                ['code' => (string) $row['codigo']],
                [
                    'canton_id' => $cantonIdsByCode[$cantonCode],
                    'name' => $this->normalizeName((string) $row['nombre']),
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readCsv(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $handle = fopen($path, 'rb');
        if (! $handle) {
            return [];
        }

        $rows = [];
        $headers = [];

        while (($data = fgetcsv($handle)) !== false) {
            if ($headers === []) {
                $headers = array_map(fn ($value) => $this->normalizeKey((string) $value), $data);
                continue;
            }

            if ($data === [null] || $data === false) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($data[$index] ?? ''));
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeKey(string $value): string
    {
        $value = $this->decodeText($value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], ['a', 'e', 'i', 'o', 'u', 'n', '_'], $value);
        $value = preg_replace('/[^a-z0-9_]/', '', $value) ?? '';

        return $value;
    }

    private function normalizeName(string $value): string
    {
        $value = $this->decodeText($value);
        $value = trim($value);

        return match ($value) {
            'Guancaste' => 'Guanacaste',
            default => $value,
        };
    }

    private function decodeText(string $value): string
    {
        $detected = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) ?: 'UTF-8';

        return trim(mb_convert_encoding($value, 'UTF-8', $detected));
    }
}
