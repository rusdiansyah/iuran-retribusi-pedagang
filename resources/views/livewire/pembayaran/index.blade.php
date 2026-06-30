<?php

use App\Models\Pembayaran;
use App\Models\PembayaranDetail;
use App\Models\Pedagang;
use App\Models\Metode;
use App\Models\TagihanDetail;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $isOpen = false;
    public $search = '';
    public $tanggal, $pedagang_id, $metode_id, $keterangan;
    public $selectedTagihans = [];
    public $total = 0;
    
    // For search pedagang in modal
    public $searchPedagang = '';

    public function render(): mixed
    {
        $query = Pembayaran::with(['pedagang', 'metode', 'creator']);
        if (auth()->user()->role === 'Pedagang') {
            $pedagang = Pedagang::where('nama_pedagang', auth()->user()->name)->orWhere('nik', auth()->user()->username)->first();
            if ($pedagang) {
                $query->where('pedagang_id', $pedagang->id);
            } else {
                $query->where('pedagang_id', 0); // show none
            }
        } else {
            if ($this->search) {
                $query->whereHas('pedagang', function($q) {
                    $q->where('nama_pedagang', 'like', '%' . $this->search . '%')
                      ->orWhere('kode_pedagang', 'like', '%' . $this->search . '%');
                });
            }
        }

        $data = $query->latest()->paginate(10);
        
        $pedagangs = [];
        if ($this->isOpen) {
            if (auth()->user()->role === 'Pedagang') {
                $p = Pedagang::where('nama_pedagang', auth()->user()->name)->orWhere('nik', auth()->user()->username)->first();
                if($p) $pedagangs = [$p];
            } else {
                $pedagangs = Pedagang::where('nama_pedagang', 'like', '%' . $this->searchPedagang . '%')
                                     ->orWhere('kode_pedagang', 'like', '%' . $this->searchPedagang . '%')
                                     ->take(10)->get();
            }
        }

        return view('livewire.pembayaran.index', compact('data', 'pedagangs'), [
            'metodes' => Metode::all()
        ]);
    }

    public function create()
    {
        $this->reset('tanggal', 'pedagang_id', 'metode_id', 'keterangan', 'selectedTagihans', 'total', 'searchPedagang');
        $this->tanggal = date('Y-m-d');
        
        if (auth()->user()->role === 'Pedagang') {
            $p = Pedagang::where('nama_pedagang', auth()->user()->name)->orWhere('nik', auth()->user()->username)->first();
            if($p) {
                $this->pedagang_id = $p->id;
                $this->updatedPedagangId();
            }
        }
        
        $this->isOpen = true;
    }

    public $unpaidTagihans = [];

    public function updatedPedagangId()
    {
        $this->selectedTagihans = [];
        $this->total = 0;
        if ($this->pedagang_id) {
            $this->unpaidTagihans = TagihanDetail::with('tagihan')->where('pedagang_id', $this->pedagang_id)->where('is_paid', false)->get();
        } else {
            $this->unpaidTagihans = [];
        }
    }

    public function updatedSelectedTagihans()
    {
        $this->total = 0;
        foreach ($this->selectedTagihans as $tagihanDetailId => $value) {
            if ($value) {
                $td = TagihanDetail::with('tagihan')->find($tagihanDetailId);
                if ($td) $this->total += $td->tagihan->jumlah;
            }
        }
    }

    public function store()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'pedagang_id' => 'required|exists:pedagangs,id',
            'metode_id' => 'required|exists:metodes,id',
            'total' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string'
        ]);

        $pedagang = Pedagang::findOrFail($this->pedagang_id);

        DB::beginTransaction();
        try {
            $pembayaran = Pembayaran::create([
                'nomor_transaksi' => 'PAY-' . strtoupper(Str::random(6)),
                'tanggal' => $this->tanggal,
                'pedagang_id' => $this->pedagang_id,
                'total' => $this->total,
                'metode_id' => $this->metode_id,
                'keterangan' => $this->keterangan,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->selectedTagihans as $tagihanDetailId => $value) {
                if ($value) {
                    $td = TagihanDetail::with('tagihan')->find($tagihanDetailId);
                    if ($td) {
                        PembayaranDetail::create([
                            'pembayaran_id' => $pembayaran->id,
                            'tagihan_detail_id' => $td->id,
                            'jumlah_bayar' => $td->tagihan->jumlah,
                        ]);
                        $td->update(['is_paid' => true]);
                    }
                }
            }

            // Kurangi Piutang
            $pedagang->decrement('piutang', $this->total);

            DB::commit();
            session()->flash('message', 'Pembayaran berhasil disimpan.');
            $this->isOpen = false;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $pembayaran = Pembayaran::with('details')->findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Kembalikan status tagihan dan piutang
            $pedagang = Pedagang::find($pembayaran->pedagang_id);
            if ($pedagang) {
                $pedagang->increment('piutang', $pembayaran->total);
            }
            
            foreach ($pembayaran->details as $detail) {
                $td = TagihanDetail::find($detail->tagihan_detail_id);
                if ($td) $td->update(['is_paid' => false]);
            }
            
            $pembayaran->delete();
            DB::commit();
            session()->flash('message', 'Pembayaran berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
};
?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Riwayat Pembayaran</h2>
        <button wire:click="create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Input Pembayaran
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-lg">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if (auth()->user()->role !== 'Pedagang')
        <div class="p-4 border-b border-gray-100">
            <input type="text" wire:model.live="search" placeholder="Cari Kode / Nama Pedagang..." class="w-full md:w-1/3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        @endif
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No Transaksi</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Pedagang</th>
                        <th class="px-6 py-3">Metode</th>
                        <th class="px-6 py-3">Jumlah</th>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $item->nomor_transaksi }}</td>
                        <td class="px-6 py-4">{{ $item->tanggal }}</td>
                        <td class="px-6 py-4">
                            {{ $item->pedagang->nama_pedagang ?? '-' }}<br>
                            <span class="text-xs">{{ $item->pedagang->kode_pedagang ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $item->metode->nama ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 font-bold text-green-600">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ $item->creator->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin membatalkan pembayaran ini?" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-times-circle"></i> Batal
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada data pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $data->links() }}</div>
    </div>

    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50 py-10">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-6 my-auto">
            <h3 class="text-lg font-bold mb-4">Input Pembayaran</h3>
            <form wire:submit="store">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal</label>
                        <input type="date" wire:model="tanggal" class="w-full rounded border-gray-300">
                        @error('tanggal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Metode Pembayaran</label>
                        <select wire:model="metode_id" class="w-full rounded border-gray-300">
                            <option value="">Pilih Metode</option>
                            @foreach($metodes as $m) <option value="{{ $m->id }}">{{ $m->nama }}</option> @endforeach
                        </select>
                        @error('metode_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Pedagang</label>
                    @if (auth()->user()->role !== 'Pedagang')
                    <input type="text" wire:model.live="searchPedagang" placeholder="Cari Pedagang..." class="w-full mb-2 rounded border-gray-300 text-sm">
                    @endif
                    <div class="max-h-40 overflow-y-auto border rounded-lg border-gray-200">
                        @foreach($pedagangs as $p)
                        <div class="p-2 border-b hover:bg-gray-50 flex items-center">
                            <input type="radio" wire:model.live="pedagang_id" value="{{ $p->id }}" class="mr-2 rounded-full border-gray-300 text-indigo-600">
                            <span>{{ $p->kode_pedagang }} - {{ $p->nama_pedagang }} (Piutang: Rp {{ number_format($p->piutang, 0, ',', '.') }})</span>
                        </div>
                        @endforeach
                        @if(empty($pedagangs))
                        <div class="p-4 text-center text-gray-500 text-sm">Pedagang tidak ditemukan</div>
                        @endif
                    </div>
                    @error('pedagang_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                @if($pedagang_id && count($unpaidTagihans) > 0)
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Tagihan yang Dibayar</label>
                    <div class="border rounded-lg border-gray-200 overflow-hidden">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 w-10">#</th>
                                    <th class="px-4 py-2">Bulan/Tahun</th>
                                    <th class="px-4 py-2 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unpaidTagihans as $ut)
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        <input type="checkbox" wire:model.live="selectedTagihans.{{ $ut->id }}" class="rounded border-gray-300 text-indigo-600">
                                    </td>
                                    <td class="px-4 py-2">{{ $ut->tagihan->bulan }} - {{ $ut->tagihan->tahun }} ({{ $ut->tagihan->nomor_transaksi }})</td>
                                    <td class="px-4 py-2 text-right">Rp {{ number_format($ut->tagihan->jumlah, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @elseif($pedagang_id)
                <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg text-sm font-semibold">
                    Pedagang ini tidak memiliki tagihan yang belum dibayar.
                </div>
                @endif

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Total Bayar</label>
                    <input type="number" wire:model="total" class="w-full rounded border-gray-300 bg-gray-100 font-bold text-lg" readonly>
                    @error('total') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan</label>
                    <textarea wire:model="keterangan" class="w-full rounded border-gray-300"></textarea>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('isOpen', false)" class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded" @if($total <= 0) disabled @endif>Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
