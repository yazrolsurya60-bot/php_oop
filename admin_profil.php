<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Admin.php';

if (!isset($_SESSION['id_users'])) { header("Location: login.php"); exit(); }
$id_users_login = $_SESSION['id_users'];
$query = "SELECT u.*, a.id_admin FROM users u JOIN admin a ON u.id_users = a.id_users WHERE u.id_users = ?";
$stmt = $conn->getConn()->prepare($query);
$stmt->bind_param("s", $id_users_login);
$stmt->execute();
$data_login = $stmt->get_result()->fetch_assoc();
if (!$data_login) { header("Location: index.php"); exit(); }

$admin = new Admin($data_login['id_users'], $data_login['username'], $data_login['password'], $data_login['nama'], $data_login['alamat'], $data_login['jenis_kelamin'], $data_login['foto_profil'], $data_login['id_admin']);

// Handle update profil
$success_msg = '';
$error_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profil') {
    $newData = [
        'username' => $_POST['username'],
        'nama'     => $_POST['nama'],
        'alamat'   => $_POST['alamat'],
    ];
    if ($admin->editUser($data_login['id_users'], $newData)) {
        $success_msg = 'Profil berhasil diperbarui!';
        // Refresh data
        $stmt2 = $conn->getConn()->prepare($query);
        $stmt2->bind_param("s", $id_users_login);
        $stmt2->execute();
        $data_login = $stmt2->get_result()->fetch_assoc();
    } else {
        $error_msg = 'Gagal memperbarui profil.';
    }
}

// Data profil pengembang (hardcode untuk laporan / presentasi)
$developer = [
    'nama'        => 'Yazrol Surya',
    'nim'         => '3202402003',
    'prodi'       => 'Manajemen Informatika',
    'kampus'      => 'Politeknik Negeri Sambas',
    'role'        => 'Mahasiswa',
    'versi'       => 'v1.0.0 (Beta)',
    'foto'        => 'https://i.pravatar.cc/150?img=12',
    'app_desc'    => 'Aplikasi Point of Sale (POS) Oak Coffee System dirancang khusus untuk mempermudah manajemen kafe dengan antarmuka yang modern, cepat, dan responsif.',
    'app_info'    => 'Dibuat dan dikembangkan sebagai bagian dari project / tugas akhir untuk memenuhi persyaratan akademis.',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Owner - Oak Coffee POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <div class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white font-bold text-xl"><i class="fa-solid fa-mug-hot"></i></div>
            <span class="text-xl font-bold tracking-tight">Oak Admin</span>
        </div>
        <div class="p-4 flex flex-col gap-1 flex-1 overflow-y-auto">
            <a href="admin.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-chart-pie w-5"></i> Dashboard</a>
            <a href="admin_menu.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-utensils w-5"></i> Manajemen Menu</a>
            <a href="admin_user.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-users w-5"></i> Manajemen User</a>
            <a href="admin_bahan_baku.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-boxes-stacked w-5"></i> Bahan Baku</a>
            <a href="admin_laporan.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-file-invoice-dollar w-5"></i> Laporan Penjualan</a>
            <a href="admin_profil.php" class="px-4 py-3 bg-brand-red text-white rounded-xl font-medium flex items-center gap-3 shadow-[0_4px_14px_rgba(230,57,70,0.4)]"><i class="fa-solid fa-user-tie w-5"></i> Profil Owner</a>
            <a href="logout.php" class="mt-auto px-4 py-3 text-white/50 hover:bg-white/5 hover:text-brand-red rounded-xl font-medium transition-colors flex items-center gap-2"><i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center px-8 border-b border-black/5">
            <div>
                <h2 class="text-2xl font-extrabold text-brand-black">Profil Pengembang</h2>
                <p class="text-xs text-brand-gray mt-0.5">Informasi pembuat sistem Point of Sale Oak Coffee.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-brand-black"><?php echo htmlspecialchars($admin->getNama()); ?></p>
                    <p class="text-xs text-brand-gray">Administrator</p>
                </div>
                <img src="<?php echo htmlspecialchars($admin->getFotoProfile() ?: 'https://i.pravatar.cc/150?img=11'); ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-brand-red/20 object-cover" onerror="this.src='https://i.pravatar.cc/150?img=11'">
            </div>
        </header>

        <div class="p-8 max-w-4xl mx-auto">

            <?php if ($success_msg): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="bg-red-100 border-l-4 border-brand-red text-brand-darkred p-4 mb-6 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <!-- Profile Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-black/5 overflow-hidden">
                <!-- Banner -->
                <div class="h-40 bg-gradient-to-br from-brand-black via-brand-gray to-brand-red relative">
                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_20%_50%,_white_1px,_transparent_1px)] bg-[length:20px_20px]"></div>
                </div>

                <!-- Avatar & Name Row -->
                <div class="px-8 pb-6 relative">
                    <!-- Avatar -->
                    <div class="absolute -top-14 left-8 w-28 h-28 rounded-2xl border-4 border-white shadow-xl overflow-hidden bg-white">
                        <img src="<?php echo htmlspecialchars($developer['foto']); ?>" alt="Developer Photo" class="w-full h-full object-cover" onerror="this.src='https://i.pravatar.cc/150?img=12'">
                        <div class="absolute bottom-0 inset-x-0 bg-brand-red text-white text-center text-xs font-bold py-0.5">DEVELOPER</div>
                    </div>

                    <!-- Edit Button aligned right -->
                    <div class="flex justify-end pt-4 pb-2">
                        <button onclick="document.getElementById('modalEditProfil').classList.remove('hidden')" class="px-4 py-2 border-2 border-gray-200 text-brand-gray rounded-xl text-sm font-semibold hover:border-brand-red hover:text-brand-red transition-all flex items-center gap-2">
                            <i class="fa-solid fa-envelope"></i> Edit Profil Akun
                        </button>
                    </div>

                    <!-- Name & Role (offset for avatar) -->
                    <div class="mt-4 flex flex-col md:flex-row gap-8">
                        <!-- Left: Developer Info -->
                        <div class="flex-1">
                            <h3 class="text-3xl font-black text-brand-black"><?php echo htmlspecialchars($developer['nama']); ?></h3>
                            <p class="text-brand-red font-bold text-lg mt-1"><?php echo htmlspecialchars($developer['role']); ?></p>

                            <div class="mt-6">
                                <p class="text-xs font-bold text-brand-gray tracking-widest uppercase mb-3">Detail Informasi</p>
                                <div class="flex flex-col gap-3">
                                    <!-- NIM -->
                                    <div class="flex items-center gap-4 bg-brand-offwhite rounded-xl p-4 border border-black/5">
                                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-sm border border-black/5 flex-shrink-0">
                                            <i class="fa-solid fa-id-badge text-brand-gray text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-brand-gray uppercase tracking-wider">NIM</p>
                                            <p class="font-bold text-brand-black"><?php echo htmlspecialchars($developer['nim']); ?></p>
                                        </div>
                                    </div>
                                    <!-- Jurusan -->
                                    <div class="flex items-center gap-4 bg-brand-offwhite rounded-xl p-4 border border-black/5">
                                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-sm border border-black/5 flex-shrink-0">
                                            <i class="fa-solid fa-book-open text-brand-gray text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-brand-gray uppercase tracking-wider">Jurusan</p>
                                            <p class="font-bold text-brand-black"><?php echo htmlspecialchars($developer['prodi']); ?></p>
                                        </div>
                                    </div>
                                    <!-- Kampus -->
                                    <div class="flex items-center gap-4 bg-brand-offwhite rounded-xl p-4 border border-black/5">
                                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center shadow-sm border border-black/5 flex-shrink-0">
                                            <i class="fa-solid fa-building-columns text-brand-gray text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-brand-gray uppercase tracking-wider">Universitas/Kampus</p>
                                            <p class="font-bold text-brand-black"><?php echo htmlspecialchars($developer['kampus']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: App Info Card -->
                        <div class="w-full md:w-72 bg-brand-black text-white rounded-2xl p-6 flex flex-col gap-4 self-start">
                            <div>
                                <h4 class="text-lg font-extrabold mb-1">Tentang Aplikasi</h4>
                                <div class="w-8 h-1 bg-brand-red rounded-full"></div>
                            </div>
                            <p class="text-white/70 text-sm leading-relaxed"><?php echo htmlspecialchars($developer['app_desc']); ?></p>
                            <p class="text-white/70 text-sm leading-relaxed"><?php echo htmlspecialchars($developer['app_info']); ?></p>
                            <div class="mt-auto pt-4 border-t border-white/10">
                                <p class="text-xs text-white/40 uppercase tracking-widest font-bold">Versi Sistem</p>
                                <p class="text-brand-red font-extrabold text-lg mt-1"><?php echo htmlspecialchars($developer['versi']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Akun Login Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-black/5 p-6 mt-6">
                <h4 class="text-lg font-bold mb-4 flex items-center gap-2"><i class="fa-solid fa-shield-halved text-brand-red"></i> Informasi Akun Login</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-brand-offwhite rounded-xl p-4 border border-black/5">
                        <p class="text-xs font-bold text-brand-gray uppercase tracking-wider">Nama Lengkap</p>
                        <p class="font-bold text-brand-black mt-1"><?php echo htmlspecialchars($data_login['nama']); ?></p>
                    </div>
                    <div class="bg-brand-offwhite rounded-xl p-4 border border-black/5">
                        <p class="text-xs font-bold text-brand-gray uppercase tracking-wider">Username</p>
                        <p class="font-bold text-brand-black mt-1"><?php echo htmlspecialchars($data_login['username']); ?></p>
                    </div>
                    <div class="bg-brand-offwhite rounded-xl p-4 border border-black/5 col-span-2">
                        <p class="text-xs font-bold text-brand-gray uppercase tracking-wider">Alamat</p>
                        <p class="font-bold text-brand-black mt-1"><?php echo htmlspecialchars($data_login['alamat'] ?: '-'); ?></p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal Edit Profil Akun -->
    <div id="modalEditProfil" class="fixed inset-0 bg-brand-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-black/5 flex justify-between items-center">
                <h3 class="text-xl font-extrabold">Edit Profil Akun</h3>
                <button onclick="document.getElementById('modalEditProfil').classList.add('hidden')" class="text-brand-gray hover:text-brand-red"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="" method="POST" class="p-6 flex flex-col gap-4">
                <input type="hidden" name="action" value="update_profil">
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($data_login['nama']); ?>" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($data_login['username']); ?>" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Alamat</label>
                        <input type="text" name="alamat" value="<?php echo htmlspecialchars($data_login['alamat']); ?>" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                </div>
                <div class="mt-2 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalEditProfil').classList.add('hidden')" class="flex-1 px-4 py-3 rounded-xl font-bold text-brand-gray bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 rounded-xl font-bold text-white bg-brand-red shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
