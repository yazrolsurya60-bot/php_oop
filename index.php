<?php
if (isset($_POST['login']))

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Oak Coffee | POS Canggih untuk Kafe</title>
    <meta name="description"
        content="POS Oak Coffee menghadirkan pesanan yang mulus, analitik yang jelas, dan efisiensi untuk kafe Anda. Sistem point of sale terbaik untuk kedai kopi modern.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#E63946',
                            darkred: '#D62828',
                            lightred: '#FF4D6D',
                            black: '#111111',
                            gray: '#333333',
                            offwhite: '#F8F9FA'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        html {
            scroll-behavior: smooth;
        }

        .hero-image-transform {
            transform: perspective(1000px) rotateY(-5deg);
        }

        .hero-image-transform:hover {
            transform: perspective(1000px) rotateY(0deg) scale(1.02);
        }
    </style>
</head>

<body class="bg-white text-brand-black font-sans antialiased overflow-x-hidden">

    <!-- Header -->
    <header class="fixed top-0 left-0 w-full bg-white/95 backdrop-blur-md z-50 border-b border-black/5 py-5">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="logo">
                <img src="images/Oak_Coffe.png" alt="Logo" class="h-10 w-auto">
            </div>
            <nav class="hidden md:flex gap-10">
                <a href="#features"
                    class="font-semibold text-brand-black relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-brand-red after:transition-all hover:after:w-full">Fitur</a>
                <a href="#about"
                    class="font-semibold text-brand-black relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-brand-red after:transition-all hover:after:w-full">Tentang</a>
                <a href="#workflow"
                    class="font-semibold text-brand-black relative after:content-[''] after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-brand-red after:transition-all hover:after:w-full">Alur
                    Kerja</a>
            </nav>
            <div class="nav-action">
                <a href="login.php"
                    class="inline-flex cursor-pointer items-center justify-center px-7 py-3.5 bg-brand-red text-white font-semibold rounded-lg shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(230,57,70,0.5)] transition-all duration-300">Login</a>
            </div>
        </div>
    </header>

    <!-- 1. Hero Section -->
    <section class="pt-40 pb-20 min-h-screen flex items-center" id="hero">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div>
                <h1 class="text-4xl md:text-5xl lg:text-[4.5rem] font-extrabold tracking-tight leading-tight mb-6">
                    Kelola Bisnis Kopi Anda ke<span class="text-brand-red"> Level Selanjutnya</span></h1>
                <p class="text-lg md:text-xl text-brand-gray mb-10 max-w-2xl">Sistem cerdas yang mengintegrasikan
                    manajemen bahan baku secara real-time dengan sinkronisasi instan ke dapur. Pastikan setiap pesanan
                    tersaji cepat tanpa kendala stok habis.</p>
                <div class="flex flex-wrap gap-4">
                    <a href="tampilan_awal.php"
                        class="inline-flex items-center justify-center px-9 py-4 bg-brand-red text-white text-lg font-semibold rounded-lg shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(230,57,70,0.5)] transition-all duration-300">Demo
                        Tampilan</a>
                    <a href="#features"
                        class="inline-flex items-center justify-center px-9 py-4 border-2 border-brand-black text-brand-black text-lg font-semibold rounded-lg hover:bg-brand-black hover:text-white transition-all duration-300">Jelajahi
                        Fitur</a>
                </div>
            </div>
            <div class="relative">
                <!-- Red decorative shape -->
                <div
                    class="absolute w-96 h-96 top-1/2 right-0 translate-x-1/4 -translate-y-1/2 bg-brand-red rounded-full blur-[80px] opacity-15 -z-10">
                </div>
                <img src="images/hero_pos.png" alt="POS Oak Coffee Terminal"
                    class="w-full rounded-2xl shadow-2xl border border-black/5 hero-image-transform transition-all duration-500">
            </div>
        </div>
    </section>

    <!-- 2. About Us Section -->
    <section class="py-32 bg-brand-offwhite" id="about">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
            <div class="order-2 lg:order-1 relative">
                <div class="absolute w-72 h-72 top-1/4 -left-20 bg-brand-red rounded-full blur-[80px] opacity-15 -z-10">
                </div>
                <img src="images/about_barista.png" alt="Barista using POS Oak Coffee"
                    class="w-full h-auto rounded-2xl shadow-2xl">
            </div>
            <div class="order-1 lg:order-2">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight mb-6">Layanan <span
                        class="text-brand-red">Tanpa Hambatan</span></h2>
                <p class="text-lg text-brand-gray mb-6 leading-relaxed">
                    Dalam lingkungan kedai kopi yang serba cepat, setiap detik sangat berarti. POS Oak Coffee dirancang
                    untuk mempercepat waktu pembayaran dengan menghilangkan langkah-langkah yang tidak perlu. Dengan
                    antarmuka yang intuitif, barista Anda dapat mengetuk, menggesek, dan melayani lebih cepat dari
                    sebelumnya. Kami menangani kerumitannya sehingga Anda dapat fokus pada seduhan yang sempurna.
                </p>
                <ul class="space-y-4 mt-8">
                    <li class="text-lg font-semibold flex items-center gap-3"><i
                            class="fa-solid fa-check text-brand-red text-xl"></i> Antarmuka sentuh intuitif</li>
                    <li class="text-lg font-semibold flex items-center gap-3"><i
                            class="fa-solid fa-check text-brand-red text-xl"></i> Pemrosesan transaksi secepat kilat
                    </li>
                    <li class="text-lg font-semibold flex items-center gap-3"><i
                            class="fa-solid fa-check text-brand-red text-xl"></i> Manajemen tip terintegrasi</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- 3. Features Section -->
    <section class="py-32 bg-white" id="features">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold tracking-tight mb-6">Semua yang Anda Butuhkan
                    untuk <span class="text-brand-red">Berkembang</span></h2>
                <p class="text-lg text-brand-gray">Alat canggih yang dirancang khusus untuk kafe modern.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-16">
                <!-- Card 1 -->
                <div
                    class="bg-white p-10 rounded-3xl shadow-[0_10px_40px_rgba(17,17,17,0.08)] border border-black/5 hover:-translate-y-2.5 hover:shadow-[0_20px_50px_rgba(17,17,17,0.15)] hover:border-brand-red/20 transition-all duration-300">
                    <div class="w-20 h-20 rounded-2xl bg-brand-red/5 flex items-center justify-center mb-6 p-4">
                        <img src="images/icon_inventory.png" alt="Inventory Management Icon"
                            class="w-full h-full object-contain">
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Manajemen Inventaris</h3>
                    <p class="text-brand-gray leading-relaxed">Lacak biji kopi, susu, dan sirup Anda secara real-time.
                        Dapatkan peringatan stok menipis sebelum Anda kehabisan saat jam sibuk.</p>
                </div>
                <!-- Card 2 -->
                <div
                    class="bg-white p-10 rounded-3xl shadow-[0_10px_40px_rgba(17,17,17,0.08)] border border-black/5 hover:-translate-y-2.5 hover:shadow-[0_20px_50px_rgba(17,17,17,0.15)] hover:border-brand-red/20 transition-all duration-300">
                    <div class="w-20 h-20 rounded-2xl bg-brand-red/5 flex items-center justify-center mb-6 p-4">
                        <img src="images/icon_report.png" alt="Pelaporan Icon" class="w-full h-full object-contain">
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Pelaporan Mudah</h3>
                    <p class="text-brand-gray leading-relaxed">Pantau performa bisnis Anda melalui laporan penjualan
                        otomatis yang akurat. Analisis data transaksi harian hingga bulanan dengan visualisasi yang
                        sangat mudah dipahami.</p>
                </div>
                <!-- Card 3 -->
                <div
                    class="bg-white p-10 rounded-3xl shadow-[0_10px_40px_rgba(17,17,17,0.08)] border border-black/5 hover:-translate-y-2.5 hover:shadow-[0_20px_50px_rgba(17,17,17,0.15)] hover:border-brand-red/20 transition-all duration-300">
                    <div class="w-20 h-20 rounded-2xl bg-brand-red/5 flex items-center justify-center mb-6 p-4">
                        <img src="images/icon_integration.png" alt="Sistem Terintegrasi Icon"
                            class="w-full h-full object-contain">
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Sistem Terintegrasi</h3>
                    <p class="text-brand-gray leading-relaxed">Hubungkan seluruh aspek operasional cafe Anda—dari
                        manajemen stok, transaksi kasir, hingga laporan keuangan—dalam satu ekosistem yang saling
                        sinkron secara otomatis.</p>
                </div>
                <!-- Card 4 -->
                <div
                    class="bg-white p-10 rounded-3xl shadow-[0_10px_40px_rgba(17,17,17,0.08)] border border-black/5 hover:-translate-y-2.5 hover:shadow-[0_20px_50px_rgba(17,17,17,0.15)] hover:border-brand-red/20 transition-all duration-300">
                    <div class="w-20 h-20 rounded-2xl bg-brand-red/5 flex items-center justify-center mb-6 p-4">
                        <img src="images/icon_kitchen.png" alt="Manajemen Dapur Icon"
                            class="w-full h-full object-contain">
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Manajemen Dapur</h3>
                    <p class="text-brand-gray leading-relaxed">Percepat alur pesanan dari kasir langsung ke layar dapur
                        secara real-time. Kurangi kesalahan komunikasi dan pastikan setiap hidangan disajikan tepat
                        waktu kepada pelanggan.</p>
                </div>
                <!-- Card 5 -->
                <div
                    class="bg-white p-10 rounded-3xl shadow-[0_10px_40px_rgba(17,17,17,0.08)] border border-black/5 hover:-translate-y-2.5 hover:shadow-[0_20px_50px_rgba(17,17,17,0.15)] hover:border-brand-red/20 transition-all duration-300">
                    <div class="w-20 h-20 rounded-2xl bg-brand-red/5 flex items-center justify-center mb-6 p-4">
                        <img src="images/icon_product.png" alt="Manajemen Produk Icon"
                            class="w-full h-full object-contain">
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Manajemen Produk</h3>
                    <p class="text-brand-gray leading-relaxed">Atur katalog menu Anda dengan mudah, mulai dari kategori,
                        varian rasa, hingga penyesuaian harga. Kelola ribuan SKU produk dalam satu dashboard yang
                        intuitif dan rapi.</p>
                </div>
                <!-- Card 6 -->
                <div
                    class="bg-white p-10 rounded-3xl shadow-[0_10px_40px_rgba(17,17,17,0.08)] border border-black/5 hover:-translate-y-2.5 hover:shadow-[0_20px_50px_rgba(17,17,17,0.15)] hover:border-brand-red/20 transition-all duration-300">
                    <div class="w-20 h-20 rounded-2xl bg-brand-red/5 flex items-center justify-center mb-6 p-4">
                        <img src="images/icon_access.png" alt="Aksesibilitas Icon" class="w-full h-full object-contain">
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Aksesibilitas</h3>
                    <p class="text-brand-gray leading-relaxed">Sebagai sistem berbasis web, Oak Coffee dapat diakses
                        kapan saja dan melalui perangkat apa pun oleh admin, kasir, maupun koki tanpa perlu instalasi
                        rumit.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Stats Section -->
    <section class="py-20 bg-brand-offwhite border-y border-black/5" id="stats">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 text-center">
            <!-- Stat 1: Jeda Sinkronisasi Dapur -->
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-brand-red/10 flex items-center justify-center mb-5">
                    <i class="fa-solid fa-bolt text-3xl text-brand-red"></i>
                </div>
                <div class="text-5xl font-extrabold text-brand-black tracking-tight mb-2 leading-none">0 Detik</div>
                <div class="text-xs font-bold text-brand-gray uppercase tracking-widest mt-1 leading-tight">JEDA
                    SINKRONISASI DAPUR</div>
            </div>
            <!-- Stat 2: Kapasitas Menu & Transaksi -->
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-brand-red/10 flex items-center justify-center mb-5">
                    <i class="fa-solid fa-database text-3xl text-brand-red"></i>
                </div>
                <div class="text-5xl font-extrabold text-brand-black tracking-tight mb-2 leading-none">∞</div>
                <div class="text-xs font-bold text-brand-gray uppercase tracking-widest mt-1 leading-tight">KAPASITAS
                    MENU & TRANSAKSI</div>
            </div>
            <!-- Stat 3: Akurasi Pencatatan Stok -->
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-brand-red/10 flex items-center justify-center mb-5">
                    <i class="fa-solid fa-bullseye text-3xl text-brand-red"></i>
                </div>
                <div class="text-5xl font-extrabold text-brand-black tracking-tight mb-2 leading-none">100%</div>
                <div class="text-xs font-bold text-brand-gray uppercase tracking-widest mt-1 leading-tight">AKURASI
                    PENCATATAN STOK</div>
            </div>
            <!-- Stat 4: Akses Pantauan Owner -->
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-brand-red/10 flex items-center justify-center mb-5">
                    <i class="fa-solid fa-mobile-screen-button text-3xl text-brand-red"></i>
                </div>
                <div class="text-5xl font-extrabold text-brand-black tracking-tight mb-2 leading-none">24/7</div>
                <div class="text-xs font-bold text-brand-gray uppercase tracking-widest mt-1 leading-tight">AKSES
                    PANTAUAN OWNER</div>
            </div>
        </div>
    </section>

    <!-- 4. Workflow Section -->
    <section class="py-32 bg-white" id="workflow">
        <div class="max-w-7xl mx-auto px-6">
            <div
                class="flex flex-col lg:flex-row bg-white rounded-3xl shadow-[0_20px_50px_rgba(17,17,17,0.15)] overflow-hidden max-w-5xl mx-auto border border-black/5">
                <div class="lg:w-2/5">
                    <img src="images/testimonial_owner.png" alt="Alur Kerja POS Oak Coffee"
                        class="w-full h-full object-cover">
                </div>
                <div class="lg:w-3/5 p-12 lg:p-16 flex flex-col justify-center">
                    <div
                        class="inline-flex items-center gap-2 bg-brand-red/10 text-brand-red text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-6 w-fit">
                        <i class="fa-solid fa-arrows-spin"></i>
                        <span>Alur Kerja Sistem</span>
                    </div>
                    <h2 class="text-3xl lg:text-4xl font-extrabold text-brand-black tracking-tight mb-6 leading-tight">
                        Satu Sistem, <span class="text-brand-red">Tim yang Solid.</span>
                    </h2>
                    <p class="text-lg text-brand-gray leading-relaxed">
                        Setiap pesanan yang dimasukkan di kasir akan langsung tampil di layar dapur dalam hitungan
                        detik. Tidak ada lagi kertas pesanan yang hilang atau miskomunikasi. Pastikan selalu mengecek
                        layar Anda agar hidangan tersaji tepat waktu.
                    </p>
                    <div class="mt-8 flex items-center gap-3 text-sm font-semibold text-brand-red">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Real-time &mdash; tanpa jeda, tanpa hambatan</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Bottom Call to Action -->
    <section class="py-32 bg-brand-offwhite" id="cta">
        <div class="max-w-7xl mx-auto px-6">
            <div
                class="bg-brand-black rounded-3xl grid grid-cols-1 lg:grid-cols-2 items-stretch relative overflow-hidden shadow-[0_20px_50px_rgba(17,17,17,0.15)]">
                <!-- Left: Text Content -->
                <div class="relative z-10 text-center lg:text-left p-10 lg:p-16 flex flex-col justify-center">
                    <div
                        class="inline-flex items-center gap-2 bg-white/10 text-white text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-6 w-fit mx-auto lg:mx-0">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>Multi-Device Access</span>
                    </div>
                    <h2 class="text-white text-4xl lg:text-5xl font-extrabold mb-6 leading-tight">Kelola Oak Coffee dari
                        <span class="text-brand-red">Perangkat Mana Saja.</span>
                    </h2>
                    <p class="text-white/80 text-lg mb-10 max-w-md mx-auto lg:mx-0 leading-relaxed">Tanpa instalasi
                        rumit. Cukup login melalui browser di tablet, smartphone, atau laptop Anda untuk akses penuh ke
                        seluruh fitur integrasi kami.</p>
                    <a href="?page=login"
                        class="inline-flex items-center gap-3 justify-center lg:justify-start px-9 py-4 bg-brand-red text-white text-lg font-semibold rounded-lg shadow-[0_4px_14px_rgba(230,57,70,0.4)] hover:bg-brand-darkred hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(230,57,70,0.5)] transition-all duration-300 w-fit mx-auto lg:mx-0">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        Akses Sistem Sekarang
                    </a>
                </div>
                <!-- Right: Image flush to right edge -->
                <div class="hidden lg:flex items-center justify-end overflow-hidden bg-brand-black/50">
                    <img src="images/cta_isometric.png" alt="Modular POS System"
                        class="w-full h-full object-contain object-right scale-105 pr-0">
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Footer -->
    <footer class="bg-white pt-20 pb-10 border-t border-black/5">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 mb-16">
                <div class="lg:col-span-5">
                    <div class="mb-6">
                        <img src="images/Oak_Coffe.png" alt="POS Oak Coffee Logo" class="h-10 w-auto">
                    </div>
                    <p class="text-brand-gray mb-8 max-w-sm">Solusi point of sale terbaik untuk kedai kopi dan kafe
                        modern.</p>
                    <div class="flex gap-4">
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-brand-offwhite flex items-center justify-center text-xl text-brand-black hover:bg-brand-red hover:text-white transition-all"><i
                                class="fa-brands fa-instagram"></i></a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-brand-offwhite flex items-center justify-center text-xl text-brand-black hover:bg-brand-red hover:text-white transition-all"><i
                                class="fa-brands fa-twitter"></i></a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-brand-offwhite flex items-center justify-center text-xl text-brand-black hover:bg-brand-red hover:text-white transition-all"><i
                                class="fa-brands fa-facebook"></i></a>
                    </div>
                </div>

                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-8">
                    <div>
                        <h4 class="text-lg font-bold mb-6">Produk</h4>
                        <ul class="space-y-3">
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Fitur</a>
                            </li>
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Perangkat
                                    Keras</a></li>
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Harga</a>
                            </li>
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Integrasi</a>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-6">Sumber Daya</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Pusat
                                    Bantuan</a></li>
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Blog</a></li>
                            <li><a href="#" class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Studi
                                    Kasus</a></li>
                            <li><a href="#" class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">API
                                    Pengembang</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-6">Perusahaan</h4>
                        <ul class="space-y-3">
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Tentang
                                    Kami</a></li>
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Karir</a>
                            </li>
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Kontak</a>
                            </li>
                            <li><a href="#"
                                    class="text-brand-gray hover:text-brand-red hover:pl-1 transition-all">Legal</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="text-center pt-8 border-t border-black/5 text-brand-gray text-sm">
                <p>&copy; 2026 POS Oak Coffee. Hak cipta dilindungi undang-undang.</p>
            </div>
        </div>
    </footer>
</body>

</html>