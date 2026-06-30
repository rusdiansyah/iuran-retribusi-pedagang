<?php

use App\Models\Tagihan;
use App\Models\TagihanDetail;
use App\Models\Pedagang;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $isOpen = false;
    public $tanggal, $bulan, $tahun, $jumlah, $keterangan;
    
    public function render(): mixed
    {
        $data = Tagihan::latest()->paginate(10);
        return view('livewire.tagihan.index', compact('data'));
    }

    public function create()
    {
        $this->reset('tanggal', 'bulan', 'tahun', 'jumlah', 'keterangan');
        $this->tanggal = date('Y-m-d');
        $this->bulan = date('m');
        $this->tahun = date('Y');
        $this->isOpen = true;
    }

    public function store()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'bulan' => 'required|string',
            'tahun' => 'required|string',
            'jumlah' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string'
        ]);

        $activePedagang = Pedagang::where('is_aktif', true)->get();

        if ($activePedagang->isEmpty()) {
            session()->flash('error', 'Tidak ada pedagang aktif untuk ditagih.');
            return;
        }

        DB::beginTransaction();
        try {
            $tagihan = Tagihan::create([
                'nomor_transaksi' => 'TGH-' . strtoupper(Str::random(6)),
                'tanggal' => $this->tanggal,
                'bulan' => $this->bulan,
                'tahun' => $this->tahun,
                'jumlah' => $this->jumlah,
                'keterangan' => $this->keterangan,
                'created_by' => auth()->id(),
            ]);

            foreach ($activePedagang as $p) {
                TagihanDetail::create([
                    'tagihan_id' => $tagihan->id,
                    'pedagang_id' => $p->id,
                ]);
                $p->increment('piutang', $this->jumlah);
            }
            DB::commit();
            session()->flash('message', 'Tagihan berhasil dibuat untuk ' . $activePedagang->count() . ' pedagang aktif.');
            $this->isOpen = false;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        // Menghapus tagihan berarti membatalkan piutang
        $tagihan = Tagihan::findOrFail($id);
        
        DB::beginTransaction();
        try {
            foreach ($tagihan->details as $detail) {
                if (!$detail->is_paid) {
                    $detail->pedagang->decrement('piutang', $tagihan->jumlah);
                }
            }
            $tagihan->delete();
            DB::commit();
            session()->flash('message', 'Tagihan berhasil dihapus dan piutang disesuaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
};
?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Tagihan</h2>
        <button wire:click="create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Buat Tagihan
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-lg">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">No Transaksi</th>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Periode</th>
                        <th class="px-6 py-3">Jumlah per Pedagang</th>
                        <th class="px-6 py-3">Total Tagihan</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $item->nomor_transaksi }}</td>
                        <td class="px-6 py-4">{{ $item->tanggal }}</td>
                        <td class="px-6 py-4">{{ $item->bulan }} - {{ $item->tahun }}</td>
                        <td class="px-6 py-4">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 font-bold">Rp {{ number_format($item->jumlah * $item->details()->count(), 0, ',', '.') }} ({{ $item->details()->count() }} Pedagang)</td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin hapus tagihan ini? Piutang pedagang terkait akan dikurangi." class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data tagihan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $data->links() }}</div>
    </div>

    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold mb-4">Buat Tagihan Iuran</h3>
            <form wire:submit="store">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal</label>
                    <input type="date" wire:model="tanggal" class="w-full rounded border-gray-300">
                    @error('tanggal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Bulan</label>
                        <select wire:model="bulan" class="w-full rounded border-gray-300">
                            @foreach(range(1,12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tahun</label>
                        <select wire:model="tahun" class="w-full rounded border-gray-300">
                            @for($y=date('Y')-2; $y<=date('Y')+2; $y++)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah Iuran per Pedagang</label>
                    <input type="number" wire:model="jumlah" class="w-full rounded border-gray-300">
                    @error('jumlah') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Keterangan</label>
                    <textarea wire:model="keterangan" class="w-full rounded border-gray-300"></textarea>
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('isOpen', false)" class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan & Tagihkan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
