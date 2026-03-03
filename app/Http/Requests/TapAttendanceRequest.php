<?php
// app/Http/Requests/TapAttendanceRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TapAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rfid_number' => [
                'required',
                'string',
                'exists:employees,rfid_number',
            ],
            'tapped_at' => [
                'nullable',
                'date',
                'before_or_equal:now',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rfid_number.required' => 'Nomor RFID harus diisi',
            'rfid_number.exists' => 'RFID tidak terdaftar dalam sistem',
            'tapped_at.date' => 'Format tanggal tidak valid',
            'tapped_at.before_or_equal' => 'Tanggal tap tidak boleh di masa depan',
        ];
    }
}
