<?php

use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\ConversionController;
use App\Http\Controllers\Api\StkPushController;
use App\Http\Controllers\Webhooks\B2cCallbackController;
use App\Http\Controllers\Webhooks\C2bConfirmationController;
use App\Http\Controllers\Webhooks\C2bValidationController;
use App\Http\Controllers\Webhooks\SmsIntakeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- Public app-facing API (consumed by the Flutter app) ---
Route::get('/config', ConfigController::class)->name('api.config');
Route::post('/conversions', [ConversionController::class, 'store'])->name('api.conversions.store');
Route::post('/stk-push', [StkPushController::class, 'store'])->name('api.stk-push.store');

// --- Daraja webhooks (Safaricom calls these, not the app) ---
Route::prefix('webhooks/mpesa')->name('webhooks.mpesa.')->group(function () {
    Route::post('c2b/validation', C2bValidationController::class)->name('c2b.validation');
    Route::post('c2b/confirmation', C2bConfirmationController::class)->name('c2b.confirmation');
    Route::post('b2c/result', [B2cCallbackController::class, 'result'])->name('b2c.result');
    Route::post('b2c/timeout', [B2cCallbackController::class, 'timeout'])->name('b2c.timeout');
    Route::post('stk-push/callback', [StkPushController::class, 'callback'])->name('stk-push.callback');
});

// --- SMS-forwarder app on the receiving line calls this to report an
// incoming airtime transfer (see SmsIntakeService for the parsing/matching
// logic and the shared-secret header this requires). ---
Route::post('webhooks/sms-intake', SmsIntakeController::class)->name('webhooks.sms-intake');
