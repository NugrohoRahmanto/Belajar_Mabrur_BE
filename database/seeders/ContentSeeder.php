<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path folder kumpulan doa
        $path = base_path('kumpulan_doa');

        // Ambil semua file .json dalam folder
        $files = File::files($path);

        foreach ($files as $file) {
            $json = File::get($file->getPathname());
            $data = json_decode($json, true);

            if (!$data) {
                continue; // skip kalau file tidak valid
            }

            foreach ($data as $item) {
                DB::table('contents')->insert([
                    'name'         => $item['name'],
                    'arabic'       => $item['arabic'],
                    'latin'        => $item['latin'],
                    'translate_id' => $item['translate_id'],
                    'category'     => $item['category'],
                    'description'  => $item['description'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
