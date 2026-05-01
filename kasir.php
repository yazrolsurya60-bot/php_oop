<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Kasir.php';
require_once 'classes/Menu.php';
require_once 'classes/ItemPesanan.php';

// 1. Cek Sesi Login & Role Kasir
if (!isset($_SESSION['id_users'])) {
    header("Location: login.php");
    exit();
}

global $conn;
$id_users_login = $_SESSION['id_users'];

// Ambil data user beserta id_kasir dan shift
$query = "SELECT u.*, k.id_kasir, k.shift 
          FROM users u 
          JOIN kasir k ON u.id_users = k.id_users 
          WHERE u.id_users = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $id_users_login);
$stmt->execute();
$data_login = $stmt->get_result()->fetch_assoc();

// Jika bukan kasir, tendang keluar
if (!$data_login) {
    header("Location: index.php");
    exit();
}

// 2. Inisialisasi Objek Kasir menggunakan data asli dari database
$kasir = new Kasir(
    $data_login['id_users'], 
    $data_login['username'], 
    $data_login['password'], 
    $data_login['nama'], 
    $data_login['alamat'], 
    $data_login['jenis_kelamin'], 
    $data_login['foto_profil'], 
    $data_login['id_kasir'], 
    $data_login['shift']
);

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $id_produk = $_POST['id_produk'];
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    
    // Check if exists
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id_produk'] == $id_produk) {
            $item['jumlah']++;
            $item['subtotal'] = $item['jumlah'] * $item['harga_item'];
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'id_produk' => $id_produk,
            'nama_produk' => $nama_produk,
            'harga_item' => $harga,
            'jumlah' => 1,
            'catatan' => '',
            'subtotal' => $harga
        ];
    }
    header("Location: kasir.php");
    exit();
}

// Handle Clear Cart
if (isset($_POST['clear_cart'])) {
    unset($_SESSION['cart']);
    header("Location: kasir.php");
    exit();
}

// Handle Checkout
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    $totalPesanan = array_sum(array_column($_SESSION['cart'], 'subtotal'));
    
    $dataPesanan = [
        'nama_customer' => $_POST['nama_customer'] ?: 'Pelanggan Umum',
        'total_pesanan' => $totalPesanan,
        'items' => $_SESSION['cart']
    ];
    
    try {
        $kasir->buatPesanan($dataPesanan);
        unset($_SESSION['cart']);
        $success_msg = "Pesanan berhasil dibuat!";
    } catch (Exception $e) {
        $error_msg = "Gagal checkout: " . $e->getMessage();
    }
}

// Ambil semua menu aktif dari DB
$dummyMenu = new Menu(0,'','',0,0,'','');
$menus = [];
try {
    $menus = $dummyMenu->tampilkanMenu();
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalCart = array_sum(array_column($cart, 'subtotal'));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir POS - Oak Coffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
<body class="bg-brand-offwhite h-screen flex overflow-hidden text-brand-black">

    <!-- Sidebar / Menu Kategori -->
    <aside class="w-24 bg-brand-black shadow-xl flex-hidden flex-col items-center py-6 md:flex border-r border-white/10 z-20">
        <div class="w-12 h-12 bg-brand-red rounded-xl flex items-center justify-center text-white font-bold text-xl mb-8 shadow-[0_4px_14px_rgba(230,57,70,0.4)]">
            <i class="fa-solid fa-mug-hot"></i>
        </div>
        <div class="flex flex-col gap-4 w-full px-4">
            <button class="w-full aspect-square bg-brand-red/20 text-brand-red rounded-2xl flex flex-col items-center justify-center gap-1 transition-transform hover:scale-105 border border-brand-red/30">
                <i class="fa-solid fa-border-all text-xl"></i>
                <span class="text-[10px] font-bold uppercase tracking-wider mt-1">Semua</span>
            </button>
            <button class="w-full aspect-square text-gray-400 hover:bg-white/10 hover:text-white rounded-2xl flex flex-col items-center justify-center gap-1 transition-transform hover:scale-105">
                <i class="fa-solid fa-burger text-xl"></i>
                <span class="text-[10px] font-bold uppercase tracking-wider mt-1">Makanan</span>
            </button>
            <button class="w-full aspect-square text-gray-400 hover:bg-white/10 hover:text-white rounded-2xl flex flex-col items-center justify-center gap-1 transition-transform hover:scale-105">
                <i class="fa-solid fa-glass-water text-xl"></i>
                <span class="text-[10px] font-bold uppercase tracking-wider mt-1">Minuman</span>
            </button>
        </div>
        <a href="logout.php" class="mt-auto w-12 h-12 bg-white/5 text-gray-400 rounded-xl flex items-center justify-center hover:bg-brand-red hover:text-white transition-colors">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
    </aside>

    <!-- Menu List -->
    <main class="flex-1 flex flex-col bg-brand-offwhite p-6 overflow-y-auto relative">
        <header class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-brand-black tracking-tight">Oak POS</h1>
                <p class="text-sm text-brand-gray mt-1">Pilih menu untuk pesanan pelanggan</p>
            </div>
            <div class="relative">
                <input type="text" placeholder="Cari menu..." class="pl-10 pr-4 py-3 bg-white border border-black/5 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-red shadow-sm w-64">
                <i class="fa-solid fa-magnifying-glass text-brand-gray absolute left-4 top-4"></i>
            </div>
        </header>

        <?php if(isset($success_msg)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg font-semibold shadow-sm">
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error_msg)): ?>
            <div class="bg-red-100 border-l-4 border-brand-red text-brand-darkred p-4 mb-6 rounded-lg font-semibold shadow-sm">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if(empty($menus)): ?>
                <div class="col-span-full p-10 text-center text-brand-gray border-2 border-dashed border-black/10 rounded-2xl">
                    Belum ada menu tersedia. Tambahkan melalui halaman Admin.
                </div>
            <?php endif; ?>

            <?php foreach($menus as $m): ?>
            <form method="POST" action="">
                <input type="hidden" name="add_to_cart" value="1">
                <input type="hidden" name="id_produk" value="<?php echo htmlspecialchars($m['id_produk'] ?? 0); ?>">
                <input type="hidden" name="nama_produk" value="<?php echo htmlspecialchars($m['nama_produk']); ?>">
                <input type="hidden" name="harga" value="<?php echo htmlspecialchars($m['harga']); ?>">
                
                <button type="submit" class="w-full text-left bg-white rounded-2xl shadow-sm border border-black/5 overflow-hidden hover:-translate-y-1 hover:shadow-xl hover:border-brand-red/30 transition-all group relative">
                    <div class="h-40 overflow-hidden relative bg-gray-100">
                        <img src="<?php echo htmlspecialchars($m['foto_produk']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" onerror="this.src='https://via.placeholder.com/300'">
                        <div class="absolute top-3 right-3 bg-brand-black/80 backdrop-blur text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                            <?php echo htmlspecialchars($m['kategori']); ?>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-extrabold text-brand-black text-lg leading-tight mb-1"><?php echo htmlspecialchars($m['nama_produk']); ?></h3>
                        <p class="text-brand-red font-black text-xl">Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></p>
                    </div>
                    <div class="absolute inset-0 bg-brand-red/0 group-hover:bg-brand-red/5 transition-colors pointer-events-none"></div>
                </button>
            </form>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Cart Sidebar -->
    <aside class="w-96 bg-white shadow-2xl flex flex-col z-30 border-l border-black/5">
        <div class="p-6 border-b border-black/5 flex items-center justify-between bg-brand-black text-white">
            <h2 class="text-xl font-extrabold tracking-tight">Pesanan Saat Ini</h2>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-bold"><?php echo htmlspecialchars($kasir->getNama()); ?></p>
                    <p class="text-[10px] text-white/50 uppercase tracking-widest">Shift <?php echo htmlspecialchars($kasir->getShift()); ?></p>
                </div>
                <!-- Menggunakan foto dari database -->
                <img src="<?php echo htmlspecialchars($kasir->getFotoProfile() ?: 'https://i.pravatar.cc/150?img=5'); ?>" class="w-10 h-10 rounded-full border border-white/20 object-cover" onerror="this.src='https://i.pravatar.cc/150?img=5'">
            </div>
        </div>

        <!-- Items -->
        <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-4 bg-brand-offwhite">
            <?php if(empty($cart)): ?>
                <div class="flex flex-col items-center justify-center h-full text-brand-gray opacity-50">
                    <i class="fa-solid fa-basket-shopping text-6xl mb-4"></i>
                    <p class="font-semibold">Keranjang Kosong</p>
                </div>
            <?php else: ?>
                <?php foreach($cart as $idx => $item): ?>
                <div class="flex flex-col bg-white border border-black/5 p-4 rounded-2xl shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-bold text-brand-black leading-tight flex-1 pr-4"><?php echo htmlspecialchars($item['nama_produk']); ?></h4>
                        <p class="text-brand-black font-black">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>
                    </div>
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-sm text-brand-gray font-medium">Rp <?php echo number_format($item['harga_item'], 0, ',', '.'); ?> / item</p>
                        <div class="flex items-center gap-3 bg-brand-offwhite rounded-lg px-2 py-1 border border-black/5">
                            <span class="text-sm font-bold w-6 text-center text-brand-red"><?php echo $item['jumlah']; ?>x</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Summary -->
        <div class="p-6 bg-white border-t border-black/5 shadow-[0_-10px_30px_rgba(0,0,0,0.03)]">
            <div class="flex justify-between items-center mb-6 text-lg">
                <span class="text-brand-gray font-bold">Total Pembayaran</span>
                <span class="text-3xl font-black text-brand-red tracking-tight">Rp <?php echo number_format($totalCart, 0, ',', '.'); ?></span>
            </div>
            
            <form method="POST" action="" class="flex flex-col gap-3">
                <input type="text" name="nama_customer" placeholder="Nama Pelanggan (Opsional)" class="w-full px-4 py-3 bg-brand-offwhite border border-black/5 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-red focus:bg-white transition-colors font-medium">
                
                <div class="flex gap-2 mt-2">
                    <button type="submit" name="clear_cart" class="px-5 py-4 bg-brand-offwhite text-brand-black font-bold rounded-xl hover:bg-gray-200 transition-colors border border-black/5">
                        <i class="fa-solid fa-trash text-brand-gray"></i>
                    </button>
                    <button type="submit" name="checkout" <?php if(empty($cart)) echo 'disabled'; ?> class="flex-1 bg-brand-red hover:bg-brand-darkred disabled:bg-brand-gray/30 disabled:cursor-not-allowed text-white py-4 rounded-xl font-bold text-lg shadow-[0_4px_14px_rgba(230,57,70,0.4)] transition-all flex justify-center items-center gap-2">
                        <i class="fa-solid fa-check-to-slot"></i>
                        Proses Transaksi
                    </button>
                </div>
            </form>
        </div>
    </aside>

</body>
</html>
