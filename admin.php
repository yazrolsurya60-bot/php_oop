<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';
require_once 'classes/User.php';

// 1. Cek Sesi Login & Role Admin
if (!isset($_SESSION['id_users'])) {
    header("Location: login.php");
    exit();
}

$id_users_login = $_SESSION['id_users'];

// Ambil data user beserta id_admin
$query = "SELECT u.*, a.id_admin 
          FROM users u  
          JOIN admin a ON u.id_users = a.id_users 
          WHERE u.id_users = ?";
$stmt = $conn->getConn()->prepare($query);
$stmt->bind_param("s", $id_users_login);
$stmt->execute();
$data_login = $stmt->get_result()->fetch_assoc();

// Jika bukan admin, tendang keluar
if (!$data_login) {
    header("Location: index.php");
    exit();
}

// 2. Inisialisasi Objek Admin menggunakan data asli dari database
$admin = new Admin(
    $data_login['id_users'],
    $data_login['username'],
    $data_login['password'],
    $data_login['nama'],
    $data_login['alamat'],
    $data_login['jenis_kelamin'],
    $data_login['foto_profil'],
    $data_login['id_admin']
);

$total_pendapatan = 0;
$grafik_data = [];

try {
    $total_pendapatan = $admin->tampilkanPendapatan(date('n'));
    $grafik_data = $admin->tampilkanGrafikPendapatan();
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}

// Convert grafik data to JSON for Chart.js
$labels = [];
$data_points = [];
foreach ($grafik_data as $row) {
    $labels[] = date('d M Y', strtotime($row['tanggal']));
    $data_points[] = (float) $row['total'];
}
$grafik_json_labels = json_encode($labels);
$grafik_json_data = json_encode($data_points);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Oak Coffee POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#E63946',
                            darkred: '#D62828',
                            black: '#111111',
                            gray: '#333333',
                            offwhite: '#F8F9FA'
                        }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'], }
                }
            }
        }
    </script>
</head>

<body class="bg-brand-offwhite h-screen flex text-brand-black">

    <!-- Sidebar -->
    <aside class="w-64 bg-brand-black text-white shadow-xl flex-hidden flex-col md:flex">
        <div class="p-6 border-b border-white/10 flex items-center gap-3">
            <div
                class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white font-bold text-xl">
                <img src="images/Oak_Coffe.png" class="object-cover rounded-lg w-full h-full" alt="Logo Kafe">
            </div>
            <span class="text-xl font-bold tracking-tight">Oak Admin</span>
        </div>

        <div class="p-6 flex flex-col gap-2 flex-1">
            <a href="admin.php"
                class="px-4 py-3 bg-brand-red text-white rounded-xl font-medium transition-colors shadow-[0_4px_14px_rgba(230,57,70,0.4)] flex items-center gap-3"><i
                    class="fa-solid fa-chart-pie w-5"></i> Dashboard</a>
            <a href="admin_menu.php"
                class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i
                    class="fa-solid fa-utensils w-5"></i> Manajemen Menu</a>
            <a href="admin_user.php"
                class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i
                    class="fa-solid fa-users w-5"></i> Manajemen User</a>
            <a href="admin_bahan_baku.php"
                class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i
                    class="fa-solid fa-boxes-stacked w-5"></i> Bahan Baku</a>
            <a href="admin_laporan.php"
                class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i
                    class="fa-solid fa-file-invoice-dollar w-5"></i> Laporan Penjualan</a>
            <a href="admin_profil.php"
                class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i
                    class="fa-solid fa-user-tie w-5"></i> Profil Owner</a>

            <a href="logout.php"
                class="mt-auto px-4 py-3 text-white/50 hover:bg-white/5 hover:text-brand-red rounded-xl font-medium transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <!-- Topbar -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center px-8 border-b border-black/5">
            <h2 class="text-2xl font-extrabold text-brand-black">Dashboard Analitik</h2>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-brand-black"><?php echo htmlspecialchars($admin->getNama()); ?></p>
                    <p class="text-xs text-brand-gray">Administrator</p>
                </div>
                <!-- Menggunakan foto dari database -->
                <img src="<?php echo htmlspecialchars($admin->getFotoProfile() ?: 'https://i.pravatar.cc/150?img=11'); ?>"
                    alt="Profile" class="w-10 h-10 rounded-full border-2 border-brand-red/20 object-cover"
                    onerror="this.src='https://i.pravatar.cc/150?img=11'">
            </div>
        </header>

        <!-- Content Area -->
        <div class="p-8 max-w-7xl mx-auto w-full">

            <?php if (isset($error_msg)): ?>
                <div class="bg-red-100 border-l-4 border-brand-red text-brand-darkred p-4 mb-6 rounded-lg">
                    <p class="font-bold">Informasi Database:</p>
                    <p><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>

            <!-- DASHBOARD VIEW -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Revenue Card -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-black/5 relative overflow-hidden group">
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-brand-red/10 rounded-bl-full -z-10 group-hover:scale-110 transition-transform">
                    </div>
                    <p class="text-brand-gray font-bold mb-2">Pendapatan Bulan Ini</p>
                    <h3 class="text-4xl font-black text-brand-black tracking-tight">
                        <span
                            class="text-lg text-brand-red mr-1">Rp</span><?php echo number_format($total_pendapatan, 0, ',', '.'); ?>
                    </h3>
                    <div class="mt-4 flex items-center gap-2 text-sm text-green-600 font-bold">
                        <i class="fa-solid fa-arrow-trend-up"></i> Real-time tracking
                    </div>
                </div>

                <!-- Placeholder Cards -->
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-black/5">
                    <p class="text-brand-gray font-bold mb-2">Total Transaksi</p>
                    <h3 class="text-4xl font-black text-brand-black tracking-tight">-</h3>
                    <p class="text-xs text-brand-gray mt-4">Akan dikembangkan</p>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-sm border border-black/5">
                    <p class="text-brand-gray font-bold mb-2">Menu Terlaris</p>
                    <h3 class="text-4xl font-black text-brand-black tracking-tight">-</h3>
                    <p class="text-xs text-brand-gray mt-4">Akan dikembangkan</p>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-black/5">
                <h3 class="text-xl font-bold mb-6">Grafik Tren Pendapatan</h3>
                <div class="h-80 w-full relative">
                    <?php if (empty($labels)): ?>
                        <div
                            class="absolute inset-0 flex flex-col items-center justify-center text-brand-gray bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                            <i class="fa-solid fa-chart-line text-4xl mb-3 text-gray-300"></i>
                            <p class="font-bold">Belum ada data transaksi untuk ditampilkan.</p>
                        </div>
                    <?php endif; ?>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <script>
                // Render Chart.js
                const ctx = document.getElementById('revenueChart');
                if (ctx) {
                    const labels = <?php echo $grafik_json_labels; ?>;
                    const data = <?php echo $grafik_json_data; ?>;

                    if (labels.length > 0) {
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Pendapatan (Rp)',
                                    data: data,
                                    borderColor: '#E63946',
                                    backgroundColor: 'rgba(230, 57, 70, 0.1)',
                                    borderWidth: 3,
                                    pointBackgroundColor: '#fff',
                                    pointBorderColor: '#E63946',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: '#111111',
                                        padding: 12,
                                        titleFont: { family: 'Inter', size: 14 },
                                        bodyFont: { family: 'Inter', size: 14, weight: 'bold' },
                                        callbacks: {
                                            label: function (context) {
                                                let value = context.raw;
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                                        ticks: {
                                            font: { family: 'Inter' },
                                            callback: function (value) {
                                                if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                                if (value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                                return 'Rp ' + value;
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { family: 'Inter' } }
                                    }
                                }
                            }
                        });
                    }
                }
            </script>

        </div>
    </main>
</body>

</html>