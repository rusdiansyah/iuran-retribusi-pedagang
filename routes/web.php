<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');


Route::get('dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('lokasi', 'master-data.lokasi')->name('lokasi.index');
    Volt::route('jenis', 'master-data.jenis')->name('jenis.index');
    Volt::route('zonasi', 'master-data.zonasi')->name('zonasi.index');
    Volt::route('metode', 'master-data.metode')->name('metode.index');

    Volt::route('pedagang', 'pedagang.index')->name('pedagang.index');
    Volt::route('tagihan', 'tagihan.index')->name('tagihan.index');
    Volt::route('pembayaran', 'pembayaran.index')->name('pembayaran.index');
    Volt::route('piutang', 'piutang.index')->name('piutang.index');
    Volt::route('laporan', 'laporan.index')->name('laporan.index');
    Volt::route('pengguna', 'pengguna.index')->name('pengguna.index');
    Volt::route('pengaturan', 'pengaturan.index')->name('pengaturan.index');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
