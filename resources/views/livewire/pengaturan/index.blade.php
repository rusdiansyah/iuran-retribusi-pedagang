<?php

use App\Models\Setting;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    public $nama_aplikasi, $email, $logo;

    public function mount()
    {
        $setting = Setting::first();
        if($setting) {
            $this->nama_aplikasi = $setting->nama_aplikasi;
            $this->email = $setting->email;
            $this->logo = $setting->logo;
        }
    }

    public function store()
    {
        $this->validate([
            'nama_aplikasi' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $setting = Setting::first() ?? new Setting();
        $setting->nama_aplikasi = $this->nama_aplikasi;
        $setting->email = $this->email;
        $setting->save();

        session()->flash('message', 'Pengaturan berhasil disimpan.');
    }
};
?>
<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pengaturan Sistem</h2>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-lg">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-2xl">
        <div class="p-6">
            <form wire:submit="store">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Aplikasi</label>
                    <input type="text" wire:model="nama_aplikasi" class="w-full rounded border-gray-300">
                    @error('nama_aplikasi') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email Aplikasi</label>
                    <input type="email" wire:model="email" class="w-full rounded border-gray-300">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>
