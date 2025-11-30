<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController; 
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Customer\CatalogController;
use App\Http\Controllers\Customer\OrderController;

// Halaman Depan (Bisa diakses siapa saja)
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/product/{id}', [CatalogController::class, 'show'])->name('product.show');

Route::get('/dashboard', function () {
    $user = auth()->user();
    $role = $user->role;
    
    switch($role) {
        case 'kasir':
            return redirect()->route('pos.index');
        case 'admin':
            return redirect()->route('admin.dashboard');
        case 'customer':
            return redirect()->route('home'); // atau home
        default:
            return redirect('/');
    }
    
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';

// ====================================================
// GROUP 1: ADMIN (Hanya Admin yang Boleh Masuk)
// ====================================================
// [AMAN] Tambahkan middleware 'role:admin'
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // 1. MANAJEMEN PRODUK
    Route::resource('products', ProductController::class)->except(['show']);
    Route::get('products/{product}/edit-json', [ProductController::class, 'editJson'])->name('products.editJson');
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggleStatus');

    // API Kategori & Satuan
    Route::get('categories-json', [CategoryController::class, 'indexJson'])->name('categories.json');
    Route::post('categories-ajax', [CategoryController::class, 'storeAjax'])->name('categories.storeAjax');
    Route::patch('categories-ajax/{category}', [CategoryController::class, 'updateAjax'])->name('categories.updateAjax');
    Route::delete('categories-ajax/{category}', [CategoryController::class, 'destroyAjax'])->name('categories.destroyAjax');

    Route::get('units-json', [UnitController::class, 'indexJson'])->name('units.json');
    Route::post('units-ajax', [UnitController::class, 'storeAjax'])->name('units.storeAjax');
    Route::patch('units-ajax/{unit}', [UnitController::class, 'updateAjax'])->name('units.updateAjax');
    Route::delete('units-ajax/{unit}', [UnitController::class, 'destroyAjax'])->name('units.destroyAjax');

    // 2. MANAJEMEN STOK
    Route::get('/stok', [StockController::class, 'index'])->name('stok.index');
    Route::post('/stok', [StockController::class, 'store'])->name('stok.store');
    Route::put('/stok/{id}', [StockController::class, 'update'])->name('stok.update');
    Route::delete('/stok/{id}', [StockController::class, 'destroy'])->name('stok.destroy');
    Route::get('/stok/{id}/edit-json', [StockController::class, 'editJson'])->name('stok.editJson');
    Route::get('/stok/search-product', [StockController::class, 'searchProduct'])->name('stok.searchProduct');
    Route::post('/stok/supplier-ajax', [StockController::class, 'storeSupplierAjax'])->name('stok.storeSupplierAjax');

    // 3. MANAJEMEN KASIR
    Route::resource('cashiers', CashierController::class)->except(['show', 'create', 'edit']);
    Route::get('cashiers/{cashier}/edit-json', [CashierController::class, 'editJson'])->name('cashiers.edit-json');
    Route::post('cashiers/{cashier}/toggle-status', [CashierController::class, 'toggleStatus'])->name('cashiers.toggle-status');
});

// ====================================================
// GROUP 2: KASIR POS (Admin & Kasir Boleh Masuk)
// ====================================================
// [AMAN] Tambahkan middleware 'role:kasir,admin'. Customer DILARANG masuk sini.
Route::middleware(['auth', 'verified', 'role:kasir,admin'])->group(function () {
    
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    
    // API POS
    Route::get('/pos/search-product', [PosController::class, 'searchProduct'])->name('pos.search');
    Route::get('/pos/search-customer', [PosController::class, 'searchCustomer'])->name('pos.customer.search');
    Route::post('/pos/customer-ajax', [PosController::class, 'storeCustomerAjax'])->name('pos.customer.storeAjax');
      Route::get('/pos/customer-list', [PosController::class, 'customerList'])->name('pos.customer.list'); // Get All
    Route::patch('/pos/customer/{id}', [PosController::class, 'updateCustomer'])->name('pos.customer.update'); // Edit
    Route::delete('/pos/customer/{id}', [PosController::class, 'destroyCustomer'])->name('pos.customer.destroy'); // Delete

    Route::post('/pos/transaction', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/print/{invoice}', [PosController::class, 'printInvoice'])->name('pos.print');
    Route::get('/pos/history-json', [PosController::class, 'historyJson'])->name('pos.history.json');
    
    Route::get('/pos/online-order/{id}', [PosController::class, 'onlineOrderDetail'])->name('pos.online.detail');
    Route::post('/pos/online-order/{id}/process', [PosController::class, 'processOnlineOrder'])->name('pos.online.process');
    Route::post('/pos/online-order/{id}/reject', [PosController::class, 'rejectOrder'])->name('pos.online.reject');
});


// Halaman Perlu Login
Route::middleware(['auth', 'verified'])->group(function () {
    // Cart
    Route::get('/cart', [OrderController::class, 'cart'])->name('cart.index');
    Route::post('/cart/add', [OrderController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update/{id}', [OrderController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [OrderController::class, 'removeFromCart'])->name('cart.remove');
    
    // Checkout & Riwayat
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{id}', [OrderController::class, 'show'])->name('orders.show');

     // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});