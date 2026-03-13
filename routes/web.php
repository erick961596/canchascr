<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{LoginController, RegisterController, GoogleController};
use App\Http\Controllers\Player\{HomeController, ReservationController as PlayerReservationController, RatingController};
use App\Http\Controllers\Owner\{DashboardController as OwnerDashboard, VenueController, CourtController, ReservationController as OwnerReservationController, SubscriptionController as OwnerSubscriptionController};
use App\Http\Controllers\Admin\{DashboardController as AdminDashboard, UserController, SubscriptionController as AdminSubscriptionController, PlanController, VenueController as AdminVenueController, LogController};
use App\Http\Controllers\Webhooks\OnvoWebhookController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',         [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login',        [LoginController::class, 'login']);
    Route::get('/registro',      [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/registro',     [RegisterController::class, 'register']);
    Route::get('/registro-sede', [RegisterController::class, 'showOwnerRegister'])->name('register.owner');
    Route::post('/registro-sede',[RegisterController::class, 'registerOwner']);
    Route::get('/auth/google',   [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| PLAYER (authenticated + role:user)
|--------------------------------------------------------------------------
*/
Route::prefix('app')->name('player.')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/',          [HomeController::class, 'index'])->name('home');
    Route::get('/explorar',  [HomeController::class, 'explore'])->name('explore');
    Route::get('/sede/{slug}', [HomeController::class, 'venueDetail'])->name('venue');
    Route::get('/sede/{slug}/cancha/{courtId}', [HomeController::class, 'courtDetail'])->name('court');
    Route::get('/cancha/{courtId}/slots', [HomeController::class, 'getSlots'])->name('slots');
    Route::post('/sede/{venue}/calificar', [RatingController::class, 'store'])->name('venue.rate');

    Route::prefix('reservas')->name('bookings.')->group(function () {
        Route::get('/',  [PlayerReservationController::class, 'index'])->name('index');
        Route::post('/', [PlayerReservationController::class, 'store'])->name('store');
        Route::post('/{reservation}/comprobante', [PlayerReservationController::class, 'uploadProof'])->name('proof');
        Route::patch('/{reservation}/cancelar',   [PlayerReservationController::class, 'cancel'])->name('cancel');
    });
});

/*
|--------------------------------------------------------------------------
| OWNER
|--------------------------------------------------------------------------
*/
Route::prefix('owner')->name('owner.')->middleware(['auth', 'role:owner'])->group(function () {

    // Subscription (no active subscription required)
    Route::get('/suscripcion', [OwnerSubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/suscripcion', [OwnerSubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/suscripcion/init-card', [OwnerSubscriptionController::class, 'initCard'])->name('subscription.init_card');
    Route::post('/suscripcion/upload', [OwnerSubscriptionController::class, 'uploadProof'])->name('subscription.upload');

    // Requires active subscription
    Route::middleware('subscription')->group(function () {
        Route::get('/dashboard', [OwnerDashboard::class, 'index'])->name('dashboard');

        Route::prefix('sedes')->name('venues.')->group(function () {
            Route::get('/',                [VenueController::class, 'index'])->name('index');
            Route::post('/',               [VenueController::class, 'store'])->name('store');
            Route::put('/{venue}',         [VenueController::class, 'update'])->name('update');
            Route::get('/cantones',        [VenueController::class, 'getCantons'])->name('cantons');
            Route::get('/distritos',       [VenueController::class, 'getDistricts'])->name('districts');
        });

        Route::prefix('canchas')->name('courts.')->group(function () {
            Route::get('/',                              [CourtController::class, 'index'])->name('index');
            Route::post('/',                             [CourtController::class, 'store'])->name('store');
            Route::put('/{court}',                       [CourtController::class, 'update'])->name('update');
            Route::delete('/{court}',                    [CourtController::class, 'destroy'])->name('destroy');
            Route::post('/{court}/horarios',             [CourtController::class, 'saveSchedules'])->name('schedules');
            Route::post('/{court}/bloqueos',             [CourtController::class, 'addBlockout'])->name('blockout.store');
            Route::delete('/bloqueos/{blockout}',        [CourtController::class, 'removeBlockout'])->name('blockout.destroy');
        });

        Route::prefix('reservas')->name('reservations.')->group(function () {
            Route::get('/',                                [OwnerReservationController::class, 'index'])->name('index');
            Route::patch('/{reservation}/confirmar',      [OwnerReservationController::class, 'confirm'])->name('confirm');
            Route::patch('/{reservation}/rechazar',       [OwnerReservationController::class, 'reject'])->name('reject');
            Route::get('/calendario-data',                [OwnerReservationController::class, 'calendarData'])->name('calendar.data');
        });
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard',          [AdminDashboard::class, 'index'])->name('dashboard');
    Route::get('/usuarios',           [UserController::class, 'index'])->name('users.index');
    Route::put('/usuarios/{user}',    [UserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/suscripciones',      [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/suscripciones/{subscription}', [AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::get('/pagos-pendientes',   [AdminSubscriptionController::class, 'pendingPayments'])->name('subscriptions.pending');
    Route::post('/pagos/{payment}/aprobar',  [AdminSubscriptionController::class, 'approvePayment'])->name('payments.approve');
    Route::post('/pagos/{payment}/rechazar', [AdminSubscriptionController::class, 'rejectPayment'])->name('payments.reject');
    Route::put('/suscripciones/{subscription}', [AdminSubscriptionController::class, 'update'])->name('subscriptions.update');

    // Planes
    Route::get('/planes',                 [PlanController::class, 'index'])->name('plans.index');
    Route::post('/planes',                [PlanController::class, 'store'])->name('plans.store');
    Route::put('/planes/{plan}',          [PlanController::class, 'update'])->name('plans.update');
    Route::delete('/planes/{plan}',       [PlanController::class, 'destroy'])->name('plans.destroy');
    Route::patch('/planes/{plan}/toggle', [PlanController::class, 'toggle'])->name('plans.toggle');

    // Sedes (admin overview)
    Route::get('/venues',                    [AdminVenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/{venue}',            [AdminVenueController::class, 'show'])->name('venues.show');
    Route::patch('/venues/{venue}/toggle',   [AdminVenueController::class, 'toggle'])->name('venues.toggle');

    // Logs
    Route::get('/logs',              [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}',        [LogController::class, 'show'])->name('logs.show');
    Route::delete('/logs/clear',     [LogController::class, 'clear'])->name('logs.clear');
});

/*
|--------------------------------------------------------------------------
| WEBHOOKS ONVOPAY
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/onvo', [OnvoWebhookController::class, 'handle'])
    ->name('webhooks.onvo')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/', fn() => redirect()->route('player.home'))->middleware('auth');
Route::get('/', fn() => view('pages.auth.login'))->middleware('guest');
