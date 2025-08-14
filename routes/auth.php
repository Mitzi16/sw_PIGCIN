<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegisteredReviewUserController;
use App\Http\Controllers\Auth\RegisteredEditorUserController;
use App\Http\Controllers\Auth\RegisteredConferencistaUserController;
use App\Http\Controllers\Auth\RegisteredTalleristaUserController;
use App\Http\Controllers\Auth\RegisteredAsistenteUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    // Rutas para el registro de postulantes
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])->name('register.postulantes');

    // Rutas para el registro de revisores
    Route::get('registerReviewCitca2025', [RegisteredReviewUserController::class, 'createRevisores'])
                ->name('registerReview');

    Route::post('registerReviewCitca2025', [RegisteredReviewUserController::class, 'storeRevisores'])->name('registerReview.storeRevisores');

    // Rutas para el registro de editores
    Route::get('registerEditorCitca2025', [RegisteredEditorUserController::class, 'createEditores'])
                ->name('registerEditor');

    Route::post('registerEditorCitca2025', [RegisteredEditorUserController::class, 'storeEditores'])->name('registerEditor.storeEditores');

    // Rutas para el registro de conferencistas
    Route::get('registerConferencistas', [RegisteredConferencistaUserController::class, 'createConferencistas'])
                ->name('registerConferencistas');

    Route::post('registerConferencistas', [RegisteredConferencistaUserController::class, 'storeConferencista'])->name('registerConferencista.storeConferencista');


    // Rutas para el registro de Talleristas
    Route::get('registerTalleristas', [RegisteredTalleristaUserController::class, 'createTalleristas'])
                ->name('registerTalleristas');

    Route::post('registerTalleristas', [RegisteredTalleristaUserController::class, 'storeTallerista'])->name('registerTallerista.storeTallerista');

     // Rutas para el registro de Asistentes
    Route::get('registerAsistentes', [RegisteredAsistenteUserController::class, 'createAsistentes'])
                ->name('registerAsistentes');

    Route::post('registerAsistentes', [RegisteredAsistenteUserController::class, 'storeAsistente'])->name('registerAsistente.storeAsistente');


    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login-store', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
