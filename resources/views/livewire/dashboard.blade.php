<div>
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h2>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Total Pedagang</p>
                <h4 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalPedagang) }}</h4>
                <div class="text-xs text-gray-400 mt-1">
                    <span class="text-green-500 font-medium">{{ $pedagangAktif }} Aktif</span> &bull;
                    <span class="text-red-500 font-medium">{{ $pedagangNonAktif }} Non Aktif</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xl">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Total Tagihan</p>
                <h4 class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h4>
            </div>
            <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Total Pembayaran</p>
                <h4 class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalPembayaran, 0, ',', '.') }}</h4>
            </div>
            <div class="w-12 h-12 rounded-full bg-green-50 text-green-500 flex items-center justify-center text-xl">
                <i class="fas fa-money-bill-wave"></i>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Total Piutang</p>
                <h4 class="text-2xl font-bold text-gray-900 mt-1 text-red-600">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h4>
            </div>
            <div class="w-12 h-12 rounded-full bg-red-50 text-red-500 flex items-center justify-center text-xl">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Lokasi Pedagang</p>
                <h4 class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totalLokasi) }} Lokasi</h4>
            </div>
            <div class="text-gray-400"><i class="fas fa-map-marker-alt text-2xl"></i></div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">Jenis Pedagang</p>
                <h4 class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totalJenis) }} Jenis</h4>
            </div>
            <div class="text-gray-400"><i class="fas fa-tags text-2xl"></i></div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Grafik Transaksi Bulanan</h3>
            <div class="relative h-72">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Metode Pembayaran</h3>
            <div class="relative h-72">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">10 Transaksi Terakhir</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">No Transaksi</th>
                        <th class="px-6 py-3">Pedagang</th>
                        <th class="px-6 py-3">Metode</th>
                        <th class="px-6 py-3">Jumlah</th>
                        <th class="px-6 py-3">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiTerakhir as $t)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $t->nomor_transaksi }}</td>
                        <td class="px-6 py-4">{{ $t->pedagang->nama_pedagang ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $t->metode->nama ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ $t->creator->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada transaksi pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" data-navigate-once="true"></script>

    <script data-navigate-eval="true">
        document.addEventListener('livewire:navigated', () => {
            const barChartElement = document.getElementById('barChart');
            const pieChartElement = document.getElementById('pieChart');

            if (!barChartElement || !pieChartElement) return;

            const initCharts = () => {
                if (window.myBarChart) window.myBarChart.destroy();
                if (window.myPieChart) window.myPieChart.destroy();

                const barCtx = barChartElement.getContext('2d');
                const pieCtx = pieChartElement.getContext('2d');

                const tagihanData = @json($tagihanPerBulan);
                const pembayaranData = @json($pembayaranPerBulan);
                const chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

                window.myBarChart = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: 'Tagihan',
                                data: tagihanData,
                                backgroundColor: 'rgba(99, 102, 241, 0.8)', // Indigo
                                borderRadius: 4
                            },
                            {
                                label: 'Pembayaran',
                                data: pembayaranData,
                                backgroundColor: 'rgba(34, 197, 94, 0.8)', // Green
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });

                const metodeData = @json($metodePembayaran);
                window.myPieChart = new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(metodeData),
                        datasets: [{
                            data: Object.values(metodeData),
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(139, 92, 246, 0.8)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        cutout: '70%'
                    }
                });
            };

            // Ensure Chart.js is loaded before initializing
            if (typeof Chart !== 'undefined') {
                initCharts();
            } else {
                let attempts = 0;
                const checkInterval = setInterval(() => {
                    attempts++;
                    if (typeof Chart !== 'undefined') {
                        clearInterval(checkInterval);
                        initCharts();
                    }
                    if (attempts > 50) clearInterval(checkInterval); // Stop after 5s
                }, 100);
            }
        }, { once: true });
    </script>
</div>
