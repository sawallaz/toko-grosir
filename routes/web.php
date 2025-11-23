<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController; 
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UnitController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('products/{product}/edit-json', [ProductController::class, 'editJson'])->name('products.editJson');
    
    // [BARU] Route untuk toggle status
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggleStatus');

    // AJAX Manajemen Cepat
    Route::get('categories-json', [CategoryController::class, 'indexJson'])->name('categories.json');
    Route::post('categories-ajax', [CategoryController::class, 'storeAjax'])->name('categories.storeAjax');
    Route::patch('categories-ajax/{category}', [CategoryController::class, 'updateAjax'])->name('categories.updateAjax');
    Route::delete('categories-ajax/{category}', [CategoryController::class, 'destroyAjax'])->name('categories.destroyAjax');

    Route::get('units-json', [UnitController::class, 'indexJson'])->name('units.json');
    Route::post('units-ajax', [UnitController::class, 'storeAjax'])->name('units.storeAjax');
    Route::patch('units-ajax/{unit}', [UnitController::class, 'updateAjax'])->name('units.updateAjax');
    Route::delete('units-ajax/{unit}', [UnitController::class, 'destroyAjax'])->name('units.destroyAjax');
});


Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // 1. Halaman Utama & Simpan Baru
    Route::get('/stok', [StockController::class, 'index'])->name('stok.index');
    Route::post('/stok', [StockController::class, 'store'])->name('stok.store');

    // 2. API Helper (Pencarian & Tambah Supplier) - Taruh di atas {id} agar aman
    Route::get('/stok/search-product', [StockController::class, 'searchProduct'])->name('stok.searchProduct');
    Route::post('/stok/supplier-ajax', [StockController::class, 'storeSupplierAjax'])->name('stok.storeSupplierAjax');

    // 3. Aksi pada Transaksi Tertentu (Edit & Hapus) - YANG KURANG TADI
    Route::get('/stok/{id}/edit-json', [StockController::class, 'editJson'])->name('stok.editJson'); // Ambil data untuk modal edit
    Route::put('/stok/{id}', [StockController::class, 'update'])->name('stok.update'); // Simpan perubahan edit
    Route::delete('/stok/{id}', [StockController::class, 'destroy'])->name('stok.destroy'); // Hapus transaksi

});