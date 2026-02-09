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

// Real Chat Count (Unread from users)
$stmt = $pdo->query("SELECT COUNT(DISTINCT user_id, product_id) as unread_count FROM chats WHERE is_read = 0 AND is_admin = 0");
$new_chats = $stmt->fetch()['unread_count'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alvitho Admin | PayBag</title>
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
            <i class="fas fa-gem"></i> PayBag Admin
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
                <p style="color: #777;">Monitor performa bisnis PayBag Anda hari ini.</p>
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
                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px;">
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                                <h4>Grafik Keuntungan (Rp)</h4>
                                <select id="chartPeriod" onchange="updateSalesChart(this.value)" style="border:1px solid #ddd; padding:5px; border-radius:5px;">
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                    <option value="yearly">Tahunan</option>
                                </select>
                            </div>
                            <div style="height: 220px; position: relative; margin-bottom: 30px;">
                                <canvas id="salesChart"></canvas>
                            </div>

                            <h4>Paling Banyak Terjual (Unit)</h4>
                            <div style="height: 200px; position: relative;">
                                <canvas id="productChart"></canvas>
                            </div>
                        </div>
                        <div style="background:#f9f9f9; padding:20px; border-radius:20px; max-height: 550px; overflow-y: auto;">
                            <h4>Record Penjualan Terbaru</h4>
                            <div id="salesRecordsList" style="margin-top: 15px; font-size: 12px;">
                                <div style="text-align:center; padding:20px; color:#999;">Memuat records...</div>
                            </div>
                            <h4 style="margin-top: 25px;">Pesan Terbaru Pemesan</h4>
                            <div id="latestBuyerMessages" style="margin-top: 10px; font-size: 11px; color: #666;">
                                <div style="text-align:center; padding:20px; color:#999;">Memuat pesan...</div>
                            </div>
                        </div>
                    </div>
                `;
                fetchSalesInfo();
            } else if (type === 'chat') {
                content.innerHTML = `
                    <h2 style="margin-bottom: 30px;"><i class="fas fa-comments"></i> Pusat Chat Pelanggan (Berdasarkan Produk)</h2>
                    <div style="display: grid; grid-template-columns: 280px 1fr; border: 1px solid #eee; border-radius: 15px; overflow: hidden; height: 500px; background: white;">
                        <div style="background: #f8f8f8; border-right: 1px solid #eee; overflow-y: auto;" id="adminChatList">
                            <div style="padding: 15px; background: white; border-bottom: 1px solid #eee;"><strong>Pesan Masuk</strong></div>
                            <div style="padding: 30px; text-align: center; color: #999;">Memuat percakapan...</div>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <div id="activeChatHeader" style="padding: 15px; background: #fff; border-bottom: 1px solid #eee; font-weight: 600; font-size: 14px; display: none;">
                                <span id="activeUserName"></span> - <span id="activeProductName" style="color: var(--primary-main);"></span>
                            </div>
                            <div id="adminChatMessages" style="flex: 1; padding: 20px; background: #fafafa; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                                <div style="margin: auto; color: #bbb; text-align: center;">Pilih percakapan dari kiri untuk mulai membalas.</div>
                            </div>
                            <div id="adminChatInputArea" style="padding: 15px; border-top: 1px solid #eee; display: none; gap: 10px;">
                                <input type="text" id="adminReplyInput" placeholder="Balas pesan..." class="form-input" style="flex: 1;">
                                <button class="btn btn-primary" onclick="adminSendReply()"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                fetchAdminConversations();
                if (convInterval) clearInterval(convInterval);
                convInterval = setInterval(fetchAdminConversations, 5000);
            }
            else if (type === 'product') {
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
        let salesData = null;
        function fetchSalesInfo() {
            fetch('get_sales_info.php?action=all')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        salesData = data.stats;

                        // Populate Records
                        const recordList = document.getElementById('salesRecordsList');
                        recordList.innerHTML = '';
                        data.records.forEach(r => {
                            recordList.innerHTML += `
                                <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee;">
                                    <span>ORD #${r.id} - ${r.nama_pengguna || 'Guest'}</span>
                                    <strong style="color:#2ecc71">Rp ${parseInt(r.total_price).toLocaleString('id-ID')}</strong>
                                </div>
                            `;
                        });

                        // Populate Messages
                        const msgList = document.getElementById('latestBuyerMessages');
                        msgList.innerHTML = '';
                        data.messages.forEach(m => {
                            msgList.innerHTML += `
                                <div style="padding: 8px; background: white; border-radius: 8px; margin-bottom: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                                    "${m.message}" - <strong>${m.nama_pengguna}</strong>
                                </div>
                            `;
                        });

                        initSalesChart(data.stats.weekly);
                        initProductChart(data.stats.best_selling);
                    }
                });
        }

        function fetchAdminProducts() {
            fetch('get_products_json.php')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('adminPList');
                    list.innerHTML = '';
                    data.forEach(p => {
                        const isOut = p.stock <= 0;
                        const statusClass = !isOut ? 'status-completed' : 'status-unpaid';
                        const statusText = !isOut ? 'Tersedia' : 'Stok Habis';
                        list.innerHTML += `
                        <tr style="border-bottom: 1px solid #eee;" class="p-row" data-name="${p.name.toLowerCase()}">
                            <td style="padding: 12px;">#${p.id}</td>
                            <td style="padding: 12px;">
                                ${p.name}
                                <span class="status-pill ${statusClass}" style="margin-left:8px; font-size:9px;">${statusText}</span>
                            </td>
                            <td style="padding: 12px;">
                                <input type="number" id="s-${p.id}" value="${p.stock}" style="width: 60px;" class="form-input">
                            </td>
                            <td style="padding: 12px; display:flex; gap:5px;">
                                <button onclick="updateStock(${p.id}, 'update')" class="btn" style="background:var(--secondary-main); color:white; font-size:10px; padding:8px 12px;">Simpan</button>
                                <button onclick="updateStock(${p.id}, 'out')" class="btn" style="background: #ff4757; color: white; font-size: 10px; padding:8px 12px;">Mark Habis</button>
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

        let activeChatUser = null;
        let activeChatProduct = null;
        let chatInterval = null;
        let convInterval = null;

        function fetchAdminConversations() {
            fetch('get_chats.php?action=get_conversations')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('adminChatList');
                    if (!list) return;
                    list.innerHTML = '<div style="padding: 15px; background: white; border-bottom: 1px solid #eee;"><strong>Pesan Masuk</strong></div>';
                    if (data.success && data.conversations.length > 0) {
                        data.conversations.forEach(c => {
                            const unread = c.is_read == 0 && c.is_admin == 0 ? '<span style="background:var(--primary-main); color:white; border-radius:50%; padding:2px 6px; font-size:10px; margin-left:5px;">!</span>' : '';
                            const activeClass = (activeChatUser == c.user_id && activeChatProduct == c.product_id) ? 'style="background: var(--secondary-light);"' : '';
                            list.innerHTML += `
                                <div onclick="openAdminChat(${c.user_id}, ${c.product_id}, '${c.user_name}', '${c.product_name}')" 
                                     style="padding: 15px; border-bottom: 1px solid #eee; cursor: pointer;" ${activeClass}>
                                    <div style="font-weight: 600; font-size: 13px;">${c.user_name} ${unread}</div>
                                    <div style="font-size: 11px; color: var(--primary-main);">Produk: ${c.product_name}</div>
                                    <div style="font-size: 11px; color: #777; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">${c.message}</div>
                                </div>
                            `;
                        });
                    } else {
                        list.innerHTML += '<div style="padding: 30px; text-align: center; color: #999;">Belum ada percakapan.</div>';
                    }
                });
        }

        function openAdminChat(userId, productId, userName, productName) {
            activeChatUser = userId;
            activeChatProduct = productId;
            document.getElementById('activeChatHeader').style.display = 'block';
            document.getElementById('activeUserName').innerText = userName;
            document.getElementById('activeProductName').innerText = productName;
            document.getElementById('adminChatInputArea').style.display = 'flex';

            fetchAdminMessages();
            if (chatInterval) clearInterval(chatInterval);
            chatInterval = setInterval(fetchAdminMessages, 3000);

            // Highlight active in list
            fetchAdminConversations();
        }

        function fetchAdminMessages() {
            if (!activeChatUser || !activeChatProduct) return;
            fetch(`get_chats.php?user_id=${activeChatUser}&product_id=${activeChatProduct}`)
                .then(r => r.json())
                .then(data => {
                    const box = document.getElementById('adminChatMessages');
                    if (!box) return;
                    if (data.success) {
                        box.innerHTML = '';
                        data.messages.forEach(m => {
                            const isMe = m.is_admin == 1;
                            const align = isMe ? 'flex-end' : 'flex-start';
                            const bg = isMe ? 'var(--secondary-main)' : '#eee';
                            const color = isMe ? 'white' : '#333';
                            box.innerHTML += `
                                <div style="align-self: ${align}; background: ${bg}; color: ${color}; padding: 10px 15px; border-radius: 12px; max-width: 80%; font-size: 13px;">
                                    ${m.message}
                                </div>
                            `;
                        });
                        box.scrollTop = box.scrollHeight;
                    }
                });
        }

        function adminSendReply() {
            const input = document.getElementById('adminReplyInput');
            const msg = input.value.trim();
            if (!msg || !activeChatUser || !activeChatProduct) return;

            const fd = new FormData();
            fd.append('user_id', activeChatUser);
            fd.append('product_id', activeChatProduct);
            fd.append('message', msg);

            fetch('send_chat.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        fetchAdminMessages();
                    }
                });
        }

        function closeModule() {
            document.getElementById('moduleOverlay').style.display = 'none';
            document.getElementById('adminMain').classList.remove('blur-bg');
            if (chatInterval) {
                clearInterval(chatInterval);
                chatInterval = null;
            }
            if (convInterval) {
                clearInterval(convInterval);
                convInterval = null;
            }
        }

        let chart;
        let pChart;
        function initSalesChart(initialData) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            if (chart) chart.destroy();

            const labels = initialData.map(d => d.date || d.label);
            const totals = initialData.map(d => d.total);

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Keuntungan (Rp)',
                        data: totals,
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        fill: true, tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2ecc71',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { display: false } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        function initProductChart(productData) {
            const ctx = document.getElementById('productChart').getContext('2d');
            if (pChart) pChart.destroy();

            const labels = productData.map(d => d.name);
            const totals = productData.map(d => d.total_sold);

            pChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Unit Terjual',
                        data: totals,
                        backgroundColor: '#ADD8E6',
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { beginAtZero: true, grid: { display: false } },
                        y: { grid: { display: false } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        function updateSalesChart(period) {
            if (!chart || !salesData) return;
            const data = salesData[period];
            chart.data.labels = data.map(d => d.date || d.label);
            chart.data.datasets[0].data = data.map(d => d.total);
            chart.update();
        }
    </script>
</body>

</html>