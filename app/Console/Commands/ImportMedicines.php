<?php

namespace App\Console\Commands;

use App\Models\Medicine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportMedicines extends Command
{
    protected $signature = 'medicines:import {path}';
    protected $description = 'Bulk import medicines from a CSV file';

    public function handle()
    {
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $batch = [];
        $total = 0;

        $this->info('Importing... this may take a minute.');
        $bar = $this->output->createProgressBar();

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            $batch[] = [
                'brand_name'       => $data['brand_name'],
                'generic_name'     => $data['generic_name'],
                'strength'         => $data['strength'] ?: null,
                'company'          => $data['company'] ?: null,
                'dosage_form'      => $data['dosage_form'] ?: 'Tablet',
                'category'         => $data['category'] ?: null,
                'shortcut'         => $data['shortcut'] ?: null,
                'usage_count'      => (int) $data['usage_count'],
                'status'           => (bool) $data['status'],
                'is_sponsored'     => (bool) $data['is_sponsored'],
                'sponsor_company'  => $data['sponsor_company'] ?: null,
                'sponsor_priority' => (int) $data['sponsor_priority'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('medicines')->insert($batch);
                $total += count($batch);
                $batch = [];
                $bar->advance(500);
            }
        }

        if (!empty($batch)) {
            DB::table('medicines')->insert($batch);
            $total += count($batch);
        }

        fclose($handle);
        $bar->finish();
        $this->newLine();
        $this->info("Imported {$total} medicines.");
    }
}