<?php

namespace App\Exports;

use App\Models\Penerima;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PenerimasExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Penerima::all();
    }

    public function headings(): array
    {
        // Judul kolom di file Excel tetap user-friendly
        return [
            'Nama',
            'NIK',
            'Alamat',
            'Dusun',
            'RT',
            'RW',
            'Jenis Kepesertaan',
            'Status',
            'Bantuan Lainnya',
            'Latitude',
            'Longitude',
        ];
    }

    /**
    * @var Penerima $penerima
    */
    public function map($penerima): array
    {
        // Ambil data dari kolom 'lat' dan 'lng'
        return [
            $penerima->nama,
            $penerima->nik,
            $penerima->alamat,
            $penerima->dusun,
            $penerima->rt,
            $penerima->rw,
            $penerima->jenis_kepesertaan,
            $penerima->status,
            $penerima->bantuan_lainnya ? implode(', ', $penerima->bantuan_lainnya) : '',
            $penerima->lat,  // <<< INI BAGIAN UTAMA YANG DIPERBAIKI
            $penerima->lng,  // <<< INI BAGIAN UTAMA YANG DIPERBAIKI
        ];
    }
}