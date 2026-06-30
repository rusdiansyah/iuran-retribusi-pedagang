<?php

use App\Models\Pedagang;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    public $jenis_laporan = 'pedagang'; // pedagang, tagihan, pembayaran, piutang
    public $tanggal_mulai, $tanggal_akhir;

    public function mount()
    {
        $this->tanggal_mulai = date('Y-m-d', strtotime('-30 days'));
        $this->tanggal_akhir = date('Y-m-d');
    }

    public function print()
    {
        $this->dispatch('print-window');
    }

    public function render(): mixed
    {
        $data = [];
        
        if ($this->jenis_laporan == 'pedagang') {
            $data = Pedagang::with(['lokasi', 'jenis', 'zonasi'])->get();
        } elseif ($this->jenis_laporan == 'tagihan') {
            $data = Tagihan::whereBetween('tanggal', [$this->tanggal_mulai, $this->tanggal_akhir])
                           ->orderBy('tanggal', 'desc')->get();
        } elseif ($this->jenis_laporan == 'pembayaran') {
            $data = Pembayaran::with(['pedagang', 'metode', 'creator'])
                              ->whereBetween('tanggal', [$this->tanggal_mulai, $this->tanggal_akhir])
                              ->orderBy('tanggal', 'desc')->get();
        } elseif ($this->jenis_laporan == 'piutang') {
            $data = Pedagang::where('piutang', '>', 0)->orderByDesc('piutang')->get();
        }

        return view('livewire.laporan.index', compact('data'));
    }
};
?>
<div>
    <div class="flex justify-between items-center mb-6 no-print">
        <h2 class="text-2xl font-bold text-gray-800">Laporan</h2>
        <div>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-print mr-2"></i> Print / PDF
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 no-print">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Laporan</label>
                <select wire:model.live="jenis_laporan" class="w-full rounded border-gray-300">
                    <option value="pedagang">Laporan Pedagang</option>
                    <option value="tagihan">Laporan Tagihan</option>
                    <option value="pembayaran">Laporan Pembayaran</option>
                    <option value="piutang">Laporan Piutang Pedagang</option>
                </select>
            </div>
            
            @if(in_array($jenis_laporan, ['tagihan', 'pembayaran']))
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Mulai</label>
                <input type="date" wire:model.live="tanggal_mulai" class="w-full rounded border-gray-300">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Akhir</label>
                <input type="date" wire:model.live="tanggal_akhir" class="w-full rounded border-gray-300">
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 print-container" id="printableArea">
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold uppercase">LAPORAN {{ strtoupper($jenis_laporan) }}</h3>
            @if(in_array($jenis_laporan, ['tagihan', 'pembayaran']))
            <p class="text-sm text-gray-600">Periode: {{ date('d/m/Y', strtotime($tanggal_mulai)) }} s/d {{ date('d/m/Y', strtotime($tanggal_akhir)) }}</p>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse border border-gray-200">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    
                    @if($jenis_laporan == 'pedagang')
                    <tr>
                        <th class="px-4 py-2 border">Kode</th>
                        <th class="px-4 py-2 border">Nama</th>
                        <th class="px-4 py-2 border">Lokasi</th>
                        <th class="px-4 py-2 border">Jenis</th>
                        <th class="px-4 py-2 border">Zonasi</th>
                        <th class="px-4 py-2 border">Status</th>
                    </tr>
                    @elseif($jenis_laporan == 'tagihan')
                    <tr>
                        <th class="px-4 py-2 border">No Transaksi</th>
                        <th class="px-4 py-2 border">Tanggal</th>
                        <th class="px-4 py-2 border">Periode</th>
                        <th class="px-4 py-2 border text-right">Jumlah</th>
                    </tr>
                    @elseif($jenis_laporan == 'pembayaran')
                    <tr>
                        <th class="px-4 py-2 border">No Transaksi</th>
                        <th class="px-4 py-2 border">Tanggal</th>
                        <th class="px-4 py-2 border">Pedagang</th>
                        <th class="px-4 py-2 border">Metode</th>
                        <th class="px-4 py-2 border text-right">Jumlah</th>
                    </tr>
                    @elseif($jenis_laporan == 'piutang')
                    <tr>
                        <th class="px-4 py-2 border">Kode</th>
                        <th class="px-4 py-2 border">Nama Pedagang</th>
                        <th class="px-4 py-2 border text-right">Sisa Piutang</th>
                    </tr>
                    @endif

                </thead>
                <tbody>
                    
                    @if($jenis_laporan == 'pedagang')
                        @foreach($data as $d)
                        <tr>
                            <td class="px-4 py-2 border">{{ $d->kode_pedagang }}</td>
                            <td class="px-4 py-2 border">{{ $d->nama_pedagang }}</td>
                            <td class="px-4 py-2 border">{{ $d->lokasi->nama ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $d->jenis->nama ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $d->zonasi->nama ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $d->is_aktif ? 'Aktif' : 'Tidak Aktif' }}</td>
                        </tr>
                        @endforeach
                    @elseif($jenis_laporan == 'tagihan')
                        @php $total = 0; @endphp
                        @foreach($data as $d)
                        @php 
                            $sub = $d->jumlah * $d->details()->count(); 
                            $total += $sub;
                        @endphp
                        <tr>
                            <td class="px-4 py-2 border">{{ $d->nomor_transaksi }}</td>
                            <td class="px-4 py-2 border">{{ $d->tanggal }}</td>
                            <td class="px-4 py-2 border">{{ $d->bulan }} - {{ $d->tahun }}</td>
                            <td class="px-4 py-2 border text-right">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="px-4 py-2 border text-right font-bold">TOTAL TAGIHAN</td>
                            <td class="px-4 py-2 border text-right font-bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    @elseif($jenis_laporan == 'pembayaran')
                        @php $total = 0; @endphp
                        @foreach($data as $d)
                        @php $total += $d->total; @endphp
                        <tr>
                            <td class="px-4 py-2 border">{{ $d->nomor_transaksi }}</td>
                            <td class="px-4 py-2 border">{{ $d->tanggal }}</td>
                            <td class="px-4 py-2 border">{{ $d->pedagang->nama_pedagang ?? '-' }}</td>
                            <td class="px-4 py-2 border">{{ $d->metode->nama ?? '-' }}</td>
                            <td class="px-4 py-2 border text-right">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" class="px-4 py-2 border text-right font-bold">TOTAL PEMBAYARAN</td>
                            <td class="px-4 py-2 border text-right font-bold text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    @elseif($jenis_laporan == 'piutang')
                        @php $total = 0; @endphp
                        @foreach($data as $d)
                        @php $total += $d->piutang; @endphp
                        <tr>
                            <td class="px-4 py-2 border">{{ $d->kode_pedagang }}</td>
                            <td class="px-4 py-2 border">{{ $d->nama_pedagang }}</td>
                            <td class="px-4 py-2 border text-right">Rp {{ number_format($d->piutang, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="px-4 py-2 border text-right font-bold">TOTAL PIUTANG</td>
                            <td class="px-4 py-2 border text-right font-bold text-red-600">Rp {{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                </tbody>
            </table>
            @if(count($data) == 0)
                <div class="p-4 text-center text-gray-500">Tidak ada data.</div>
            @endif
        </div>
    </div>
    
    <style>
        @media print {
            body * { visibility: hidden; }
            #printableArea, #printableArea * { visibility: visible; }
            #printableArea { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
            @page { margin: 10mm; }
        }
    </style>
</div>
