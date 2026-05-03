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

$success_msg = '';
$error_msg = '';

// Handle Tambah User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah_user') {
        $userData = [
            'id_users' => $_POST['id_users'],
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'nama' => $_POST['nama'],
            'alamat' => $_POST['alamat'],
            'jenis_kelamin' => $_POST['jenis_kelamin'],
            'foto_profil' => ''
        ];
        if ($admin->tambahUser($userData)) {
            $success_msg = 'User berhasil ditambahkan!';
        } else {
            $error_msg = 'Gagal menambahkan user.';
        }
    } elseif ($_POST['action'] === 'edit_user') {
        $newData = [
            'username' => $_POST['username'],
            'nama' => $_POST['nama'],
            'alamat' => $_POST['alamat'],
        ];
        if ($admin->editUser($_POST['id_users_edit'], $newData)) {
            $success_msg = 'User berhasil diperbarui!';
        } else {
            $error_msg = 'Gagal memperbarui user.';
        }
    } elseif ($_POST['action'] === 'hapus_user') {
        if ($admin->hapusUser($_POST['id_users_hapus'])) {
            $success_msg = 'User berhasil dihapus!';
        } else {
            $error_msg = 'Gagal menghapus user.';
        }
    }
}

$users = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';
try {
    $users = !empty($search) ? $admin->cariUser($search) : $admin->tampilkanUser();
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}

// Helper function untuk inisial avatar
function getInitials($name)
{
    $words = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= strtoupper(mb_substr($word, 0, 1));
    }
    return $initials ?: '?';
}

$avatar_colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-orange-500', 'bg-pink-500', 'bg-teal-500', 'bg-indigo-500'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Oak Coffee POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            <div
                class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white font-bold text-xl">
                <i class="fa-solid fa-mug-hot"></i>
            </div>
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
                class="px-4 py-3 bg-brand-red text-white rounded-xl font-medium flex items-center gap-3 shadow-[0_4px_14px_rgba(230,57,70,0.4)]"><i
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
                class="mt-auto px-4 py-3 text-white/50 hover:bg-white/5 hover:text-brand-red rounded-xl font-medium transition-colors flex items-center gap-2"><i
                    class="fa-solid fa-arrow-right-from-bracket"></i> Keluar</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center px-8 border-b border-black/5">
            <h2 class="text-2xl font-extrabold text-brand-black">Manajemen User</h2>
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

            <?php if ($success_msg): ?>
                <div
                    class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div
                    class="bg-red-100 border-l-4 border-brand-red text-brand-darkred p-4 mb-6 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <!-- Header Actions -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold">Daftar Pengguna Sistem</h3>
                    <p class="text-brand-gray text-sm mt-1">Kelola akun kasir, koki, dan admin</p>
                </div>
                <div class="flex items-center gap-4">
                    <form action="" method="GET" class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Cari nama atau username..."
                            class="pl-10 pr-8 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red text-sm w-64">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <?php if (!empty($search)): ?>
                            <a href="admin_user.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-red"><i
                                    class="fa-solid fa-xmark"></i></a>
                        <?php endif; ?>
                    </form>
                    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')"
                        class="bg-brand-red hover:bg-brand-darkred text-white px-5 py-2.5 rounded-xl font-semibold shadow-[0_4px_14px_rgba(230,57,70,0.4)] transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Tambah User
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-black/5 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-black text-white text-sm">
                            <th class="p-4 font-bold">Nama Staf</th>
                            <th class="p-4 font-bold">Username</th>
                            <th class="p-4 font-bold">Password</th>
                            <th class="p-4 font-bold">Alamat</th>
                            <th class="p-4 font-bold">Jenis Kelamin</th>
                            <th class="p-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <?php if (empty($users) && !isset($error_msg)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-brand-gray">Belum ada user terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($users as $i => $u): ?>
                            <?php
                            $initials = getInitials($u['nama']);
                            $colorClass = $avatar_colors[$i % count($avatar_colors)];
                            ?>
                            <tr class="hover:bg-brand-offwhite/50 transition-colors">
                                <td class="p-4 flex items-center gap-3">
                                    <!-- Avatar Inisial -->
                                    <div
                                        class="w-10 h-10 rounded-full <?php echo $colorClass; ?> flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-brand-black"><?php echo htmlspecialchars($u['nama']); ?>
                                        </p>
                                        <p class="text-xs text-brand-gray"><?php echo htmlspecialchars($u['id_users']); ?>
                                        </p>
                                    </div>
                                </td>
                                <td class="p-4 text-brand-gray font-medium"><?php echo htmlspecialchars($u['username']); ?>
                                </td>
                                <td class="p-4 text-brand-gray font-medium"><?php echo htmlspecialchars($u['password']); ?>
                                </td>
                                <td class="p-4 text-brand-gray text-sm"><?php echo htmlspecialchars($u['alamat'] ?: '-'); ?>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="px-3 py-1 bg-gray-100 text-brand-gray rounded-lg text-xs font-bold border border-black/5">
                                        <?php echo htmlspecialchars($u['jenis_kelamin'] ?: '-'); ?>
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button onclick='openEditModal(<?php echo json_encode($u); ?>)'
                                        class="w-8 h-8 rounded-lg text-brand-gray hover:bg-gray-100 transition-colors"><i
                                            class="fa-solid fa-pen-to-square"></i></button>
                                    <button
                                        onclick='openHapusModal("<?php echo htmlspecialchars($u['id_users']); ?>", "<?php echo htmlspecialchars($u['nama']); ?>")'
                                        class="w-8 h-8 rounded-lg text-brand-red hover:bg-red-50 transition-colors"><i
                                            class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah User -->
    <div id="modalTambah"
        class="fixed inset-0 bg-brand-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-black/5 flex justify-between items-center">
                <h3 class="text-xl font-extrabold">Tambah User Baru</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')"
                    class="text-brand-gray hover:text-brand-red"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="" method="POST" class="p-6 flex flex-col gap-4">
                <input type="hidden" name="action" value="tambah_user">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">ID User</label>
                        <input type="text" name="id_users" required placeholder="Cth: USR002"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Username</label>
                        <input type="text" name="username" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Alamat</label>
                    <input type="text" name="alamat"
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div class="mt-2 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')"
                        class="flex-1 px-4 py-3 rounded-xl font-bold text-brand-gray bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 rounded-xl font-bold text-white bg-brand-red shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred transition-colors">Simpan
                        User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div id="modalEdit"
        class="fixed inset-0 bg-brand-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-black/5 flex justify-between items-center">
                <h3 class="text-xl font-extrabold">Edit User</h3>
                <button onclick="document.getElementById('modalEdit').classList.add('hidden')"
                    class="text-brand-gray hover:text-brand-red"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="" method="POST" class="p-6 flex flex-col gap-4">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="id_users_edit" id="edit_id_users">
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" id="edit_nama" required
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Username</label>
                        <input type="text" name="username" id="edit_username" required
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Alamat</label>
                        <input type="text" name="alamat" id="edit_alamat"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                </div>
                <div class="mt-2 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')"
                        class="flex-1 px-4 py-3 rounded-xl font-bold text-brand-gray bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 rounded-xl font-bold text-white bg-brand-red shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred transition-colors">Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus User -->
    <div id="modalHapus"
        class="fixed inset-0 bg-brand-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden p-8 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash text-2xl text-brand-red"></i>
            </div>
            <h3 class="text-xl font-extrabold mb-2">Hapus User?</h3>
            <p class="text-brand-gray mb-6">User <strong id="hapus_nama_user"></strong> akan dihapus permanen dari
                sistem.</p>
            <form action="" method="POST" class="flex gap-3">
                <input type="hidden" name="action" value="hapus_user">
                <input type="hidden" name="id_users_hapus" id="hapus_id_users">
                <button type="button" onclick="document.getElementById('modalHapus').classList.add('hidden')"
                    class="flex-1 px-4 py-3 rounded-xl font-bold text-brand-gray bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                <button type="submit"
                    class="flex-1 px-4 py-3 rounded-xl font-bold text-white bg-brand-red shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred transition-colors">Ya,
                    Hapus</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(user) {
            document.getElementById('edit_id_users').value = user.id_users;
            document.getElementById('edit_nama').value = user.nama;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_alamat').value = user.alamat || '';
            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function openHapusModal(id, nama) {
            document.getElementById('hapus_id_users').value = id;
            document.getElementById('hapus_nama_user').textContent = nama;
            document.getElementById('modalHapus').classList.remove('hidden');
        }
    </script>
</body>

</html>