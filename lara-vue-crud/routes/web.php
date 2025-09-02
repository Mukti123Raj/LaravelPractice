<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductListingController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AdminOrSellerMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    Route::middleware([AdminOrSellerMiddleware::class])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Product Listings - Admin only
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/product-listings', [ProductListingController::class, 'index'])->name('product-listings.index');
        Route::get('/product-listings/create', [ProductListingController::class, 'create'])->name('product-listings.create');
        Route::post('/product-listings', [ProductListingController::class, 'store'])->name('product-listings.store');
        Route::get('/product-listings/{productListing}/edit', [ProductListingController::class, 'edit'])->name('product-listings.edit');
        Route::put('/product-listings/{productListing}', [ProductListingController::class, 'update'])->name('product-listings.update');
        Route::delete('/product-listings/{productListing}', [ProductListingController::class, 'destroy'])->name('product-listings.destroy');
    });

    // Customers - Admin only
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Sales Orders - Admin only
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/sales-orders', [SalesOrderController::class, 'index'])->name('sales-orders.index');
        Route::get('/sales-orders/create', [SalesOrderController::class, 'create'])->name('sales-orders.create');
        Route::post('/sales-orders', [SalesOrderController::class, 'store'])->name('sales-orders.store');
        Route::get('/sales-orders/{salesOrder}/edit', [SalesOrderController::class, 'edit'])->name('sales-orders.edit');
        Route::put('/sales-orders/{salesOrder}', [SalesOrderController::class, 'update'])->name('sales-orders.update');
        Route::delete('/sales-orders/{salesOrder}', [SalesOrderController::class, 'destroy'])->name('sales-orders.destroy');
    });

    // Email - Admin only
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::post('/emails/send', [\App\Http\Controllers\EmailController::class, 'send'])->name('emails.send');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
