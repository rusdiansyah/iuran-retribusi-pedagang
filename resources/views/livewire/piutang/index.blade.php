<?php

use App\Models\Pedagang;
use App\Models\Lokasi;
use App\Models\Jenis;
use App\Models\Zonasi;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $search = '';
    public $filterLokasi = '';
    public $filterJenis = '';
    public $filterZonasi = '';
    public $filterStatus = '';

    public function render(): mixed
    {
        $query = Pedagang::query();
        
        if (auth()->user()->role === 'Pedagang') {
            $query->where('nama_pedagang', auth()->user()->name)
                  ->orWhere('nik', auth()->user()->username);
        } else {
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('nama_pedagang', 'like', '%' . $this->search . '%')
                      ->orWhere('kode_pedagang', 'like', '%' . $this->search . '%');
                });
            }
            if ($this->filterLokasi) $query->where('lokasi_id', $this->filterLokasi);
            if ($this->filterJenis) $query->where('jenis_id', $this->filterJenis);
            if ($this->filterZonasi) $query->where('zonasi_id', $this->filterZonasi);
            if ($this->filterStatus !== '') $query->where('is_aktif', $this->filterStatus);
        }

        $totalPiutang = (clone $query)->sum('piutang');
        $data = $query->orderByDesc('piutang')->paginate(10);
        
        return view('livewire.piutang.index', [
            'data' => $data,
            'lokasis' => Lokasi::all(),
            'jenises' => Jenis::all(),
            'zonasis' => Zonasi::all(),
            'totalPiutang' => $totalPiutang,
        ]);
    }
};
?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Data Piutang Pedagang</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if(auth()->user()->role !== 'Pedagang')
        <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row gap-2">
            <input type="text" wire:model.live="search" placeholder="Cari..." class="w-full md:flex-1 rounded-lg border-gray-300">
            <select wire:model.live="filterLokasi" class="w-full md:flex-1 rounded-lg border-gray-300">
                <option value="">Semua Lokasi</option>
                @foreach($lokasis as $lokasi)
                    <option value="{{ $lokasi->id }}">{{ $lokasi->nama }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterJenis" class="w-full md:flex-1 rounded-lg border-gray-300">
                <option value="">Semua Jenis</option>
                @foreach($jenises as $jenis)
                    <option value="{{ $jenis->id }}">{{ $jenis->nama }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterZonasi" class="w-full md:flex-1 rounded-lg border-gray-300">
                <option value="">Semua Zonasi</option>
                @foreach($zonasis as $zonasi)
                    <option value="{{ $zonasi->id }}">{{ $zonasi->nama }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="w-full md:flex-1 rounded-lg border-gray-300">
                <option value="">Semua Status</option>
                <option value="1">Aktif</option>
                <option value="0">Tidak Aktif</option>
            </select>
        </div>
        @endif
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Kode Pedagang</th>
                        <th class="px-6 py-3">Nama Pedagang</th>
                        <th class="px-6 py-3 text-right">Total Tagihan</th>
                        <th class="px-6 py-3 text-right">Total Pembayaran</th>
                        <th class="px-6 py-3 text-right">Sisa Piutang</th>
                        <th class="px-6 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    @php
                        // Hitung total tagihan dari tabel tagihan details untuk pedagang ini
                        $totalTagihan = \App\Models\TagihanDetail::where('pedagang_id', $item->id)
                            ->join('tagihans', 'tagihan_details.tagihan_id', '=', 'tagihans.id')
                            ->sum('tagihans.jumlah');
                        
                        // Hitung total pembayaran dari tabel pembayarans untuk pedagang ini
                        $totalPembayaran = \App\Models\Pembayaran::where('pedagang_id', $item->id)->sum('total');
                    @endphp
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold text-gray-900">{{ $item->kode_pedagang }}</td>
                        <td class="px-6 py-4">{{ $item->nama_pedagang }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-green-600">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold {{ $item->piutang > 0 ? 'text-red-600' : 'text-gray-900' }}">Rp {{ number_format($item->piutang, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($item->is_aktif)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold border-t border-gray-200 text-gray-900">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right">TOTAL KESELURUHAN SISA PIUTANG :</td>
                        <td class="px-6 py-4 text-right text-red-600">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="p-4">{{ $data->links() }}</div>
    </div>
</div>
