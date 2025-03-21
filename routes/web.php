<?php

declare(strict_types=1);

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;
use XbNz\LaravelAuditableUsers\Livewire\EmailVerified;
use XbNz\LaravelAuditableUsers\Livewire\ForgotPassword;
use XbNz\LaravelAuditableUsers\Livewire\Login;
use XbNz\LaravelAuditableUsers\Livewire\Register;
use XbNz\LaravelAuditableUsers\Livewire\ResetPassword;

Route::middleware(RedirectIfAuthenticated::class)->group(function (): void {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('forgotPassword');
});

Route::middleware(ValidateSignature::class)->group(function (): void {
    Route::get('/confirm-email/{userUuid}', EmailVerified::class)->name('confirmEmail');
    Route::get('/reset-password/{userUuid}', ResetPassword::class)
        ->middleware(RedirectIfAuthenticated::class)
        ->name('resetPassword');
});
