<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerima extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nik',
        'nama',
        'alamat',
        'dusun',
        'status',
        'lat',
        'lng',
        'rt',
        'rw',
        'jenis_kepesertaan',
        'bantuan_lainnya'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penerimas';
        protected $casts = [
        'bantuan_lainnya' => 'array', // Ini akan mengubah JSON menjadi array secara otomatis
    ];
}
