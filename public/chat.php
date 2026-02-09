<?php
require_once 'inc/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_pemesan.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['product_id'] ?? 0;
$product_name = "Luxury Admin";

if ($product_id > 0) {
    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $p = $stmt->fetch();
    if ($p)
        $product_name = $p['name'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Luxury Shope</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 20px;
            background: white;
            box-shadow: var(--shadow-soft);
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 10;
        }

        .chat-container {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .msg {
            max-width: 80%;
            padding: 15px;
            border-radius: 20px;
            font-size: 14px;
            position: relative;
            animation: fadeUp 0.3s ease-out forwards;
        }

        .msg-in {
            align-self: flex-start;
            background: white;
            box-shadow: var(--shadow-soft);
            border-bottom-left-radius: 5px;
        }

        .msg-out {
            align-self: flex-end;
            background: var(--secondary-main);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .chat-footer {
            padding: 20px;
            background: white;
            display: flex;
            gap: 15px;
            align-items: center;
            border-top: 1px solid #eee;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <header class="chat-header">
        <a href="dashboard.php" style="color: #333;"><i class="fas fa-arrow-left"></i></a>
        <div style="width: 45px; height: 45px; background: #eee; border-radius: 50%; overflow: hidden;">
            <img src="https://i.pravatar.cc/150?u=admin" style="width: 100%;">
        </div>
        <div>
            <div style="font-weight: 600; font-size: 15px;">Admin Luxury Shope</div>
            <div style="font-size: 11px; color: #2ecc71;"><i class="fas fa-circle" style="font-size: 8px;"></i> Online
            </div>
        </div>
    </header>

    <div class="chat-container">
        <div class="msg msg-in">
            Halo! Ada yang bisa kami bantu mengenai produk <strong>
                <?= htmlspecialchars($product_name) ?>
            </strong>?
        </div>
        <div class="msg msg-out">
            Halo admin, saya ingin tanya apakah stok produk ini masih ada?
        </div>
        <div class="msg msg-in">
            Tentu saja! Kami selalu memastikan stok produk premium kami diperbarui secara real-time. Silakan lanjutkan
            ke checkout ya!
        </div>
    </div>

    <div class="chat-footer">
        <button class="btn glass" style="width: 50px; height: 50px; border-radius: 50%; padding: 0;"><i
                class="fas fa-plus"></i></button>
        <input type="text" id="chatInput" placeholder="Ketik pesan Anda..." class="form-input" style="flex: 1;">
        <button id="sendMessageBtn" class="btn btn-primary"
            style="width: 50px; height: 50px; border-radius: 50%; padding: 0;"><i
                class="fas fa-paper-plane"></i></button>
    </div>

    <script>
        const container = document.querySelector('.chat-container');
        const input = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendMessageBtn');
        const productId = <?= $product_id ?>;

        function fetchMessages() {
            fetch(`get_chats.php?product_id=${productId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const currentScroll = container.scrollTop;
                        const atBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;

                        container.innerHTML = `
                            <div class="msg msg-in">
                                Halo! Ada yang bisa kami bantu mengenai produk <strong><?= htmlspecialchars($product_name) ?></strong>?
                            </div>
                        `;

                        data.messages.forEach(m => {
                            const isMe = m.is_admin == 0;
                            const msgDiv = document.createElement('div');
                            msgDiv.className = isMe ? 'msg msg-out' : 'msg msg-in';
                            msgDiv.innerText = m.message;
                            container.appendChild(msgDiv);
                        });

                        if (atBottom) {
                            container.scrollTop = container.scrollHeight;
                        } else {
                            container.scrollTop = currentScroll;
                        }
                    }
                });
        }

        function sendMessage() {
            const text = input.value.trim();
            if (!text) return;

            // Disable button during sending
            sendBtn.disabled = true;
            sendBtn.style.opacity = '0.5';

            const fd = new FormData();
            fd.append('product_id', productId);
            fd.append('message', text);

            fetch('send_chat.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    sendBtn.disabled = false;
                    sendBtn.style.opacity = '1';
                    if (data.success) {
                        input.value = '';
                        fetchMessages();
                    } else {
                        alert('Gagal mengirim pesan: ' + data.message);
                    }
                })
                .catch(err => {
                    sendBtn.disabled = false;
                    sendBtn.style.opacity = '1';
                    console.error('Error:', err);
                });
        }

        // Initial fetch
        fetchMessages();

        // Polling for updates
        setInterval(fetchMessages, 3000);

        // Send on click and enter
        sendBtn.addEventListener('click', function () {
            sendBtn.style.transform = 'scale(0.9)';
            setTimeout(() => sendBtn.style.transform = 'scale(1)', 100);
            sendMessage();
        });

        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Ensure button appearance
        sendBtn.style.cursor = 'pointer';
        sendBtn.style.zIndex = '100';
    </script>
</body>

</html>