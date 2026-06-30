<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();
    $this->redirect('/', navigate: true);
};
?>

<aside class="flex flex-col w-64 h-screen px-4 py-8 overflow-y-auto bg-indigo-700 border-r">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2" wire:navigate>
        <i class="fas fa-store text-white text-2xl"></i>
        <span class="text-xl font-bold text-white">Retribusi App</span>
    </a>

    <div class="flex flex-col justify-between flex-1 mt-6">
        <nav class="space-y-2 text-white">
            <a class="flex items-center px-4 py-2 mt-5 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('dashboard') }}" wire:navigate>
                <i class="fas fa-home w-5"></i>
                <span class="mx-2 font-medium">Dashboard</span>
            </a>

            @if(in_array(auth()->user()->role, ['Admin', 'Staff Penagihan']))
                <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('pedagang.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('pedagang.index') }}" wire:navigate>
                    <i class="fas fa-users w-5"></i>
                    <span class="mx-2 font-medium">Pedagang</span>
                </a>
            @endif

            @if(in_array(auth()->user()->role, ['Admin']))
                <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('tagihan.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('tagihan.index') }}" wire:navigate>
                    <i class="fas fa-file-invoice w-5"></i>
                    <span class="mx-2 font-medium">Tagihan</span>
                </a>
            @endif

            <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('pembayaran.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('pembayaran.index') }}" wire:navigate>
                <i class="fas fa-money-bill-wave w-5"></i>
                <span class="mx-2 font-medium">Pembayaran</span>
            </a>

            <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('piutang.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('piutang.index') }}" wire:navigate>
                <i class="fas fa-hand-holding-usd w-5"></i>
                <span class="mx-2 font-medium">Piutang</span>
            </a>

            @if(in_array(auth()->user()->role, ['Admin']))
                <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('laporan.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('laporan.index') }}" wire:navigate>
                    <i class="fas fa-chart-bar w-5"></i>
                    <span class="mx-2 font-medium">Laporan</span>
                </a>

                <div class="mt-8">
                    <span class="px-4 text-xs font-semibold tracking-wider text-indigo-200 uppercase">Master Data</span>
                    <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('lokasi.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('lokasi.index') }}" wire:navigate>
                        <i class="fas fa-map-marker-alt w-5"></i>
                        <span class="mx-2 font-medium">Lokasi</span>
                    </a>
                    <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('zonasi.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('zonasi.index') }}" wire:navigate>
                        <i class="fas fa-layer-group w-5"></i>
                        <span class="mx-2 font-medium">Zonasi</span>
                    </a>
                    <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('jenis.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('jenis.index') }}" wire:navigate>
                        <i class="fas fa-tags w-5"></i>
                        <span class="mx-2 font-medium">Jenis Pedagang</span>
                    </a>
                    <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('metode.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('metode.index') }}" wire:navigate>
                        <i class="fas fa-credit-card w-5"></i>
                        <span class="mx-2 font-medium">Metode Pembayaran</span>
                    </a>
                    <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('pengguna.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('pengguna.index') }}" wire:navigate>
                        <i class="fas fa-user-shield w-5"></i>
                        <span class="mx-2 font-medium">Pengguna</span>
                    </a>
                    <a class="flex items-center px-4 py-2 mt-2 transition-colors rounded-lg hover:text-white hover:bg-indigo-600 {{ request()->routeIs('pengaturan.*') ? 'bg-indigo-600 text-white' : 'text-gray-300' }}" href="{{ route('pengaturan.index') }}" wire:navigate>
                        <i class="fas fa-cogs w-5"></i>
                        <span class="mx-2 font-medium">Pengaturan</span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="mt-8">
            <button wire:click="logout" class="flex items-center w-full px-4 py-2 mt-5 text-gray-300 transition-colors rounded-lg hover:text-white hover:bg-indigo-600">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="mx-2 font-medium">Logout</span>
            </button>
        </div>
    </div>
</aside>
