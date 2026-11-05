<?php

return [
    'required' => ':attribute wajib diisi.',
    'required_if' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'email' => ':attribute harus berupa email yang valid.',
    'unique' => ':attribute sudah dipakai.',
    'in' => ':attribute tidak valid.',
    'regex' => 'Format :attribute tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'numeric' => ':attribute harus berupa angka.',
    'array' => ':attribute tidak valid.',
    'between' => [
        'numeric' => ':attribute harus di antara :min dan :max.',
        'string' => ':attribute harus di antara :min dan :max karakter.',
    ],
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'max' => [
        'numeric' => ':attribute maksimal :max.',
        'string' => ':attribute maksimal :max karakter.',
        'array' => ':attribute maksimal :max item.',
    ],
    'password' => [
        'letters' => ':attribute harus mengandung huruf.',
        'numbers' => ':attribute harus mengandung angka.',
        'mixed' => ':attribute harus mengandung huruf besar dan kecil.',
        'symbols' => ':attribute harus mengandung simbol.',
        'uncompromised' => ':attribute pernah bocor di internet, pilih yang lain.',
    ],
    'attributes' => [],
];
