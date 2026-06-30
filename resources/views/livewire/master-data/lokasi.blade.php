<?php

use App\Models\Lokasi;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $nama, $editId;
    public $isOpen = false;
    public $search = '';

    public function render(): mixed
    {
        $data = Lokasi::where('nama', 'like', '%' . $this->search . '%')->paginate(10);
        return view('livewire.master-data.lokasi', compact('data'));
    }

    public function create()
    {
        $this->reset('nama', 'editId');
        $this->isOpen = true;
    }

    public function store()
    {
        $this->validate(['nama' => 'required|string|max:255']);
        
        // Case-insensitive unique check
        $exists = \App\Models\Lokasi::whereRaw('LOWER(nama) = ?', [strtolower(trim($this->nama))])
            ->when($this->editId, fn($q) => $q->where('id', '!=', $this->editId))
            ->exists();
            
        if ($exists) {
            $this->addError('nama', 'Nama tersebut sudah ada (abaikan huruf besar/kecil).');
            return;
        }
        
        $this->nama = ucwords(strtolower(trim($this->nama))); // Auto-format to Title Case
        Lokasi::updateOrCreate(['id' => $this->editId], ['nama' => $this->nama]);
        session()->flash('message', $this->editId ? 'Data berhasil diupdate.' : 'Data berhasil ditambahkan.');
        $this->isOpen = false;
        $this->reset('nama', 'editId');
    }

    public function edit($id)
    {
        $item = Lokasi::findOrFail($id);
        $this->editId = $id;
        $this->nama = $item->nama;
        $this->isOpen = true;
    }

    public function delete($id)
    {
        Lokasi::findOrFail($id)->delete();
        session()->flash('message', 'Data berhasil dihapus.');
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Lokasi</h2>
        <button wire:click="create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Tambah
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <input type="text" wire:model.live="search" placeholder="Cari..." class="w-full md:w-1/3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Nama Lokasi</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $item->id }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item->nama }}</td>
                    <td class="px-6 py-4 text-right">
                        <button wire:click="edit({{ $item->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin hapus data ini?" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            {{ $data->links() }}
        </div>
    </div>

    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold mb-4">{{ $editId ? 'Edit' : 'Tambah' }} Lokasi</h3>
            <form wire:submit="store">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lokasi</label>
                    <input type="text" wire:model="nama" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('nama') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('isOpen', false)" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
