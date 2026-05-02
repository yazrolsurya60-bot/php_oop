<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';
require_once 'classes/Menu.php';

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

// Handle form tambah menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'tambah_menu') {
    $dataProduk = [
        'nama_produk' => $_POST['nama_produk'],
        'deskripsi' => $_POST['deskripsi'],
        'harga' => $_POST['harga'],
        'status_tersedia' => isset($_POST['status_tersedia']) ? 1 : 0,
        'kategori' => $_POST['kategori'],
        'foto_produk' => $_POST['foto_produk']
    ];
    $admin->tambahProduk($dataProduk);
    header("Location: admin.php");
    exit();
}

// Ambil daftar menu dari database
$menus = [];
try {
    $menus = $admin->tampilkanProduk();
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}

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
            <div class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white font-bold text-xl"><i class="fa-solid fa-mug-hot"></i></div>
            <span class="text-xl font-bold tracking-tight">Oak Admin</span>
        </div>

        <div class="p-6 flex flex-col gap-2 flex-1">
            <a href="admin.php" class="px-4 py-3 bg-brand-red text-white rounded-xl font-medium transition-colors shadow-[0_4px_14px_rgba(230,57,70,0.4)]"><i class="fa-solid fa-utensils w-6"></i> Manajemen Menu</a>
            <a href="#" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors"><i class="fa-solid fa-users w-6"></i> Manajemen User</a>
            <a href="#" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors"><i class="fa-solid fa-chart-pie w-6"></i> Laporan Keuangan</a>
            
            <a href="logout.php" class="mt-auto px-4 py-3 text-white/50 hover:bg-white/5 hover:text-brand-red rounded-xl font-medium transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <!-- Topbar -->
        <header class="bg-white shadow-sm p-4 flex justify-between items-center px-8 border-b border-black/5">
            <h2 class="text-2xl font-extrabold text-brand-black">Manajemen Produk</h2>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-brand-black"><?php echo htmlspecialchars($admin->getNama()); ?></p>
                    <p class="text-xs text-brand-gray">Administrator</p>
                </div>
                <!-- Menggunakan foto dari database -->
                <img src="<?php echo htmlspecialchars($admin->getFotoProfile() ?: 'https://i.pravatar.cc/150?img=11'); ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-brand-red/20 object-cover" onerror="this.src='https://i.pravatar.cc/150?img=11'">
            </div>
        </header>

        <!-- Content -->
        <div class="p-8 max-w-7xl mx-auto">
            
            <?php if(isset($error_msg)): ?>
                <div class="bg-red-100 border-l-4 border-brand-red text-brand-darkred p-4 mb-6 rounded-lg">
                    <p class="font-bold">Error Database:</p>
                    <p><?php echo $error_msg; ?></p>
                    <p class="text-sm mt-2">Pastikan tabel `menu` sudah dibuat di database `db_sistem_pos`.</p>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold">Katalog Menu</h3>
                    <p class="text-brand-gray text-sm mt-1">Kelola daftar menu dan ketersediaan stok</p>
                </div>
                <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-brand-red hover:bg-brand-darkred text-white px-5 py-2.5 rounded-xl font-semibold shadow-[0_4px_14px_rgba(230,57,70,0.4)] transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah Menu
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-black/5 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-offwhite text-brand-gray text-sm border-b border-black/5">
                            <th class="p-4 font-bold">Menu</th>
                            <th class="p-4 font-bold">Kategori</th>
                            <th class="p-4 font-bold">Harga</th>
                            <th class="p-4 font-bold text-center">Status</th>
                            <th class="p-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <?php if(empty($menus) && !isset($error_msg)): ?>
                        <tr><td colspan="5" class="p-8 text-center text-brand-gray">Belum ada menu di database. Silakan tambah menu.</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach ($menus as $m): ?>
                            <tr class="hover:bg-brand-offwhite/50 transition-colors">
                                <td class="p-4 flex gap-4 items-center">
                                    <img src="<?php echo htmlspecialchars($m['foto_produk']); ?>" class="w-16 h-16 rounded-xl object-cover shadow-sm border border-black/5" onerror="this.src='https://via.placeholder.com/150'">
                                    <div>
                                        <p class="font-bold text-brand-black text-lg"><?php echo htmlspecialchars($m['nama_produk']); ?></p>
                                        <p class="text-sm text-brand-gray max-w-xs truncate"><?php echo htmlspecialchars($m['deskripsi']); ?></p>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1 bg-gray-100 text-brand-gray rounded-lg text-xs font-bold border border-black/5">
                                        <?php echo htmlspecialchars($m['kategori']); ?>
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-brand-black">
                                    Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?>
                                </td>
                                <td class="p-4 text-center">
                                    <?php if ($m['status_tersedia']): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold border border-green-200">Tersedia</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-100 text-brand-red rounded-lg text-xs font-bold border border-red-200">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center">
                                    <button class="w-8 h-8 rounded-lg text-brand-gray hover:bg-gray-100 transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button class="w-8 h-8 rounded-lg text-brand-red hover:bg-red-50 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Menu -->
    <div id="modalTambah" class="fixed inset-0 bg-brand-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-black/5 flex justify-between items-center">
                <h3 class="text-xl font-extrabold text-brand-black">Tambah Menu Baru</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-brand-gray hover:text-brand-red"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="" method="POST" class="p-6 flex flex-col gap-4">
                <input type="hidden" name="action" value="tambah_menu">
                
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Nama Produk</label>
                    <input type="text" name="nama_produk" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Deskripsi</label>
                    <textarea name="deskripsi" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red" rows="2"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Harga (Rp)</label>
                        <input type="number" name="harga" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Kategori</label>
                        <select name="kategori" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                            <option value="Kopi">Kopi</option>
                            <option value="Non-Kopi">Non-Kopi</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Cemilan">Cemilan</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Foto Produk</label>
                    <input type="file" name="foto_produk" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="status_tersedia" id="status" checked class="w-4 h-4 text-brand-red focus:ring-brand-red rounded">
                    <label for="status" class="text-sm font-bold text-brand-gray">Menu Tersedia Saat Ini</label>
                </div>
                <div class="mt-4 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="flex-1 px-4 py-3 rounded-xl font-bold text-brand-gray bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 rounded-xl font-bold text-white bg-brand-red shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred transition-colors">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>