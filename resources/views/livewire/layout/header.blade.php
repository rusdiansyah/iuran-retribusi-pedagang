<?php
use Livewire\Volt\Component;

new class extends Component {
    //
};
?>
<header class="flex items-center justify-between px-6 py-4 bg-white border-b-4 border-indigo-600">
    <div class="flex items-center">
        <!-- Title or hamburger menu can go here -->
    </div>

    <div class="flex items-center">
        <div x-data="{ dropdownOpen: false }" class="relative">
            <button @click="dropdownOpen = ! dropdownOpen" class="flex items-center gap-2 focus:outline-none">
                <div class="flex flex-col text-right hidden sm:flex">
                    <span class="text-sm font-semibold text-gray-700">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-gray-500">{{ auth()->user()->role }}</span>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            </button>

            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" class="absolute right-0 z-10 w-48 mt-2 overflow-hidden bg-white rounded-md shadow-xl" style="display: none;">
                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-600 hover:text-white" wire:navigate>Profile</a>
                <!-- Logout is handled in sidebar, but could add here too -->
            </div>
        </div>
    </div>
</header>
