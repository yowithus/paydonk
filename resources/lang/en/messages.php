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
	'success' 	=> ':Action is successful.',
    'error' 	=> ':Action is failed.',

    'error_incorrect_credentials' 	=> 'Incorrect phone number or password.',
    'error_incorrect_password' 		=> 'The password you have entered is incorrect.',
    'error_unregistered_phone_number' => 'The phone number is not registered.',
    'error_incorrect_verification_code' => 'The verification code you have entered is incorrect.',

    'error_invalid_order' => 'Order is not found or has been confirmed.',
    'error_invalid_product' => 'Product is not found.',
    'error_invalid_operator' => 'The operator is currently not supported.',

    'error_not_enough_balance' => 'You don\' have enough balance.',
    'error_invalid_topup_payment_method' => 'Top up balance can only be done with bank transfer.',
    'error_invalid_bill_payment_method' => 'Bill payment can not be done with bank transfer.',
    'error_payment_failed' => 'Payment failed, please try again later.',

    'error_invalid_promo_code' => 'Promo code is not found.',
    'error_promo_minimum_usage' => 'Minimum usage is Rp :min_usage',
];