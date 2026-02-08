<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}

/**
 * Simulasi Data Penjualan Admin
 */
$sales_sum = 125000000;
$order_count = 1450;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Custom */
        .sidebar {
            width: 280px;
            background: var(--white);
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.03);
            z-index: 100;
        }

        .side-link {
            padding: 14px 18px;
            border-radius: 12px;
            text-decoration: none;
            color: #555;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            transition: var(--transition);
        }

        .side-link:hover,
        .side-link.active {
            background: var(--secondary-light);
            color: var(--secondary-main);
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            height: 100vh;
            overflow-y: auto;
            padding: 40px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            border-bottom: 4px solid var(--primary-main);
        }

        .vision-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: var(--shadow-medium);
        }

        /* Admin Overlay */
        .admin-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(15px);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .admin-modal {
            background: white;
            width: 100%;
            max-width: 900px;
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--shadow-medium);
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div
            style="font-size: 24px; font-weight: 600; color: var(--secondary-main); margin-bottom: 40px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-shield-alt"></i> Panel Admin
        </div>

        <a href="#" class="side-link active"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="javascript:void(0)" onclick="openAdminModal('profile')" class="side-link"><i
                class="fas fa-user-circle"></i> Profile Admin</a>
        <a href="javascript:void(0)" onclick="openAdminModal('sales')" class="side-link"><i class="fas fa-receipt"></i>
            Info Penjualan</a>
        <a href="javascript:void(0)" onclick="openAdminModal('chat')" class="side-link"><i class="fas fa-comments"></i>
            Chat Pelanggan</a>
        <a href="javascript:void(0)" onclick="openAdminModal('product')" class="side-link"><i class="fas fa-boxes"></i>
            Setting Produk</a>
        <a href="javascript:void(0)" onclick="openAdminModal('promo')" class="side-link"><i class="fas fa-bullhorn"></i>
            Pengumuman</a>

        <div style="margin-top: auto;">
            <a href="logout.php" class="side-link" style="color: #ff4757;"><i class="fas fa-sign-out-alt"></i>
                Keluar</a>
        </div>
    </aside>

    <main class="admin-main animate-fade">
        <div class="stat-grid">
            <div class="stat-card animate-up" style="animation-delay: 0.1s;">
                <div style="color: #999; font-size: 14px;">Total Omzet</div>
                <div style="font-size: 24px; font-weight: 700; margin-top: 5px;">Rp 125,0jt</div>
            </div>
            <div class="stat-card animate-up" style="animation-delay: 0.2s;">
                <div style="color: #999; font-size: 14px;">Pesanan Baru</div>
                <div style="font-size: 24px; font-weight: 700; margin-top: 5px;">+12 Pesanan</div>
            </div>
            <div class="stat-card animate-up" style="animation-delay: 0.3s;">
                <div style="color: #999; font-size: 14px;">Rating Website</div>
                <div style="font-size: 24px; font-weight: 700; margin-top: 5px;">4.9 / 5.0</div>
            </div>
        </div>

        <div class="vision-card animate-up">
            <h2 style="margin-bottom: 25px; border-left: 5px solid var(--primary-main); padding-left: 15px;">Visi & Misi
                Perusahaan</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                <div>
                    <h4 style="color: var(--primary-main); margin-bottom: 10px;">Visi</h4>
                    <p style="color: #555; text-align: justify; font-size: 15px;">Menjadi platform e-commerce nomor satu
                        yang menyediakan barang-barang mewah dengan aksesibilitas termudah bagi seluruh lapisan
                        masyarakat di Indonesia.</p>
                </div>
                <div>
                    <h4 style="color: var(--secondary-main); margin-bottom: 10px;">Misi</h4>
                    <ul style="color: #555; font-size: 15px; padding-left: 20px;">
                        <li>Menjamin keaslian setiap produk premium yang dijual.</li>
                        <li>Memberikan pengalaman belanja berbasis teknologi mutakhir.</li>
                        <li>Membangun ekosistem jual beli yang transparan dan aman.</li>
                    </ul>
                </div>
            </div>

            <h4 style="margin: 40px 0 20px; color: #333;">Prestasi & Sertifikasi</h4>
            <div style="display: flex; gap: 15px;">
                <div
                    style="padding: 15px; border-radius: 12px; background: #fff8e1; border: 1px solid #ffe082; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-trophy" style="color: #ffa000;"></i>
                    <span style="font-size: 13px; font-weight: 600;">Best UI/UX Award 2025</span>
                </div>
                <div
                    style="padding: 15px; border-radius: 12px; background: #e8f5e9; border: 1px solid #a5d6a7; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-certificate" style="color: #2e7d32;"></i>
                    <span style="font-size: 13px; font-weight: 600;">ISO 27001 - Security</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Admin Overlay Modals -->
    <div id="adminOverlay" class="admin-modal-overlay">
        <div class="admin-modal animate-up">
            <button onclick="closeAdminModal()"
                style="position: absolute; top: 20px; right: 20px; border: none; background: #eee; width: 35px; height: 35px; border-radius: 50%; cursor: pointer;"><i
                    class="fas fa-times"></i></button>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        function openAdminModal(type) {
            const overlay = document.getElementById('adminOverlay');
            const content = document.getElementById('modalContent');
            overlay.style.display = 'flex';

            if (type === 'sales') {
                content.innerHTML = `
                    <h2 style="margin-bottom: 25px;">Statistik Penjualan</h2>
                    <canvas id="salesChart" style="width: 100%; height: 300px;"></canvas>
                    <div style="margin-top: 30px;">
                        <h4>Penjualan Terakhir</h4>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <tr style="background: #f8f9fa; font-size: 14px;">
                                <th style="padding: 12px; text-align: left;">ID Pesanan</th>
                                <th style="padding: 12px; text-align: left;">Pemesan</th>
                                <th style="padding: 12px; text-align: left;">Status</th>
                                <th style="padding: 12px; text-align: right;">Total</th>
                            </tr>
                            <tr style="border-bottom: 1px solid #eee; font-size: 13px;">
                                <td style="padding: 12px;">#ORD-772</td>
                                <td style="padding: 12px;">Andi Pratama</td>
                                <td style="padding: 12px;"><span style="color: #ffa000;">Dikemas</span></td>
                                <td style="padding: 12px; text-align: right;">Rp 12,5jt</td>
                            </tr>
                        </table>
                    </div>
                `;
                initChart();
            } else if (type === 'profile') {
                content.innerHTML = `
                    <h2 style="margin-bottom: 25px;">Profile Administrator</h2>
                    <div style="display: flex; gap: 30px;">
                        <img src="https://i.pravatar.cc/150?u=admin" style="width: 150px; height: 150px; border-radius: 20px; object-fit: cover;">
                        <div>
                            <p><strong>Nama:</strong> Admin Pak Payung</p>
                            <p><strong>Username:</strong> payung</p>
                            <p><strong>Peran:</strong> Senior Backend Owner</p>
                            <p><strong>Alamat:</strong> Jakarta Pusat, Indonesia</p>
                        </div>
                    </div>
                `;
            } else if (type === 'product') {
                content.innerHTML = `<h2>Kelola Stok Produk</h2><p>Pilih produk untuk mengedit stok atau menandai stok habis.</p>`;
            } else {
                content.innerHTML = `<h2>Panel ${type.charAt(0).toUpperCase() + type.slice(1)}</h2><p>Data sedang dimuat...</p>`;
            }
        }

        function closeAdminModal() {
            document.getElementById('adminOverlay').style.display = 'none';
        }

        function initChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    datasets: [{
                        label: 'Keuntungan (Juta)',
                        data: [50, 80, 45, 120, 95, 125],
                        borderColor: '#90EE90',
                        backgroundColor: 'rgba(144, 238, 144, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                }
            });
        }
    </script>
</body>

</html>