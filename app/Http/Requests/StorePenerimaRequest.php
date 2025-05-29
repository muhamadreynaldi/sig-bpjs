<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Izinkan semua user yang terautentikasi
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => 'required|string|digits:16|unique:penerimas,nik',
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'dusun' => 'required|string|max:100',
            'status' => 'required|string|in:Aktif,Nonaktif,Meninggal',
            'lat' => 'required|numeric|between:-90,90', // Validasi latitude
            'lng' => 'required|numeric|between:-180,180', // Validasi longitude
        ];
    }

    public function messages(): array
    {
        return [
            'lat.between' => 'Latitude harus antara -90 dan 90.',
            'lng.between' => 'Longitude harus antara -180 dan 180.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nik.digits' => 'NIK harus 16 digit.',
        ];
    }
}