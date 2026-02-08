<?php
session_start();

// Redirect if not admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

/**
 * Simulasi Data Admin & Penjualan
 */
$admin_profile = [
    'nama' => 'Chief Admin Luxury',
    'bio' => 'Berpengalaman dalam manajemen e-commerce selama 10 tahun.',
    'alamat' => 'Jakarta, Indonesia'
];

$sales_record = [
    ['user' => 'Andi', 'item' => 'Laptop Z1', 'total' => 15000000, 'date' => '2023-10-01'],
    ['user' => 'Budi', 'item' => 'Headphones', 'total' => 1200000, 'date' => '2023-10-02'],
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-main: #90EE90;
            --secondary-main: #ADD8E6;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --sidebar-width: 260px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: #f0f4f8;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--white) 0%, #f9f9f9 100%);
            position: fixed;
            left: 0;
            top: 0;
            padding: 30px 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            z-index: 100;
        }

        .sidebar-logo {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 50px;
            color: var(--text-dark);
            text-align: center;
        }

        .sidebar-menu {
            list-style: none;
        }

        .menu-item {
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #777;
        }

        .menu-item:hover {
            background: var(--secondary-light);
            color: var(--text-dark);
        }

        .menu-item i {
            font-size: 18px;
            width: 25px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            min-height: 100vh;
        }

        .welcome-card {
            background: linear-gradient(to right, var(--primary-main), var(--secondary-main));
            padding: 40px;
            border-radius: 30px;
            color: var(--text-dark);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-box {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
        }

        .info-box h3 {
            margin-bottom: 15px;
            color: var(--text-dark);
            font-size: 18px;
            border-bottom: 2px solid var(--primary-main);
            display: inline-block;
        }

        /* Modals (Rooms) */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 24px;
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        /* Charts */
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 20px 10px;
            }

            .sidebar-logo,
            .menu-item span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
                padding: 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-logo">ADMIN</div>
        <ul class="sidebar-menu">
            <li class="menu-item" onclick="openModal('profileModal')">
                <i class="fas fa-user-tie"></i> <span>Profile Admin</span>
            </li>
            <li class="menu-item" onclick="openModal('salesModal')">
                <i class="fas fa-chart-line"></i> <span>Info Penjualan</span>
            </li>
            <li class="menu-item" onclick="openModal('chatModal')">
                <i class="fas fa-comments"></i> <span>Chat</span>
            </li>
            <li class="menu-item" onclick="openModal('productModal')">
                <i class="fas fa-box-open"></i> <span>Setting Produk</span>
            </li>
            <li class="menu-item" onclick="openModal('announcementModal')">
                <i class="fas fa-bullhorn"></i> <span>Pengumuman</span>
            </li>
            <li class="menu-item" style="margin-top: 50px; color: #e74c3c;" onclick="location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-card">
            <h1>Selamat Datang, Admin!</h1>
            <p>Kelola ekosistem Luxury Shope dengan bijaksana.</p>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Visi & Misi</h3>
                <p><strong>Visi:</strong> Menjadi platform e-commerce termewah di Asia.</p>
                <p><strong>Misi:</strong> Memberikan pelayanan eksklusif dan produk berkualitas tinggi bagi setiap
                    pelanggan.</p>
            </div>
            <div class="info-box">
                <h3>Prestasi & Sertifikasi</h3>
                <p><i class="fas fa-trophy" style="color:#f1c40f"></i> Best Luxury App 2023</p>
                <p><i class="fas fa-certificate" style="color:#3498db"></i> ISO 9001:2015 Trusted Seller</p>
            </div>
        </div>
    </div>

    <!-- MODALS -->

    <!-- Profile Admin -->
    <div class="modal-overlay" id="profileModal">
        <div class="modal-content">
            <i class="fas fa-times close-modal" onclick="closeModal('profileModal')"></i>
            <h2>Profil Admin</h2>
            <div style="display:flex; gap:30px; margin-top:20px; align-items:center;">
                <div
                    style="width:120px; height:120px; background:#eee; border-radius:50%; display:flex; justify-content:center; align-items:center; font-size:40px;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <h3>
                        <?php echo $admin_profile['nama']; ?>
                    </h3>
                    <p style="color:#777;">
                        <?php echo $admin_profile['alamat']; ?>
                    </p>
                    <p style="margin-top:10px;">
                        <?php echo $admin_profile['bio']; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Penjualan -->
    <div class="modal-overlay" id="salesModal">
        <div class="modal-content" style="max-width: 900px;">
            <i class="fas fa-times close-modal" onclick="closeModal('salesModal')"></i>
            <h2>Informasi Penjualan</h2>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">
                <div class="info-box">
                    <h4>Grafik Keuntungan</h4>
                    <div class="chart-container">
                        <canvas id="profitChart"></canvas>
                    </div>
                </div>
                <div class="info-box">
                    <h4>Record Penjualan</h4>
                    <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:14px;">
                        <tr style="border-bottom:2px solid #eee;">
                            <th align="left">Pembeli</th>
                            <th align="left">Produk</th>
                            <th align="left">Total</th>
                        </tr>
                        <?php foreach ($sales_record as $row): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:10px 0;">
                                    <?php echo $row['user']; ?>
                                </td>
                                <td>
                                    <?php echo $row['item']; ?>
                                </td>
                                <td>Rp
                                    <?php echo number_format($row['total'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Settings -->
    <div class="modal-overlay" id="productModal">
        <div class="modal-content">
            <i class="fas fa-times close-modal" onclick="closeModal('productModal')"></i>
            <h2>Setting Produk</h2>
            <div style="margin-top: 20px;">
                <div class="menu-item" style="border: 1px solid #eee; display:flex; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <div style="width:50px; height:50px; background:#ddd; border-radius:8px;"></div>
                        <div>
                            <strong>Premium Gaming Laptop Z1</strong>
                            <p style="font-size:12px; color:#777;">Stok saat ini: 15</p>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button
                            style="padding:5px 10px; border-radius:5px; border:1px solid #ddd; background:#fff;">Edit
                            Stok</button>
                        <button
                            style="padding:5px 10px; border-radius:5px; border:none; background:#ff4757; color:#fff;">Stok
                            Habis</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcement -->
    <div class="modal-overlay" id="announcementModal">
        <div class="modal-content">
            <i class="fas fa-times close-modal" onclick="closeModal('announcementModal')"></i>
            <h2>Kirim Pengumuman</h2>
            <form style="margin-top:20px;">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px;">Judul Notifikasi</label>
                    <input type="text" style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd;"
                        placeholder="Contoh: Diskon Gajian!">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px;">Pesan</label>
                    <textarea style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd;" rows="4"
                        placeholder="Masukkan isi pesan pengumuman..."></textarea>
                </div>
                <button type="button" class="welcome-card"
                    style="width:100%; border:none; padding:15px; cursor:pointer;"
                    onclick="alert('Pengumuman terkirim ke semua pemesan!'); closeModal('announcementModal')">Kirim
                    Sekarang</button>
            </form>
        </div>
    </div>

    <!-- Chat Modal -->
    <div class="modal-overlay" id="chatModal">
        <div class="modal-content">
            <i class="fas fa-times close-modal" onclick="closeModal('chatModal')"></i>
            <h2>Chat dengan Pemesan</h2>
            <div
                style="height:300px; background:#f9f9f9; margin-top:20px; border-radius:12px; padding:20px; overflow-y:auto; border:1px solid #eee;">
                <div
                    style="background:var(--primary-light); padding:10px 15px; border-radius:15px 15px 15px 0; max-width:70%; margin-bottom:15px;">
                    <small>Andi:</small>
                    <p>Halo admin, apakah stok laptopnya masih ada?</p>
                </div>
                <div
                    style="background:var(--secondary-light); padding:10px 15px; border-radius:15px 15px 0 15px; max-width:70%; margin-left:auto; margin-bottom:15px; text-align:right;">
                    <small>Admin:</small>
                    <p>Halo kak Andi, stok masih ada ya. Silakan diorder :)</p>
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:15px;">
                <input type="text" style="flex:1; padding:12px; border-radius:8px; border:1px solid #ddd;"
                    placeholder="Balas chat...">
                <button style="padding:10px 20px; border-radius:8px; border:none; background:var(--primary-main);"><i
                        class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
            if (id === 'salesModal') initChart();
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function initChart() {
            const ctx = document.getElementById('profitChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                    datasets: [{
                        label: 'Keuntungan (Juta Rupiah)',
                        data: [12, 19, 3, 5, 2, 3, 10],
                        borderColor: '#90EE90',
                        backgroundColor: 'rgba(144, 238, 144, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    </script>
</body>

</html>