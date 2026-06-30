<?php

use App\Models\Pedagang;
use App\Models\Lokasi;
use App\Models\Jenis;
use App\Models\Zonasi;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $editId;
    public $nik, $nama_pedagang, $lokasi_id, $jenis_id, $zonasi_id, $no_hp, $alamat, $tanggal_masuk, $tanggal_keluar, $deskripsi, $is_aktif = true;
    
    public $isOpen = false;
    public $search = '';
    public $filterLokasi = '', $filterJenis = '', $filterZonasi = '', $filterStatus = '';

    public function render(): mixed
    {
        $query = Pedagang::with(['lokasi', 'jenis', 'zonasi']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nama_pedagang', 'like', '%' . $this->search . '%')
                  ->orWhere('kode_pedagang', 'like', '%' . $this->search . '%')
                  ->orWhere('nik', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->filterLokasi) $query->where('lokasi_id', $this->filterLokasi);
        if ($this->filterJenis) $query->where('jenis_id', $this->filterJenis);
        if ($this->filterZonasi) $query->where('zonasi_id', $this->filterZonasi);
        if ($this->filterStatus !== '') $query->where('is_aktif', $this->filterStatus);

        $data = $query->latest()->paginate(10);

        return view('livewire.pedagang.index', [
            'data' => $data,
            'lokasis' => Lokasi::all(),
            'jenises' => Jenis::all(),
            'zonasis' => Zonasi::all(),
        ]);
    }

    public function create()
    {
        $this->reset('editId', 'nik', 'nama_pedagang', 'lokasi_id', 'jenis_id', 'zonasi_id', 'no_hp', 'alamat', 'tanggal_masuk', 'tanggal_keluar', 'deskripsi');
        $this->is_aktif = true;
        $this->isOpen = true;
    }

    public function store()
    {
        $this->validate([
            'nama_pedagang' => 'required|string|max:255',
            'lokasi_id' => 'required|exists:lokasis,id',
            'jenis_id' => 'required|exists:jenis,id',
            'zonasi_id' => 'required|exists:zonasis,id',
            'no_hp' => 'nullable|string',
            'alamat' => 'nullable|string',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_keluar' => 'nullable|date',
        ]);

        $data = [
            'nik' => $this->nik,
            'nama_pedagang' => $this->nama_pedagang,
            'lokasi_id' => $this->lokasi_id,
            'jenis_id' => $this->jenis_id,
            'zonasi_id' => $this->zonasi_id,
            'no_hp' => $this->no_hp,
            'alamat' => $this->alamat,
            'tanggal_masuk' => $this->tanggal_masuk,
            'tanggal_keluar' => $this->tanggal_keluar,
            'deskripsi' => $this->deskripsi,
            'is_aktif' => $this->is_aktif,
        ];

        if (!$this->editId) {
            $data['kode_pedagang'] = 'PDG-' . strtoupper(Str::random(6));
            $data['created_by'] = auth()->id();
        }

        $pedagang = Pedagang::updateOrCreate(['id' => $this->editId], $data);
        
        // Buat akun user otomatis jika ini adalah data pedagang baru
        if (!$this->editId) {
            \App\Models\User::create([
                'name' => $pedagang->nama_pedagang,
                'email' => strtolower($pedagang->kode_pedagang) . '@retribusi.local',
                'username' => $pedagang->kode_pedagang,
                'password' => \Illuminate\Support\Facades\Hash::make('pedagang123'),
                'role' => 'Pedagang',
            ]);
        }

        session()->flash('message', $this->editId ? 'Data diupdate.' : 'Data ditambahkan. Akun berhasil dibuat (Username: ' . $pedagang->kode_pedagang . ', Password: pedagang123)');
        $this->isOpen = false;
        $this->reset();
    }

    public function edit($id)
    {
        $item = Pedagang::findOrFail($id);
        $this->editId = $id;
        $this->nik = $item->nik;
        $this->nama_pedagang = $item->nama_pedagang;
        $this->lokasi_id = $item->lokasi_id;
        $this->jenis_id = $item->jenis_id;
        $this->zonasi_id = $item->zonasi_id;
        $this->no_hp = $item->no_hp;
        $this->alamat = $item->alamat;
        $this->tanggal_masuk = $item->tanggal_masuk;
        $this->tanggal_keluar = $item->tanggal_keluar;
        $this->deskripsi = $item->deskripsi;
        $this->is_aktif = $item->is_aktif;
        $this->isOpen = true;
    }

    public function delete($id)
    {
        Pedagang::findOrFail($id)->delete();
        session()->flash('message', 'Data dihapus.');
    }
};
?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Data Pedagang</h2>
        <button wire:click="create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Tambah
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-lg">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" wire:model.live="search" placeholder="Cari..." class="rounded-lg border-gray-300">
            <select wire:model.live="filterLokasi" class="rounded-lg border-gray-300">
                <option value="">Semua Lokasi</option>
                @foreach($lokasis as $l) <option value="{{ $l->id }}">{{ $l->nama }}</option> @endforeach
            </select>
            <select wire:model.live="filterJenis" class="rounded-lg border-gray-300">
                <option value="">Semua Jenis</option>
                @foreach($jenises as $j) <option value="{{ $j->id }}">{{ $j->nama }}</option> @endforeach
            </select>
            <select wire:model.live="filterZonasi" class="rounded-lg border-gray-300">
                <option value="">Semua Zonasi</option>
                @foreach($zonasis as $z) <option value="{{ $z->id }}">{{ $z->nama }}</option> @endforeach
            </select>
            <select wire:model.live="filterStatus" class="rounded-lg border-gray-300">
                <option value="">Semua Status</option>
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
            </select>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Kode / NIK</th>
                        <th class="px-4 py-3">Nama / HP</th>
                        <th class="px-4 py-3">Info</th>
                        <th class="px-4 py-3 text-right">Piutang</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-bold text-gray-900">{{ $item->kode_pedagang }}</span><br>
                            <span class="text-xs">{{ $item->nik ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-gray-900">{{ $item->nama_pedagang }}</span><br>
                            <span class="text-xs">{{ $item->no_hp ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <i class="fas fa-map-marker-alt w-4"></i> {{ $item->lokasi->nama ?? '-' }}<br>
                            <i class="fas fa-tags w-4"></i> {{ $item->jenis->nama ?? '-' }}<br>
                            <i class="fas fa-layer-group w-4"></i> {{ $item->zonasi->nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold {{ $item->piutang > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($item->piutang, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($item->is_aktif)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="edit({{ $item->id }})" class="text-blue-600 hover:text-blue-900 mr-2"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin?" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $data->links() }}</div>
    </div>

    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-6 mt-10 mb-10">
            <h3 class="text-lg font-bold mb-4">{{ $editId ? 'Edit' : 'Tambah' }} Pedagang</h3>
            <form wire:submit="store" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Pedagang *</label>
                    <input type="text" wire:model="nama_pedagang" class="w-full rounded border-gray-300">
                    @error('nama_pedagang') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">NIK</label>
                    <input type="text" wire:model="nik" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">No HP</label>
                    <input type="text" wire:model="no_hp" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Lokasi *</label>
                    <select wire:model="lokasi_id" class="w-full rounded border-gray-300">
                        <option value="">Pilih</option>
                        @foreach($lokasis as $l) <option value="{{ $l->id }}">{{ $l->nama }}</option> @endforeach
                    </select>
                    @error('lokasi_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenis *</label>
                    <select wire:model="jenis_id" class="w-full rounded border-gray-300">
                        <option value="">Pilih</option>
                        @foreach($jenises as $j) <option value="{{ $j->id }}">{{ $j->nama }}</option> @endforeach
                    </select>
                    @error('jenis_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Zonasi *</label>
                    <select wire:model="zonasi_id" class="w-full rounded border-gray-300">
                        <option value="">Pilih</option>
                        @foreach($zonasis as $z) <option value="{{ $z->id }}">{{ $z->nama }}</option> @endforeach
                    </select>
                    @error('zonasi_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Alamat</label>
                    <textarea wire:model="alamat" class="w-full rounded border-gray-300"></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Masuk</label>
                    <input type="date" wire:model="tanggal_masuk" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Status Aktif</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="is_aktif" class="rounded border-gray-300 text-indigo-600">
                            <span class="ml-2">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="col-span-2 flex justify-end gap-2 mt-4">
                    <button type="button" wire:click="$set('isOpen', false)" class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
