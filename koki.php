<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Koki.php';

// 1. Cek Sesi Login & Role Koki
if (!isset($_SESSION['id_users'])) {
    header("Location: login.php");
    exit();
}

global $conn;
$id_users_login = $_SESSION['id_users'];

// Ambil data user beserta id_koki dan shift
$query = "SELECT u.*, k.id_koki, k.shift 
          FROM users u 
          JOIN koki k ON u.id_users = k.id_users 
          WHERE u.id_users = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $id_users_login);
$stmt->execute();
$data_login = $stmt->get_result()->fetch_assoc();

// Jika bukan koki, tendang keluar
if (!$data_login) {
    header("Location: index.php");
    exit();
}

// 2. Inisialisasi Objek Koki menggunakan data asli dari database
$koki = new Koki(
    $data_login['id_users'], 
    $data_login['username'], 
    $data_login['password'], 
    $data_login['nama'], 
    $data_login['alamat'], 
    $data_login['jenis_kelamin'], 
    $data_login['foto_profil'], 
    $data_login['id_koki'], 
    $data_login['shift']
);

// Update Status Pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id_pesanan = $_POST['id_pesanan'];
    $status_baru = $_POST['status_baru'];
    $koki->manajemenStatusPesanan($id_pesanan, $status_baru);
    header("Location: koki.php");
    exit();
}

// Fetch active orders (Menunggu / Dimasak)
$pesananAktif = [];
try {
    if ($conn) {
        $result = $conn->query("SELECT * FROM pesanan WHERE status_pesanan IN ('Menunggu', 'Dimasak') ORDER BY waktu_pesanan ASC");
        
        if ($result) {
            $pesananAktif = $result->fetch_all(MYSQLI_ASSOC);

            // Fetch items for each order
            foreach ($pesananAktif as &$p) {
                $stmtItems = $conn->prepare("
                    SELECT ip.*, m.nama_produk 
                    FROM item_pesanan ip
                    LEFT JOIN menu m ON ip.id_produk = m.id_produk
                    WHERE ip.id_pesanan = ?
                ");
                $stmtItems->bind_param("i", $p['id_pesanan']);
                $stmtItems->execute();
                $p['items'] = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
            }
        }
    }
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display - Oak Coffee POS</title>
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
<body class="bg-brand-black h-screen flex flex-col overflow-hidden text-white font-sans">

    <!-- Header KDS -->
    <header class="bg-[#1a1a1a] p-5 flex justify-between items-center border-b border-white/10 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-brand-red rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-[0_4px_14px_rgba(230,57,70,0.4)]">
                <i class="fa-solid fa-fire-burner"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight">Kitchen Display System</h1>
                <p class="text-xs text-brand-red font-bold uppercase tracking-widest mt-0.5">Oak Coffee Live Orders</p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-bold"><?php echo htmlspecialchars($koki->getNama()); ?></p>
                    <p class="text-[10px] text-white/50 uppercase tracking-widest">Shift <?php echo htmlspecialchars($koki->getShift()); ?></p>
                </div>
                <!-- Menggunakan foto dari database -->
                <img src="<?php echo htmlspecialchars($koki->getFotoProfile() ?: 'https://i.pravatar.cc/150?img=12'); ?>" class="w-10 h-10 rounded-full border border-white/20 object-cover" onerror="this.src='https://i.pravatar.cc/150?img=12'">
            </div>
            <div class="h-8 w-px bg-white/20"></div>
            <a href="logout.php" class="text-white/50 hover:text-brand-red transition-colors flex items-center gap-2 text-sm font-bold">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
            </a>
        </div>
    </header>

    <!-- Main Board -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-6">
        
        <?php if(isset($error_msg)): ?>
            <div class="bg-red-500/10 border border-brand-red text-red-200 p-4 mb-6 rounded-xl font-semibold backdrop-blur-sm max-w-2xl mx-auto">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> Error Database: <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div class="flex gap-6 h-full items-start w-max min-w-full">
            
            <?php if(empty($pesananAktif) && !isset($error_msg)): ?>
                <div class="flex-1 flex flex-col items-center justify-center h-full w-full opacity-30 mt-20">
                    <i class="fa-solid fa-mug-hot text-8xl mb-6"></i>
                    <h2 class="text-3xl font-extrabold tracking-tight">Dapur Santai</h2>
                    <p class="mt-2 text-lg">Belum ada pesanan yang masuk saat ini.</p>
                </div>
            <?php endif; ?>

            <?php foreach($pesananAktif as $p): ?>
                <?php 
                    $isMenunggu = $p['status_pesanan'] == 'Menunggu';
                    $borderColor = $isMenunggu ? 'border-brand-red' : 'border-yellow-500';
                    $headerColor = $isMenunggu ? 'bg-brand-red' : 'bg-yellow-500';
                ?>
                <!-- Ticket Card -->
                <div class="w-80 bg-[#1e1e1e] rounded-2xl flex flex-col border border-white/10 overflow-hidden shadow-2xl flex-shrink-0 relative h-full max-h-[85vh]">
                    
                    <div class="absolute top-0 left-0 w-full h-1 <?php echo $headerColor; ?>"></div>
                    
                    <!-- Header Card -->
                    <div class="p-5 border-b border-white/10 bg-white/5">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-black text-2xl tracking-tight">#<?php echo htmlspecialchars($p['id_pesanan']); ?></h3>
                            <span class="text-xs font-bold px-2 py-1 bg-white/10 rounded-md tracking-wider">
                                <?php echo date('H:i', strtotime($p['waktu_pesanan'])); ?>
                            </span>
                        </div>
                        <p class="font-bold text-white/80"><i class="fa-solid fa-user-tag mr-2 text-brand-red"></i><?php echo htmlspecialchars($p['nama_customer']); ?></p>
                        
                        <div class="mt-3 inline-block px-3 py-1 rounded-full text-xs font-bold border <?php echo $isMenunggu ? 'border-brand-red text-brand-red bg-brand-red/10' : 'border-yellow-500 text-yellow-500 bg-yellow-500/10'; ?>">
                            <i class="fa-solid <?php echo $isMenunggu ? 'fa-hourglass-start' : 'fa-fire-burner'; ?> mr-1"></i>
                            <?php echo htmlspecialchars($p['status_pesanan']); ?>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="p-5 flex-1 overflow-y-auto custom-scrollbar">
                        <ul class="space-y-4">
                            <?php if(!empty($p['items'])): ?>
                                <?php foreach($p['items'] as $item): ?>
                                    <li class="flex items-start gap-3 bg-[#2a2a2a] p-3 rounded-xl border border-white/5">
                                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center font-black text-brand-red flex-shrink-0">
                                            <?php echo htmlspecialchars($item['jumlah']); ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-lg leading-tight mb-1"><?php echo htmlspecialchars($item['nama_produk'] ?? 'Menu #'.$item['id_produk']); ?></p>
                                            <?php if(!empty($item['catatan'])): ?>
                                                <p class="text-sm text-yellow-500 bg-yellow-500/10 px-2 py-1 rounded-md inline-block mt-1">
                                                    <i class="fa-solid fa-comment-dots mr-1"></i> <?php echo htmlspecialchars($item['catatan']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-white/30 text-center py-4 italic text-sm">Tidak ada detail item. (Gunakan ID Produk jika join gagal)</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Actions -->
                    <div class="p-4 border-t border-white/10 bg-[#1a1a1a]">
                        <form method="POST" action="">
                            <input type="hidden" name="id_pesanan" value="<?php echo $p['id_pesanan']; ?>">
                            
                            <?php if ($isMenunggu): ?>
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="status_baru" value="Dimasak">
                                <button type="submit" class="w-full py-4 rounded-xl font-extrabold text-brand-black bg-yellow-400 hover:bg-yellow-300 transition-colors shadow-[0_0_15px_rgba(250,204,21,0.2)]">
                                    <i class="fa-solid fa-fire mr-2"></i> Mulai Masak
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="status_baru" value="Selesai">
                                <button type="submit" class="w-full py-4 rounded-xl font-extrabold text-white bg-green-500 hover:bg-green-400 transition-colors shadow-[0_0_15px_rgba(34,197,94,0.3)]">
                                    <i class="fa-solid fa-check-double mr-2"></i> Pesanan Selesai
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>
    </main>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
</body>
</html>
