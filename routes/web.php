<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\PenerimaController;
use App\Http\Controllers\PemetaanController; 
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout'); // Kita tambahkan route logout di sini juga

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

Route::middleware(['auth'])->group(function () {
// ... (dashboard, pemetaan, rute) ...
Route::get('/dashboard', [DashboardController::class, 'index'])
     ->name('dashboard')
     ->middleware('auth');

Route::get('/pemetaan', [PemetaanController::class, 'index'])->name('pemetaan.index');


Route::get('/rute', [RouteController::class, 'indexPage'])->name('rute.index');

Route::post('/route/calculate', [RouteController::class, 'calculateRoute'])
     ->name('route.calculate')
     ->middleware('auth');

Route::resource('penerima', PenerimaController::class)->middleware('role:admin');
Route::get('/api/penerima-suggestions', [App\Http\Controllers\PenerimaController::class, 'ajaxSearchSuggestions'])->name('api.penerima.suggestions');
Route::get('/api/penerima-detail/{penerima}', [App\Http\Controllers\PenerimaController::class, 'apiShowDetail'])->name('api.penerima.detail');
Route::get('/api/penerima-select2-suggestions', [App\Http\Controllers\PenerimaController::class, 'ajaxSelect2Suggestions'])->name('api.penerima.select2suggestions');

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
    