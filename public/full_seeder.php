<?php
require_once 'inc/db.php';

echo "<h2>Starting Deep Seeding Process...</h2>";

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE reviews");
    $pdo->exec("TRUNCATE TABLE products");
    $pdo->exec("TRUNCATE TABLE notifications");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $categories = ['Premium Fashion', 'Luxury Watches', 'High-End Tech', 'Jewelry', 'Cosmetics'];
    $images = [
        'Fashion' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b',
        'Watches' => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314',
        'Tech' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c',
        'Jewelry' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338',
        'Cosmetics' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796'
    ];

    // Luxury Video Sample URLs (Short/Premium)
    $videos = [
        'https://assets.mixkit.co/videos/preview/mixkit-fashion-model-walking-on-a-bridge-34444-large.mp4', // Loopable
        'https://assets.mixkit.co/videos/preview/mixkit-holding-a-glass-of-red-wine-at-a-party-40432-large.mp4',
        'https://assets.mixkit.co/videos/preview/mixkit-close-up-of-a-luxury-watch-running-4482-large.mp4',
        'https://assets.mixkit.co/videos/preview/mixkit-model-man-wearing-luxury-watch-and-suit-40431-large.mp4'
    ];

    $product_count = 510;

    // 1. Seed Products
    $sql_p = "INSERT INTO products (name, description, price, discount_percent, stock, category, image_url, video_url, rating) VALUES (:name, :desc, :price, :disc, :stock, :cat, :img, :vid, :rating)";
    $stmt_p = $pdo->prepare($sql_p);

    $sql_r = "INSERT INTO reviews (user_id, product_id, rating, comment, review_image, review_video) VALUES (:uid, :pid, :rating, :comment, :img, :vid)";
    $stmt_r = $pdo->prepare($sql_r);

    $comments = [
        "Kualitas produk sangat premium, sepadan dengan harganya!",
        "Gila sih ini, pengiriman cepat dan packing sangat aman.",
        "Awalnya ragu, pas datang ternyata barangnya ori dan mewah.",
        "Suka banget sama desainnya, bikin pede pas dipake pesta.",
        "Recomended seller! Chat dibalas cepat dan sangat informatif."
    ];

    // Assuming user 1 exists (after registration)
    $user_id = 1;

    for ($i = 1; $i <= $product_count; $i++) {
        $cat = $categories[array_rand($categories)];
        $name = "$cat Luxe Edition #$i";
        $desc = "Produk unggulan dari koleksi $cat LUXURY 2024. Menggunakan material Grade A+ yang memberikan durabilitas tinggi serta estetika premium. Cocok untuk Anda yang mengutamakan kualitas dan gaya hidup eksklusif.";
        $price = rand(1500000, 75000000);
        $disc = (rand(1, 10) > 8) ? rand(10, 50) : 0;
        $stock = rand(5, 100);
        $rating = rand(45, 50) / 10;
        $img = $images[explode(' ', $cat)[1] ?? 'Fashion'] . "?sig=" . ($i + 200);
        $vid = $videos[array_rand($videos)]; // Every product gets a video now

        $stmt_p->execute([
            ':name' => $name,
            ':desc' => $desc,
            ':price' => $price,
            ':disc' => $disc,
            ':stock' => $stock,
            ':cat' => $cat,
            ':img' => $img,
            ':vid' => $vid,
            ':rating' => $rating
        ]);

        $pid = $pdo->lastInsertId();

        // 2. Seed 3-5 Reviews for EVERY product
        $num_rev = rand(3, 5);
        for ($j = 0; $j < $num_rev; $j++) {
            $stmt_r->execute([
                ':uid' => $user_id,
                ':pid' => $pid,
                ':rating' => rand(4, 5),
                ':comment' => $comments[$j % count($comments)],
                ':img' => (rand(1, 10) > 5) ? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?sig=' . ($i * 10 + $j) : null,
                ':vid' => null
            ]);
        }

        if ($i % 50 == 0)
            echo "Processed $i items... <br>";
    }

    // 3. Seed Notifications
    $stmt_n = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, :title, :msg, :type)");
    $stmt_n->execute([
        ':title' => 'Selamat Datang di Luxury Shope!',
        ':msg' => 'Nikmati pengalaman belanja produk premium dengan 500+ pilihan terbaik.',
        ':type' => 'Pengumuman'
    ]);
    $stmt_n->execute([
        ':title' => 'Promo Flash Sale!',
        ':msg' => 'Diskon hingga 40% untuk kategori Luxury Watches akhir pekan ini.',
        ':type' => 'Diskon'
    ]);

    echo "<h3>SUCCESS: 510 products, reviews, and notifications seeded!</h3>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";

} catch (PDOException $e) {
    die("Deep Seeding failed: " . $e->getMessage());
}
?>