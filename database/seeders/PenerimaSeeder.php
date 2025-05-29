<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Penerima;
use Illuminate\Support\Facades\File; // Untuk membaca file

class PenerimaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Penerima::truncate(); // Kosongkan tabel sebelum seeding (opsional)

        $csvFile = File::get(database_path('seeders/data/sample_desa.csv'));
        $data = array_map('str_getcsv', explode("\n", $csvFile));

        // Ambil header
        $header = array_shift($data);

        foreach ($data as $row) {
            // Hindari baris kosong di akhir file CSV jika ada
            if (count($row) === count($header) && !empty(trim(implode('', $row)))) {
                $penerimaData = array_combine($header, $row);

                Penerima::create([
                    'nik' => $penerimaData['NIK'],
                    'nama' => $penerimaData['NAMA'],
                    'alamat' => $penerimaData['ALAMAT'],
                    'dusun' => $penerimaData['DUSUN'],
                    'status' => $penerimaData['STATUS'],
                    'lat' => (float)$penerimaData['LATITUDE'], // Pastikan dikonversi ke float
                    'lng' => (float)$penerimaData['LONGITUDE'],// Pastikan dikonversi ke float
                ]);
            }
        }
    }
}