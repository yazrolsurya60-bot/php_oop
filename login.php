<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/User.php';

// Jika sudah login, cek role dan lempar ke halaman yang sesuai
if (isset($_SESSION['id_users'])) {
    $db = new Database();
    $conn = $db->getConn();
    $id_users = $_SESSION['id_users'];

    // Cek Admin
    $q_admin = $conn->query("SELECT id_admin FROM admin WHERE id_users = '$id_users'");
    if ($q_admin && $q_admin->num_rows > 0) {
        header("Location: admin.php");
        exit;
    }
    // Cek Kasir
    $q_kasir = $conn->query("SELECT id_kasir FROM kasir WHERE id_users = '$id_users'");
    if ($q_kasir && $q_kasir->num_rows > 0) {
        header("Location: kasir.php");
        exit;
    }
    // Cek Koki
    $q_koki = $conn->query("SELECT id_koki FROM koki WHERE id_users = '$id_users'");
    if ($q_koki && $q_koki->num_rows > 0) {
        header("Location: koki.php");
        exit;
    }
}

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Instansiasi User kosong untuk memanggil fungsi login
    $auth = new User('', '', '', '', '', '', '');

    if ($auth->login($username, $password)) {
        // Jika login berhasil, kita cari tahu dia role-nya apa (di tabel admin/kasir/koki)
        $db = new Database();
        $conn = $db->getConn();
        $id_users = $_SESSION['id_users'];

        $role_found = false;

        // Cek Admin
        $q_admin = $conn->query("SELECT id_admin FROM admin WHERE id_users = '$id_users'");
        if ($q_admin && $q_admin->num_rows > 0) {
            header("Location: admin.php");
            $role_found = true;
            exit;
        }

        // Cek Kasir
        $q_kasir = $conn->query("SELECT id_kasir FROM kasir WHERE id_users = '$id_users'");
        if ($q_kasir && $q_kasir->num_rows > 0) {
            header("Location: kasir.php");
            $role_found = true;
            exit;
        }

        // Cek Koki
        $q_koki = $conn->query("SELECT id_koki FROM koki WHERE id_users = '$id_users'");
        if ($q_koki && $q_koki->num_rows > 0) {
            header("Location: koki.php");
            $role_found = true;
            exit;
        }

        if (!$role_found) {
            $error_msg = "Akun tidak memiliki role (Admin/Kasir/Koki). Silakan hubungi Owner.";
            // Hapus session jika role tidak valid
            session_unset();
            session_destroy();
        }

    } else {
        $error_msg = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Oak Coffee POS</title>
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

<body class="bg-brand-offwhite min-h-screen flex text-brand-black">

    <!-- Kolom Kiri (Visual Branding) -->
    <div class="hidden lg:flex w-1/2 bg-brand-black relative flex-col justify-between p-12 overflow-hidden">
        <!-- Dekorasi Background -->
        <div
            class="absolute top-0 right-0 w-96 h-96 bg-brand-red/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3">
        </div>
        <div
            class="absolute bottom-0 left-0 w-96 h-96 bg-brand-red/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3">
        </div>

        <div class="relative z-10">
            <a href="index.php"
                class="text-white text-2xl font-extrabold flex items-center gap-3 w-fit hover:opacity-80 transition-opacity">
                <div
                    class="w-10 h-10 bg-brand-red rounded-lg flex items-center justify-center text-white text-lg shadow-[0_4px_14px_rgba(230,57,70,0.4)]">
                    <img src="images/Oak_Coffe.png" class="object-cover rounded-lg w-full h-full" alt="Logo Kafe">
                </div>
                Oak Coffee
            </a>
        </div>

        <div class="relative z-10 text-white mt-auto mb-10">
            <h1 class="text-4xl lg:text-5xl font-extrabold mb-6 leading-tight">Sistem POS<br><span
                    class="text-brand-red">Terintegrasi.</span></h1>
            <p class="text-white/70 text-lg max-w-md">Masuk untuk mengelola operasional kafe Anda. Dari manajemen
                pesanan, stok bahan baku, hingga pemantauan pendapatan secara real-time.</p>
        </div>

        <!-- Element visual melingkar mirip biji kopi -->
        <i class="fa-solid fa-seedling absolute -right-20 -bottom-20 text-[30rem] text-white/5 -rotate-12"></i>
    </div>

    <!-- Kolom Kanan (Form Login) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
        <div class="w-full max-w-md">

            <div class="lg:hidden mb-10 flex justify-center">
                <a href="index.php" class="text-brand-black text-3xl font-extrabold flex items-center gap-3 w-fit">
                    <div
                        class="w-12 h-12 bg-brand-red rounded-xl flex items-center justify-center text-white text-xl shadow-[0_4px_14px_rgba(230,57,70,0.4)]">
                        <i class="fa-solid fa-mug-hot"></i>
                    </div>
                    Oak Coffee
                </a>
            </div>

            <div class="text-center lg:text-left mb-10">
                <h2 class="text-3xl font-extrabold mb-2 tracking-tight">Selamat Datang! 👋</h2>
                <p class="text-brand-gray">Silakan masukkan detail akun Anda untuk melanjutkan.</p>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div
                    class="bg-red-50 border-l-4 border-brand-red text-brand-darkred p-4 rounded-lg mb-6 text-sm font-semibold flex items-start gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                    <p><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">

                <div class="space-y-2">
                    <label for="username" class="block text-sm font-bold text-brand-gray">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" name="username" id="username" required
                            class="block w-full pl-11 pr-4 py-3.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red transition-colors shadow-sm"
                            placeholder="Masukkan username Anda">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-sm font-bold text-brand-gray">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                            class="block w-full pl-11 pr-4 py-3.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red transition-colors shadow-sm"
                            placeholder="Masukkan kata sandi">
                    </div>
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 py-4 mt-8 bg-brand-red text-white text-lg font-bold rounded-xl shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(230,57,70,0.5)] transition-all duration-300">
                    Masuk ke Dashboard <i class="fa-solid fa-arrow-right ml-1"></i>
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="index.php"
                    class="text-sm font-semibold text-brand-gray hover:text-brand-red transition-colors flex justify-center items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>

</body>

</html>