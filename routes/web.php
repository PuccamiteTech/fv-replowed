<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AssetsController;
use App\Http\Controllers\NeighborController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\DailyGiftController;

Route::get('/up', function () {
    $health = [
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'checks' => [],
    ];

    $dbStart = microtime(true);
    try {
        DB::select('SELECT 1');
        $health['checks']['database'] = [
            'status' => 'ok',
            'ping_ms' => round((microtime(true) - $dbStart) * 1000, 2),
        ];
    } catch (\Exception $e) {
        $health['status'] = 'degraded';
        $health['checks']['database'] = ['status' => 'error', 'message' => 'Connection failed'];
    }

    $cacheStart = microtime(true);
    try {
        Cache::put('health_check', true, 10);
        $cacheWorks = Cache::get('health_check') === true;
        $health['checks']['cache'] = [
            'status' => $cacheWorks ? 'ok' : 'error',
            'ping_ms' => round((microtime(true) - $cacheStart) * 1000, 2),
        ];
    } catch (\Exception $e) {
        $health['checks']['cache'] = ['status' => 'error'];
    }

    try {
        if (Storage::exists('last_backup.json')) {
            $backup = json_decode(Storage::get('last_backup.json'), true);
            $health['checks']['last_backup'] = [
                'status' => 'ok',
                'timestamp' => $backup['timestamp'] ?? null,
                'age_hours' => isset($backup['timestamp']) ? round((time() - $backup['timestamp']) / 3600, 1) : null,
            ];
        } else {
            $health['checks']['last_backup'] = ['status' => 'none'];
        }
    } catch (\Exception $e) {
        $health['checks']['last_backup'] = ['status' => 'error'];
    }

    return response()->json($health);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/game', [GameController::class, 'index'])->middleware(['auth', 'verified'])->name('game');

Route::post('/download-file', [AssetsController::class, 'downloadAssets'])->name('download.file');
Route::get('/download-progress', [AssetsController::class, 'getProgress'])->name('download.progress');
Route::post('/extract-file', [AssetsController::class, 'extractAssets'])->name('extract.file');
Route::get('/extract-progress', [AssetsController::class, 'extractProgress'])->name('extract.progress');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/neighbors/data', [NeighborController::class, 'getNeighborsData'])->name('neighbors.data');
    Route::get('/neighbors/potential', [NeighborController::class, 'getPotentialNeighbors'])->name('neighbors.potential');
    Route::get('/neighbors/pending', [NeighborController::class, 'getPendingRequests'])->name('neighbors.pending');
    Route::post('/neighbors/add', [NeighborController::class, 'addNeighbor'])->name('neighbors.add');
    Route::post('/neighbors/remove', [NeighborController::class, 'removeNeighbor'])->name('neighbors.remove');
    Route::post('/neighbors/accept', [NeighborController::class, 'acceptNeighbor'])->name('neighbors.accept');
    Route::post('/neighbors/reject', [NeighborController::class, 'rejectNeighbor'])->name('neighbors.reject');
    Route::post('/neighbors/send-request', [NeighborController::class, 'sendNeighborRequest'])->name('neighbors.send-request');

    // Daily Gift routes
    Route::get('/daily-gift/status', [DailyGiftController::class, 'checkStatus'])->name('daily-gift.status');
    Route::post('/daily-gift/claim', [DailyGiftController::class, 'claim'])->name('daily-gift.claim');
});

require __DIR__.'/auth.php';
