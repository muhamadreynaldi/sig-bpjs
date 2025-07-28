<?php

namespace App\Imports;

use App\Models\Penerima;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PenerimasImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Ubah string bantuan (e.g., "BLTDD, BPJS Ketenagakerjaan") menjadi array
        $bantuan = null;
        if (!empty($row['bantuan_lainnya'])) {
            $bantuan = array_map('trim', explode(',', $row['bantuan_lainnya']));
        }

        // updateOrCreate will look for a record based on NIK, then update or create
        return Penerima::updateOrCreate(
            [
                'nik' => $row['nik'] // Unique key to find the record
            ],
            [
                'nama' => $row['nama'],
                'alamat' => $row['alamat'],
                'dusun' => $row['dusun'],
                'rt' => $row['rt'],
                'rw' => $row['rw'],
                'status' => $row['status'],
                'jenis_kepesertaan' => $row['jenis_kepesertaan'],
                'bantuan_lainnya' => $bantuan,
                // INI BAGIAN PENTING YANG DIPERBAIKI:
                // Kita pastikan lat & lng selalu ada, beri nilai null jika kosong di Excel.
                'lat' => $row['latitude'] ?? null,
                'lng' => $row['longitude'] ?? null,
            ]
        );
    }

    // Menambahkan validasi dasar untuk kolom yang wajib diisi dari Excel
    public function rules(): array
    {
        return [
            'nik' => 'required',
            'nama' => 'required',
        ];
    }
}