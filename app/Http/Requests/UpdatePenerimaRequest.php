<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePenerimaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $penerimaId = $this->route('penerima')->id;

        return [
            'nik' => [
                'required',
                'string',
                'digits:16',
                Rule::unique('penerimas', 'nik')->ignore($penerimaId),
            ],
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'dusun' => 'required|string|max:100',
            'status' => 'required|string|in:Aktif,Nonaktif,Meninggal',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
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