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

$success_msg = '';
$error_msg = '';

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah_bahan') {
        $namaFoto = '';
        if (isset($_FILES['foto_bahan_baku']) && $_FILES['foto_bahan_baku']['error'] !== 4) {
            $result = $admin->uploadGambar($_FILES['foto_bahan_baku']);
            if ($result !== false) $namaFoto = $result;
        }

        $bahanData = [
            'id_bahan_baku'   => $admin->generateIdBahanBaku(),
            'nama_bahan_baku' => $_POST['nama_bahan_baku'],
            'stok'            => $_POST['stok'],
            'satuan'          => $_POST['satuan'],
            'harga_beli'      => $_POST['harga_beli'],
            'foto_bahan_baku' => $namaFoto
        ];
        if ($admin->tambahBahanBaku($bahanData)) {
            $success_msg = 'Bahan baku berhasil ditambahkan!';
        } else {
            $error_msg = 'Gagal menambahkan bahan baku.';
        }
    } elseif ($_POST['action'] === 'edit_bahan') {
        $namaFotoBaru = '';
        if (isset($_FILES['foto_bahan_edit']) && $_FILES['foto_bahan_edit']['error'] !== 4) {
            $result = $admin->uploadGambar($_FILES['foto_bahan_edit']);
            if ($result !== false) $namaFotoBaru = $result;
        }

        $newData = [
            'nama_bahan_baku' => $_POST['nama_bahan_baku'],
            'stok'            => $_POST['stok'],
            'satuan'          => $_POST['satuan'],
            'harga_beli'      => $_POST['harga_beli'],
            'foto_bahan_baku' => $namaFotoBaru
        ];
        if ($admin->editBahanBaku($_POST['id_bahan_edit'], $newData)) {
            $success_msg = 'Bahan baku berhasil diperbarui!';
        } else {
            $error_msg = 'Gagal memperbarui bahan baku.';
        }
    } elseif ($_POST['action'] === 'hapus_bahan') {
        if ($admin->hapusBahanBaku($_POST['id_bahan_hapus'])) {
            $success_msg = 'Bahan baku berhasil dihapus!';
        } else {
            $error_msg = 'Gagal menghapus bahan baku.';
        }
    }
}

$bahan_list = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';
try {
    $bahan_list = !empty($search) ? $admin->cariBahanBaku($search) : $admin->tampilkanBahanBaku();
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}

// Batas minimum stok (anggap 500 sebagai batas kritis jika kolom tidak ada)
function getStatusBadge($stok, $satuan) {
    $batas = 500; // default minimal
    if ($stok > $batas) {
        return '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200 flex items-center gap-1"><i class="fa-solid fa-circle-check"></i> Aman</span>';
    } elseif ($stok > 0) {
        return '<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold border border-yellow-200 flex items-center gap-1"><i class="fa-solid fa-triangle-exclamation"></i> Menipis</span>';
    } else {
        return '<span class="px-3 py-1 bg-red-100 text-brand-red rounded-full text-xs font-bold border border-red-200 flex items-center gap-1"><i class="fa-solid fa-circle-xmark"></i> Habis</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahan Baku - Oak Coffee POS</title>
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
            <div class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white font-bold text-xl"><i class="fa-solid fa-mug-hot"></i></div>
            <span class="text-xl font-bold tracking-tight">Oak Admin</span>
        </div>
        <div class="p-4 flex flex-col gap-1 flex-1 overflow-y-auto">
            <a href="admin.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-chart-pie w-5"></i> Dashboard</a>
            <a href="admin_menu.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-utensils w-5"></i> Manajemen Menu</a>
            <a href="admin_user.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-users w-5"></i> Manajemen User</a>
            <a href="admin_bahan_baku.php" class="px-4 py-3 bg-brand-red text-white rounded-xl font-medium flex items-center gap-3 shadow-[0_4px_14px_rgba(230,57,70,0.4)]"><i class="fa-solid fa-boxes-stacked w-5"></i> Bahan Baku</a>
            <a href="admin_laporan.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-file-invoice-dollar w-5"></i> Laporan Penjualan</a>
            <a href="admin_profil.php" class="px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl font-medium transition-colors flex items-center gap-3"><i class="fa-solid fa-user-tie w-5"></i> Profil Owner</a>
            <a href="logout.php" class="mt-auto px-4 py-3 text-white/50 hover:bg-white/5 hover:text-brand-red rounded-xl font-medium transition-colors flex items-center gap-2"><i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto">
        <header class="bg-white shadow-sm p-4 flex justify-between items-center px-8 border-b border-black/5">
            <h2 class="text-2xl font-extrabold text-brand-black">Inventori Bahan Baku</h2>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-brand-black"><?php echo htmlspecialchars($admin->getNama()); ?></p>
                    <p class="text-xs text-brand-gray">Administrator</p>
                </div>
                <img src="<?php echo htmlspecialchars($admin->getFotoProfile() ?: 'https://i.pravatar.cc/150?img=11'); ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-brand-red/20 object-cover" onerror="this.src='https://i.pravatar.cc/150?img=11'">
            </div>
        </header>

        <div class="p-8 max-w-7xl mx-auto">

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

            <!-- Header Actions -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold">Daftar Bahan Baku</h3>
                    <p class="text-brand-gray text-sm mt-1">Pantau, tambah, dan sesuaikan stok bahan mentah</p>
                </div>
                <div class="flex items-center gap-4">
                    <form action="" method="GET" class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari bahan baku..." class="pl-10 pr-8 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red text-sm w-56">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <?php if (!empty($search)): ?>
                            <a href="admin_bahan_baku.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-brand-red"><i class="fa-solid fa-xmark"></i></a>
                        <?php endif; ?>
                    </form>
                    <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-brand-red hover:bg-brand-darkred text-white px-5 py-2.5 rounded-xl font-semibold shadow-[0_4px_14px_rgba(230,57,70,0.4)] transition-all flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Tambah Bahan
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-black/5 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-brand-black text-white text-sm">
                            <th class="p-4 font-bold">Bahan Baku</th>
                            <th class="p-4 font-bold">Stok Saat Ini</th>
                            <th class="p-4 font-bold">Satuan</th>
                            <th class="p-4 font-bold">Harga Beli</th>
                            <th class="p-4 font-bold text-center">Status</th>
                            <th class="p-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <?php if (empty($bahan_list) && !isset($error_msg)): ?>
                            <tr><td colspan="6" class="p-8 text-center text-brand-gray">Belum ada data bahan baku.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($bahan_list as $b): ?>
                            <tr class="hover:bg-brand-offwhite/50 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <img src="images/<?php echo htmlspecialchars($b['foto_bahan_baku'] ?: 'default.png'); ?>" alt="Foto" class="w-10 h-10 rounded-lg object-cover border border-gray-200" onerror="this.src='https://via.placeholder.com/150'">
                                        <p class="font-bold text-brand-black"><?php echo htmlspecialchars($b['nama_bahan_baku']); ?></p>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-2xl font-black text-brand-black"><?php echo number_format($b['stok'], 0, ',', '.'); ?></span>
                                    <span class="text-xs text-brand-gray ml-1"><?php echo htmlspecialchars($b['satuan']); ?></span>
                                </td>
                                <td class="p-4 text-brand-gray text-sm font-medium"><?php echo htmlspecialchars($b['satuan']); ?></td>
                                <td class="p-4 font-bold text-brand-black">Rp <?php echo number_format($b['harga_beli'], 0, ',', '.'); ?></td>
                                <td class="p-4 text-center">
                                    <?php echo getStatusBadge($b['stok'], $b['satuan']); ?>
                                </td>
                                <td class="p-4 text-center">
                                    <button onclick='openEditBahan(<?php echo json_encode($b); ?>)' class="w-8 h-8 rounded-lg text-brand-gray hover:bg-gray-100 transition-colors"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button onclick='openHapusBahan("<?php echo $b['id_bahan_baku']; ?>", "<?php echo htmlspecialchars($b['nama_bahan_baku']); ?>")' class="w-8 h-8 rounded-lg text-brand-red hover:bg-red-50 transition-colors"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Bahan -->
    <div id="modalTambah" class="fixed inset-0 bg-brand-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-black/5 flex justify-between items-center">
                <h3 class="text-xl font-extrabold">Tambah Bahan Baku</h3>
                <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-brand-gray hover:text-brand-red"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4">
                <input type="hidden" name="action" value="tambah_bahan">
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Nama Bahan Baku</label>
                    <input type="text" name="nama_bahan_baku" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Stok Awal</label>
                        <input type="number" name="stok" required min="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Satuan</label>
                        <select name="satuan" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                            <option value="g">g (Gram)</option>
                            <option value="ml">ml (Mililiter)</option>
                            <option value="kg">kg (Kilogram)</option>
                            <option value="L">L (Liter)</option>
                            <option value="pcs">pcs</option>
                            <option value="bks">bks (Bungkus)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Harga Beli (Rp)</label>
                    <input type="number" name="harga_beli" min="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Foto Bahan Baku</label>
                    <input type="file" name="foto_bahan_baku" accept="image/jpg,image/jpeg,image/png" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm">
                </div>
                <div class="mt-2 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="flex-1 px-4 py-3 rounded-xl font-bold text-brand-gray bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 rounded-xl font-bold text-white bg-brand-red shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Bahan -->
    <div id="modalEdit" class="fixed inset-0 bg-brand-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-6 border-b border-black/5 flex justify-between items-center">
                <h3 class="text-xl font-extrabold">Edit Bahan Baku</h3>
                <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-brand-gray hover:text-brand-red"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="p-6 flex flex-col gap-4">
                <input type="hidden" name="action" value="edit_bahan">
                <input type="hidden" name="id_bahan_edit" id="edit_id_bahan">
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Nama Bahan Baku</label>
                    <input type="text" name="nama_bahan_baku" id="edit_nama_bahan" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Stok</label>
                        <input type="number" name="stok" id="edit_stok" min="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-brand-gray mb-1">Satuan</label>
                        <select name="satuan" id="edit_satuan" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                            <option value="g">g (Gram)</option>
                            <option value="ml">ml (Mililiter)</option>
                            <option value="kg">kg (Kilogram)</option>
                            <option value="L">L (Liter)</option>
                            <option value="pcs">pcs</option>
                            <option value="bks">bks (Bungkus)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Harga Beli (Rp)</label>
                    <input type="number" name="harga_beli" id="edit_harga_beli" min="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red">
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-gray mb-1">Ganti Foto <span class="text-xs text-gray-400">(opsional)</span></label>
                    <div class="flex items-center gap-3">
                        <img id="edit_foto_preview" src="" alt="" class="w-12 h-12 rounded-lg object-cover border border-gray-200 hidden">
                        <input type="file" name="foto_bahan_edit" accept="image/jpg,image/jpeg,image/png" onchange="previewFotoEdit(this)" class="flex-1 px-4 py-2 border border-gray-200 rounded-xl text-sm">
                    </div>
                </div>
                <div class="mt-2 flex gap-3">
                    <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="flex-1 px-4 py-3 rounded-xl font-bold text-brand-gray bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 rounded-xl font-bold text-white bg-brand-red shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Bahan -->
    <div id="modalHapus" class="fixed inset-0 bg-brand-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-8 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-boxes-stacked text-2xl text-brand-red"></i>
            </div>
            <h3 class="text-xl font-extrabold mb-2">Hapus Bahan Baku?</h3>
            <p class="text-brand-gray mb-6">Bahan <strong id="hapus_nama_bahan"></strong> akan dihapus permanen dari sistem.</p>
            <form action="" method="POST" class="flex gap-3">
                <input type="hidden" name="action" value="hapus_bahan">
                <input type="hidden" name="id_bahan_hapus" id="hapus_id_bahan">
                <button type="button" onclick="document.getElementById('modalHapus').classList.add('hidden')" class="flex-1 px-4 py-3 rounded-xl font-bold text-brand-gray bg-gray-100 hover:bg-gray-200 transition-colors">Batal</button>
                <button type="submit" class="flex-1 px-4 py-3 rounded-xl font-bold text-white bg-brand-red shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred transition-colors">Ya, Hapus</button>
            </form>
        </div>
    </div>

    <script>
        function openEditBahan(b) {
            document.getElementById('edit_id_bahan').value = b.id_bahan_baku;
            document.getElementById('edit_nama_bahan').value = b.nama_bahan_baku;
            document.getElementById('edit_stok').value = b.stok;
            document.getElementById('edit_satuan').value = b.satuan;
            document.getElementById('edit_harga_beli').value = b.harga_beli;

            var preview = document.getElementById('edit_foto_preview');
            if (b.foto_bahan_baku) {
                preview.src = 'images/' + b.foto_bahan_baku;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }

            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function openHapusBahan(id, nama) {
            document.getElementById('hapus_id_bahan').value = id;
            document.getElementById('hapus_nama_bahan').textContent = nama;
            document.getElementById('modalHapus').classList.remove('hidden');
        }

        // Preview foto sebelum diupload di modal edit
        function previewFotoEdit(input) {
            var preview = document.getElementById('edit_foto_preview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
