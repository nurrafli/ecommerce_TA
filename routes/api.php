<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::post('/payment/midtrans-callback', [PaymentController::class, 'midtransCallback']);