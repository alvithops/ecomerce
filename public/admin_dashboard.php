<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}

// Fetch Admin Stats
$total_revenue = 158500000; // Simulated
$total_orders = 842;        // Simulated
$new_chats = 5;             // Simulated

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin | Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f4f7f6;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Navigation */
        .admin-sidebar {
            width: 280px;
            background: var(--white);
            height: 100vh;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.03);
            z-index: 1000;
        }

        .side-btn {
            padding: 15px 20px;
            border-radius: 12px;
            text-decoration: none;
            color: #555;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            transition: var(--transition);
            border: none;
            background: none;
            width: 100%;
            font-size: 15px;
            cursor: pointer;
            text-align: left;
        }

        .side-btn:hover,
        .side-btn.active {
            background: var(--secondary-light);
            color: var(--secondary-main);
        }

        .side-btn i {
            font-size: 20px;
        }

        /* Main Viewport */
        .admin-viewport {
            flex: 1;
            height: 100vh;
            overflow-y: auto;
            padding: 40px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            border-left: 5px solid var(--primary-main);
        }

        .vision-mission {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: var(--shadow-medium);
            line-height: 1.8;
        }

        /* MODAL OVERLAY (The Blur Logic) */
        .admin-overlay {
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
            padding: 40px;
        }

        .modal-card {
            background: white;
            width: 100%;
            max-width: 900px;
            border-radius: 30px;
            padding: 40px;
            box-shadow: var(--shadow-medium);
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 25px;
            right: 25px;
            background: #f1f1f1;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal:hover {
            background: #ff4757;
            color: white;
        }

        /* Custom Scrollbar for Modal */
        .modal-card::-webkit-scrollbar {
            width: 6px;
        }

        .modal-card::-webkit-scrollbar-thumb {
            background: #ddd;
            border-radius: 10px;
        }
    </style>
</head>

<body>

    <!-- Sidebar Menu -->
    <aside class="admin-sidebar">
        <div
            style="font-size: 24px; font-weight: 700; color: var(--secondary-main); margin-bottom: 45px; display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-gem"></i> Luxury Admin
        </div>

        <button class="side-btn active" onclick="location.reload()"><i class="fas fa-columns"></i> Dashboard
            Hub</button>
        <button onclick="openModule('profile')" class="side-btn"><i class="fas fa-user-tie"></i> Profile Admin</button>
        <button onclick="openModule('sales')" class="side-btn"><i class="fas fa-chart-pie"></i> Info Penjualan</button>
        <button onclick="openModule('chat')" class="side-btn"><i class="fas fa-comments"></i> Chat</button>
        <button onclick="openModule('product')" class="side-btn"><i class="fas fa-boxes"></i> Setting Produk</button>
        <button onclick="openModule('promo')" class="side-btn"><i class="fas fa-bullhorn"></i> Pengumuman</button>

        <div style="margin-top: auto;">
            <a href="logout.php" class="side-btn" style="color: #ff4757;"><i class="fas fa-power-off"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-viewport animate-fade" id="adminMain">
        <div class="dashboard-header">
            <div>
                <h1 style="font-size: 28px;">Selamat Datang, Admin</h1>
                <p style="color: #777;">Monitor performa bisnis Luxury Shope Anda hari ini.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn glass" onclick="location.reload()"><i class="fas fa-sync-alt"></i></button>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-box animate-up">
                <div style="color: #999; font-size: 13px;">Total Omzet</div>
                <div style="font-size: 24px; font-weight: 700; margin-top: 5px;">Rp
                    <?= number_format($total_revenue, 0, ',', '.') ?>
                </div>
            </div>
            <div class="stat-box animate-up" style="animation-delay: 0.1s; border-left-color: var(--secondary-main);">
                <div style="color: #999; font-size: 13px;">Pesanan Masuk</div>
                <div style="font-size: 24px; font-weight: 700; margin-top: 5px;"><?= $total_orders ?></div>
            </div>
            <div class="stat-box animate-up" style="animation-delay: 0.2s; border-left-color: #f1c40f;">
                <div style="color: #999; font-size: 13px;">Chat Belum Dibalas</div>
                <div style="font-size: 24px; font-weight: 700; margin-top: 5px;"><?= $new_chats ?></div>
            </div>
        </div>

        <div class="vision-mission animate-up" style="animation-delay: 0.3s;">
            <h2 style="margin-bottom: 25px; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-bullseye" style="color: var(--primary-main);"></i> Visi & Misi Website
            </h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                <div style="background: #f9f9f9; padding: 25px; border-radius: 15px;">
                    <h4 style="color: var(--primary-main); margin-bottom: 10px;">Visi Kami</h4>
                    <p>Membangun platform e-commerce termewah dan terpercaya di Indonesia yang menghubungkan 500+ produk
                        premium kepada pelanggan dengan pengalaman berbasis AI dan Geolocation Maps tercanggih.</p>
                </div>
                <div style="background: #f9f9f9; padding: 25px; border-radius: 15px;">
                    <h4 style="color: var(--secondary-main); margin-bottom: 10px;">Misi Kami</h4>
                    <ul style="padding-left: 20px;">
                        <li>Memberikan layanan 24/7 dengan sistem chat real-time.</li>
                        <li>Memastikan akurasi pengiriman melalui integrasi Maps.</li>
                        <li>Transparansi keuangan dengan laporan analitik mendalam.</li>
                    </ul>
                </div>
            </div>

            <h4 style="margin: 40px 0 20px;">Prestasi & Sertifikasi Keamanan</h4>
            <div style="display: flex; gap: 20px;">
                <div class="btn glass" style="padding: 15px; flex: 1; border: 1px solid #ddd;">
                    <i class="fas fa-award" style="color: #ffa000; font-size: 24px;"></i>
                    <span>Toko Terpercaya 2026</span>
                </div>
                <div class="btn glass" style="padding: 15px; flex: 1; border: 1px solid #ddd;">
                    <i class="fas fa-lock" style="color: #2ecc71; font-size: 24px;"></i>
                    <span>SSL Secure Certified</span>
                </div>
            </div>
        </div>
    </main>

    <!-- MODULE OVERLAY -->
    <div id="moduleOverlay" class="admin-overlay">
        <div class="modal-card animate-up">
            <button class="close-modal" onclick="closeModule()"><i class="fas fa-times"></i></button>
            <div id="moduleContent"></div>
        </div>
    </div>

    <script>
        function openModule(type) {
            const overlay = document.getElementById('moduleOverlay');
            const content = document.getElementById('moduleContent');
            const main = document.getElementById('adminMain');

            overlay.style.display = 'flex';
            main.classList.add('blur-bg'); // From style.css

            if (type === 'profile') {
                content.innerHTML = `
                    <h2 style="margin-bottom: 30px;"><i class="fas fa-id-card"></i> Biodata Administrator</h2>
                    <div style="display: flex; gap: 40px; align-items: center;">
                        <img src="https://i.pravatar.cc/300?u=admin_payung" style="width: 200px; height: 200px; border-radius: 25px; object-fit: cover; box-shadow: var(--shadow-medium);">
                        <div>
                            <table style="width: 100%; border-spacing: 0 15px;">
                                <tr><td width="150" style="color:#777">Nama Lengkap</td><td><strong>Pak Admin Payung</strong></td></tr>
                                <tr><td style="color:#777">Username</td><td><code>payung</code></td></tr>
                                <tr><td style="color:#777">Jabatan</td><td><span class="status-pill status-shipping" style="background:#e8f5e9; color:#2e7d32">Senior Developer Owner</span></td></tr>
                                <tr><td style="color:#777">Alamat</td><td>Gedung Luxury Shope, Jakarta Pusat, Indonesia</td></tr>
                                <tr><td style="color:#777">Email Terdaftar</td><td>admin@luxuryshope.com</td></tr>
                            </table>
                            <button class="btn btn-primary" style="margin-top: 20px;">Edit Profil</button>
                        </div>
                    </div>
                `;
            } else if (type === 'sales') {
                content.innerHTML = `
                    <h2 style="margin-bottom: 30px;"><i class="fas fa-chart-line"></i> Info Penjualan & Record</h2>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <h4>Grafik Penjualan</h4>
                                <select onchange="updateChart(this.value)" style="border:1px solid #ddd; padding:5px; border-radius:5px;">
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                    <option value="yearly">Tahunan</option>
                                </select>
                            </div>
                            <canvas id="salesChart" style="width: 100%; height: 250px;"></canvas>
                        </div>
                        <div style="background:#f9f9f9; padding:20px; border-radius:20px;">
                            <h4>Record Penjualan</h4>
                            <div style="margin-top: 15px; font-size: 12px;">
                                <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee;">
                                    <span>ORD #881 - Andi P.</span>
                                    <strong style="color:#2ecc71">Rp 12jt</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee;">
                                    <span>ORD #880 - Budi S.</span>
                                    <strong style="color:#2ecc71">Rp 4.5jt</strong>
                                </div>
                            </div>
                            <h4 style="margin-top: 25px;">Pesan Terbaru Pemesan</h4>
                            <div style="margin-top: 10px; font-size: 11px; color: #666;">
                                <div style="padding: 8px; background: white; border-radius: 8px; margin-bottom: 5px;">"Stok jam tangan emas yang #22 masih ada?" - <strong>Lia</strong></div>
                                <div style="padding: 8px; background: white; border-radius: 8px;">"Makasih min barang sudah dipajang!" - <strong>Rian</strong></div>
                            </div>
                        </div>
                    </div>
                `;
                initSalesChart();
            } else if (type === 'chat') {
                content.innerHTML = `
                    <h2 style="margin-bottom: 30px;"><i class="fas fa-comments"></i> Pusat Chat Pelanggan (Berdasarkan Produk)</h2>
                    <div style="display: grid; grid-template-columns: 250px 1fr; border: 1px solid #eee; border-radius: 15px; overflow: hidden; height: 400px;">
                        <div style="background: #f8f8f8; border-right: 1px solid #eee; overflow-y: auto;">
                            <div style="padding: 15px; background: white; border-bottom: 1px solid #eee;"><strong>Pesan Masuk</strong></div>
                            <div style="padding: 15px; border-bottom: 1px solid #eee; background: var(--secondary-light); cursor: pointer;">
                                <div style="font-weight: 600; font-size: 13px;">Andi Pratama</div>
                                <div style="font-size: 11px; color: var(--primary-main);">Produk: Exclusive Bag #12</div>
                                <div style="font-size: 11px; color: #777; overflow: hidden; white-space: nowrap;">Halo, apakah barang ready?</div>
                            </div>
                            <div style="padding: 15px; border-bottom: 1px solid #eee; cursor: pointer;">
                                <div style="font-weight: 600; font-size: 13px;">Budi Santoso</div>
                                <div style="font-size: 11px; color: var(--primary-main);">Produk: Luxury Watch #45</div>
                                <div style="font-size: 11px; color: #777; overflow: hidden; white-space: nowrap;">Terima kasih barang sudah sampai...</div>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <div style="flex: 1; padding: 20px; background: white; overflow-y: auto;">
                                <div style="font-size: 12px; color: #999; text-align: center; margin-bottom: 15px;">Menampilkan Chat untuk: Andi Pratama (Exclusive Bag #12)</div>
                                <div style="display: inline-block; background: #f0f0f0; padding: 10px; border-radius: 10px; margin-bottom: 10px;">Halo, apakah barang ready?</div>
                                <div style="text-align: right;"><div style="display: inline-block; background: var(--secondary-main); color: white; padding: 10px; border-radius: 10px;">Tentu, barang premium kami selalu ready!</div></div>
                            </div>
                            <div style="padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px;">
                                <input type="text" placeholder="Balas pesan..." class="form-input">
                                <button class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>
                `;
            } else if (type === 'product') {
                content.innerHTML = `<h2 style="margin-bottom: 30px;"><i class="fas fa-boxes"></i> Setting Stok Produk (Live)</h2>
                    <div style="margin-bottom: 20px;">
                        <input type="text" id="pSearch" placeholder="Cari nama produk..." class="form-input" style="width: 300px;" onkeyup="filterAdminProducts()">
                    </div>
                    <div style="height: 400px; overflow-y: auto;">
                        <table style="width: 100%; text-align: left; border-collapse: collapse;">
                            <thead style="background: #f1f1f1; position: sticky; top: 0; z-index:1;">
                                <tr>
                                    <th style="padding: 12px;">ID</th>
                                    <th style="padding: 12px;">Nama Produk</th>
                                    <th style="padding: 12px;">Stok</th>
                                    <th style="padding: 12px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="adminPList">
                                <tr id="loadingP"><td colspan="4" style="text-align:center; padding:20px;">Memuat data produk...</td></tr>
                            </tbody>
                        </table>
                    </div>`;
                fetchAdminProducts();
            } else if (type === 'promo') {
                content.innerHTML = `
                    <h2 style="margin-bottom: 30px;"><i class="fas fa-bullhorn"></i> Broadcast Pengumuman Global</h2>
                    <form id="promoForm">
                        <div class="form-group">
                            <label>Judul Pengumuman</label>
                            <input type="text" name="title" class="form-input" placeholder="Contoh: Promo Flash Sale Weekend!" required>
                        </div>
                        <div class="form-group">
                            <label>Pilih Kategori</label>
                            <select name="type" class="form-input">
                                <option>Diskon</option>
                                <option>Pengumuman</option>
                                <option>Info Penting</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Isi Pesan</label>
                            <textarea name="message" class="form-input" rows="4" placeholder="Tulis rincian pengumuman di sini..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%; padding: 15px;">Kirim Sekarang</button>
                    </form>
                `;

                document.getElementById('promoForm').onsubmit = function (e) {
                    e.preventDefault();
                    const fd = new FormData(this);
                    fetch('admin_send_notif.php', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                alert('Pengumuman Berhasil Dikirim ke Semua Pemesan!');
                                closeModule();
                            } else alert('Gagal: ' + data.message);
                        });
                };
            }
        }

        // Feature functions for Admin
        function fetchAdminProducts() {
            fetch('get_products_json.php')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('adminPList');
                    list.innerHTML = '';
                    data.forEach(p => {
                        const statusClass = p.stock > 0 ? 'status-shipping' : 'status-unpaid';
                        const statusText = p.stock > 0 ? 'Tersedia' : 'Habis';
                        list.innerHTML += `
                        <tr style="border-bottom: 1px solid #eee;" class="p-row" data-name="${p.name.toLowerCase()}">
                            <td style="padding: 12px;">#${p.id}</td>
                            <td style="padding: 12px;">${p.name}</td>
                            <td style="padding: 12px;">
                                <input type="number" id="s-${p.id}" value="${p.stock}" style="width: 60px;" class="form-input">
                            </td>
                            <td style="padding: 12px; display:flex; gap:5px;">
                                <button onclick="updateStock(${p.id}, 'update')" class="btn" style="background:var(--secondary-main); color:white; font-size:10px;">Simpan</button>
                                <button onclick="updateStock(${p.id}, 'out')" class="btn" style="background: #ff4757; color: white; font-size: 10px;">Mark Habis</button>
                            </td>
                        </tr>
                    `;
                    });
                });
        }

        function filterAdminProducts() {
            const val = document.getElementById('pSearch').value.toLowerCase();
            document.querySelectorAll('.p-row').forEach(row => {
                row.style.display = row.dataset.name.includes(val) ? '' : 'none';
            });
        }

        function updateStock(id, action) {
            const stock = document.getElementById('s-' + id).value;
            const fd = new FormData();
            fd.append('product_id', id);
            fd.append('stock', stock);
            fd.append('action', action);

            fetch('admin_update_stock.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Status stok diperbarui!');
                        fetchAdminProducts();
                    } else alert('Gagal: ' + data.message);
                });
        }
        }

        function closeModule() {
            document.getElementById('moduleOverlay').style.display = 'none';
            document.getElementById('adminMain').classList.remove('blur-bg');
        }

        let chart;
        function initSalesChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                    datasets: [{
                        label: 'Keuntungan (Juta)',
                        data: [12, 19, 15, 25, 32, 45, 38],
                        borderColor: '#90EE90',
                        backgroundColor: 'rgba(144, 238, 144, 0.2)',
                        fill: true, tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        }

        function updateChart(period) {
            if (!chart) return;
            if (period === 'monthly') {
                chart.data.labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
                chart.data.datasets[0].data = [80, 120, 95, 150];
            } else if (period === 'yearly') {
                chart.data.labels = ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Okt-Des'];
                chart.data.datasets[0].data = [450, 720, 680, 950];
            } else {
                chart.data.labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                chart.data.datasets[0].data = [12, 19, 15, 25, 32, 45, 38];
            }
            chart.update();
        }
    </script>
</body>

</html>