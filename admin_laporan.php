<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';

if (!isset($_SESSION['id_users'])) {
    header("Location: login.php");
    exit();
}
$id_users_login = $_SESSION['id_users'];
$query = "SELECT u.*, a.id_admin FROM users u JOIN admin a ON u.id_users = a.id_users WHERE u.id_users = ?";
$stmt = $conn->getConn()->prepare($query);
$stmt->bind_param("s", $id_users_login);
$stmt->execute();
$data_login = $stmt->get_result()->fetch_assoc();
if (!$data_login) {
    header("Location: index.php");
    exit();
}

$admin = new Admin($data_login['id_users'], $data_login['username'], $data_login['password'], $data_login['nama'], $data_login['alamat'], $data_login['jenis_kelamin'], $data_login['foto_profil'], $data_login['id_admin']);

// Filter tanggal
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Ambil data laporan dari database
$laporan = [];
$total_pendapatan_filter = 0;
try {
    $conn_raw = $conn->getConn();
    $sql = "SELECT ps.id_pesanan, ps.waktu_pesanan, u.nama AS nama_kasir, 
                   COUNT(dp.id_produk) AS total_item, p.total_pembayaran
            FROM pesanan ps
            JOIN users u ON ps.id_users = u.id_users
            JOIN pembayaran p ON ps.id_pesanan = p.id_pesanan
            LEFT JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
            WHERE DATE(ps.waktu_pesanan) BETWEEN ? AND ?";
    $params = [$tgl_mulai, $tgl_akhir];
    $types = "ss";
    if (!empty($search)) {
        $sql .= " AND (ps.id_pesanan LIKE ? OR u.nama LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }
    $sql .= " GROUP BY ps.id_pesanan ORDER BY ps.waktu_pesanan DESC";
    $stmt_lap = $conn_raw->prepare($sql);
    $stmt_lap->bind_param($types, ...$params);
    $stmt_lap->execute();
    $laporan = $stmt_lap->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($laporan as $row) {
        $total_pendapatan_filter += $row['total_pembayaran'];
    }
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}

// Ekspor CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="laporan_penjualan_' . $tgl_mulai . '_sd_' . $tgl_akhir . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID Transaksi', 'Waktu', 'Kasir', 'Total Item', 'Total Harga (Rp)']);
    foreach ($laporan as $row) {
        fputcsv($out, [
            $row['id_pesanan'],
            $row['waktu_pesanan'],
            $row['nama_kasir'],
            $row['total_item'],
            $row['total_pembayaran']
        ]);
    }
    fclose($out);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Oak Coffee POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jsPDF untuk ekspor PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { red: '#E63946', darkred: '#D62828', black: '#111111', gray: '#333333', offwhite: '#F8F9FA' } }, fontFamily: { sans: ['Inter', 'sans-serif'] } } }
        }
    </script>
</head>

<body class="bg-brand-offwhite h-screen flex text-brand-black">

    <!-- Sidebar -->
    <aside class="w-64 bg-brand-black text-white shadow-xl flex flex-col">
        <div class="p-6 border-b border-white/10 flex items-center gap-3">
            <div
                class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white font-bold text-xl">
                <i class="fa-solid fa-mug-hot"></i></div>
            <span class="text-xl font-bold tracking-tight">Oak Admin</span>
        </div>
        <div class="p-4 flex flex-col gap-1 flex-1 overflow-y-auto">
            <a href="admin.php"
                class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i
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
                class="px-4 py-3 bg-brand-red text-white rounded-xl font-medium flex items-center gap-3 shadow-[0_4px_14px_rgba(230,57,70,0.4)]"><i
                    class="fa-solid fa-file-invoice-dollar w-5"></i> Laporan Penjualan</a>
            <a href="admin_profil.php"
                class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i
                    class="fa-solid fa-user-tie w-5"></i> Profil Owner</a>
            <a href="logout.php"
                class="mt-auto px-4 py-3 text-white/50 hover:bg-white/5 hover:text-brand-red rounded-xl font-medium transition-colors flex items-center gap-2"><i
                    class="fa-solid fa-arrow-right-from-bracket"></i> Keluar</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center px-8 border-b border-black/5">
            <h2 class="text-2xl font-extrabold text-brand-black">Laporan Penjualan</h2>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-brand-black"><?php echo htmlspecialchars($admin->getNama()); ?></p>
                    <p class="text-xs text-brand-gray">Administrator</p>
                </div>
                <img src="<?php echo htmlspecialchars($admin->getFotoProfile() ?: 'https://i.pravatar.cc/150?img=11'); ?>"
                    alt="Profile" class="w-10 h-10 rounded-full border-2 border-brand-red/20 object-cover"
                    onerror="this.src='https://i.pravatar.cc/150?img=11'">
            </div>
        </header>

        <div class="p-8 max-w-7xl mx-auto">
            <!-- Filter & Action Bar -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/5 mb-6">
                <form action="" method="GET" class="flex flex-wrap gap-3 items-center">
                    <div class="relative flex-1 min-w-48">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Cari ID transaksi atau kasir..."
                            class="pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red text-sm w-full">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-calendar text-brand-gray"></i>
                        <input type="date" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>"
                            class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                        <span class="text-brand-gray font-bold">s/d</span>
                        <input type="date" name="tgl_akhir" value="<?php echo $tgl_akhir; ?>"
                            class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                    <button type="submit"
                        class="px-5 py-2.5 bg-brand-black text-white rounded-xl font-semibold text-sm hover:bg-brand-gray transition-colors">Tampilkan</button>
                    <a href="admin_laporan.php"
                        class="px-4 py-2.5 text-brand-gray text-sm hover:text-brand-red transition-colors font-medium">Reset</a>
                </form>
            </div>

            <!-- Export Buttons -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Riwayat Transaksi</h3>
                <div class="flex gap-3">
                    <a href="?search=<?php echo urlencode($search); ?>&tgl_mulai=<?php echo $tgl_mulai; ?>&tgl_akhir=<?php echo $tgl_akhir; ?>&export=csv"
                        class="px-4 py-2 border-2 border-brand-black text-brand-black rounded-xl font-semibold text-sm hover:bg-brand-black hover:text-white transition-all flex items-center gap-2">
                        <i class="fa-solid fa-file-csv"></i> Ekspor CSV
                    </a>
                    <button onclick="exportPDF()"
                        class="px-4 py-2 bg-brand-red text-white rounded-xl font-semibold text-sm hover:bg-brand-darkred transition-colors shadow-[0_4px_14px_rgba(230,57,70,0.3)] flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-black/5 overflow-hidden">
                <table id="tabelLaporan" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-black text-white text-sm">
                            <th class="p-4 font-bold">ID Transaksi</th>
                            <th class="p-4 font-bold">Waktu</th>
                            <th class="p-4 font-bold">Kasir</th>
                            <th class="p-4 font-bold text-center">Total Item</th>
                            <th class="p-4 font-bold text-right">Total Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <?php if (empty($laporan)): ?>
                            <tr>
                                <td colspan="5" class="p-10 text-center text-brand-gray">Tidak ada transaksi yang ditemukan
                                    pada rentang tanggal ini.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($laporan as $lap): ?>
                            <tr class="hover:bg-brand-offwhite/50 transition-colors">
                                <td class="p-4">
                                    <span
                                        class="font-mono text-sm font-bold text-brand-red"><?php echo htmlspecialchars($lap['id_pesanan']); ?></span>
                                </td>
                                <td class="p-4 text-sm text-brand-gray">
                                    <?php echo date('d M Y, H:i', strtotime($lap['waktu_pesanan'])); ?></td>
                                <td class="p-4 font-medium"><?php echo htmlspecialchars($lap['nama_kasir']); ?></td>
                                <td class="p-4 text-center font-bold"><?php echo $lap['total_item']; ?></td>
                                <td class="p-4 text-right font-black text-brand-black">Rp
                                    <?php echo number_format($lap['total_pembayaran'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if (!empty($laporan)): ?>
                        <tfoot>
                            <tr class="bg-brand-offwhite border-t-2 border-brand-red/20">
                                <td colspan="4" class="p-4 font-bold text-right text-brand-gray">TOTAL KESELURUHAN:</td>
                                <td class="p-4 text-right font-black text-2xl text-brand-red">Rp
                                    <?php echo number_format($total_pendapatan_filter, 0, ',', '.'); ?></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </main>

    <script>
        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.setFontSize(16);
            doc.setFont('helvetica', 'bold');
            doc.text('Laporan Penjualan - Oak Coffee', 14, 20);

            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.text('Periode: <?php echo date("d M Y", strtotime($tgl_mulai)); ?> s/d <?php echo date("d M Y", strtotime($tgl_akhir)); ?>', 14, 28);

            const rows = [];
            <?php foreach ($laporan as $lap): ?>
                rows.push([
                    "<?php echo addslashes($lap['id_pesanan']); ?>",
                    "<?php echo date('d/m/Y H:i', strtotime($lap['waktu_pesanan'])); ?>",
                    "<?php echo addslashes($lap['nama_kasir']); ?>",
                    "<?php echo $lap['total_item']; ?>",
                    "Rp <?php echo number_format($lap['total_pembayaran'], 0, ',', '.'); ?>"
                ]);
            <?php endforeach; ?>

            doc.autoTable({
                startY: 35,
                head: [['ID Transaksi', 'Waktu', 'Kasir', 'Total Item', 'Total Harga']],
                body: rows,
                foot: [['', '', '', 'TOTAL', 'Rp <?php echo number_format($total_pendapatan_filter, 0, ',', '.'); ?>']],
                headStyles: { fillColor: [17, 17, 17], textColor: 255, fontStyle: 'bold' },
                footStyles: { fillColor: [230, 57, 70], textColor: 255, fontStyle: 'bold' },
                alternateRowStyles: { fillColor: [248, 249, 250] },
                styles: { font: 'helvetica', fontSize: 9 }
            });

            doc.save('laporan_penjualan_<?php echo $tgl_mulai; ?>_sd_<?php echo $tgl_akhir; ?>.pdf');
        }
    </script>
</body>

</html>