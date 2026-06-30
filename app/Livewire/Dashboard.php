<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lokasi;
use App\Models\Jenis;
use App\Models\Pedagang;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render(): mixed
    {
        $totalLokasi = Lokasi::count();
        $totalJenis = Jenis::count();

        $totalPedagang = Pedagang::count();
        $pedagangAktif = Pedagang::where('is_aktif', true)->count();
        $pedagangNonAktif = Pedagang::where('is_aktif', false)->count();

        $totalTagihan = \App\Models\TagihanDetail::join('tagihans', 'tagihan_details.tagihan_id', '=', 'tagihans.id')->sum('tagihans.jumlah');
        $totalPembayaran = Pembayaran::sum('total');
        $totalPiutang = Pedagang::sum('piutang');

        $transaksiTerakhir = Pembayaran::with(['pedagang', 'metode', 'creator'])->latest()->take(10)->get();

        // Chart Data
        $tagihanRaw = \App\Models\TagihanDetail::join('tagihans', 'tagihan_details.tagihan_id', '=', 'tagihans.id')
                            ->selectRaw('tagihans.bulan, sum(tagihans.jumlah) as total')
                            ->groupBy('tagihans.bulan')
                            ->pluck('total', 'tagihans.bulan')->toArray();
        
        $pembayaranRaw = Pembayaran::selectRaw('strftime("%m", tanggal) as bulan, sum(total) as total')
                            ->groupBy('bulan')
                            ->pluck('total', 'bulan')->toArray();

        $tagihanPerBulan = [];
        $pembayaranPerBulan = [];
        for ($i = 1; $i <= 12; $i++) {
            $bulanStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            // Cek index dengan leading zero ("06") atau tanpa leading zero ("6")
            $tagihanPerBulan[] = $tagihanRaw[$bulanStr] ?? ($tagihanRaw[(string)$i] ?? 0);
            $pembayaranPerBulan[] = $pembayaranRaw[$bulanStr] ?? ($pembayaranRaw[(string)$i] ?? 0);
        }

        $metodePembayaran = Pembayaran::join('metodes', 'pembayarans.metode_id', '=', 'metodes.id')
                            ->selectRaw('metodes.nama, sum(pembayarans.total) as total')
                            ->groupBy('metodes.nama')
                            ->pluck('total', 'metodes.nama')->toArray();

        return view('livewire.dashboard', compact(
            'totalLokasi', 'totalJenis', 'totalPedagang', 'pedagangAktif', 'pedagangNonAktif',
            'totalTagihan', 'totalPembayaran', 'totalPiutang', 'transaksiTerakhir',
            'tagihanPerBulan', 'pembayaranPerBulan', 'metodePembayaran'
        ));
    }
}

