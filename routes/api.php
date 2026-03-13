<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\OnvoWebhookController;


/*
|--------------------------------------------------------------------------
| WEBHOOKS ONVOPAY
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/onvo', [OnvoWebhookController::class, 'handle'])
    ->name('webhooks.onvo')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


    