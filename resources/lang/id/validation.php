<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'alpha_num'            => ':Attribute harus berupa huruf dan angka.',
    'confirmed'            => ':Attribute tidak sesuai.',
    'email'                => ':Attribute harus menggunakan email address yang valid.',
    'max'                  => [
        'file'    => ':Attribute tidak boleh melebihi :max kilobytes.',
        'string'  => ':Attribute tidak boleh melebihi :min karakter.',
    ],
    'mimes'                => ':Attribute harus berupa file :values.',
    'min'                  => [
        'file'    => ':Attribute minimal :min kilobytes.',
        'string'  => ':Attribute minimal terdiri dari :min karakter.',
    ],
    'numeric'              => ':Attribute harus berupa angka.',
    'regex'                => 'Format :attribute tidak valid.',
    'required'             => ':Attribute wajib diisi.',
    'string'               => ':Attribute harus berupa huruf.',
    'unique'               => ':Attribute sudah digunakan.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'password' => [
            'regex' => 'Password harus mengandung satu angka dan satu huruf kapital.'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'email'         => 'email',
        'image'         => 'gambar',
        'first_name'    => 'nama depan',
        'last_name'     => 'nama terakhir',
        'password'      => 'password',
        'phone_number'  => 'no telepon',
        'pin_pattern'   => 'pola pin',
    ],

];