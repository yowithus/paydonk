<?php

	/*
    |--------------------------------------------------------------------------
    | Message Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the generic error and succesful messages used by
    | all classes. Feel free to tweak each of these messages here.
    |
    */

return [
    'success' => 'Berhasil :action.',
    'error' => 'Gagal :action.',

    'error_incorrect_credentials' 	=> 'Nomor telepon atau password salah.',
    'error_incorrect_password' 		=> 'Password yang anda masukkan salah.',
    'error_unregistered_phone_number' => 'Nomor telepon tidak terdaftar.',
    'error_incorrect_verification_code' => 'Kode verifikasi yang ada masukan salah.',

    'error_invalid_order' => 'Order tidak ditemukan atau sudah dikonfirmasi.',
    'error_invalid_product' => 'Product tidak ditemukan.',
    'error_invalid_operator' => 'Saat ini operator belum tersedia.',

    'error_not_enough_balance' => 'Saldo anda tidak mencukupi.',
    'error_invalid_topup_payment_method' => 'Pembayaran untuk top up saldo hanya bisa dilakukan dengan bank transfer.',
    'error_invalid_bill_payment_method' => 'Pembayaran tagihan tidak bisa dilakukan dengan bank transfer.',
    'error_payment_failed' => 'Pembayaran gagal, mohon dicoba kembali.',

    'error_invalid_promo_code' => 'Kode promo tidak ditemukan.',
    'error_promo_minimum_usage' => 'Minimum pembelian adalah Rp :min_usage',
];