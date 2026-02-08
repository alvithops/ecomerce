<?php
require_once 'inc/db.php';

echo "Memulai seeding 500+ produk... <br>";

try {
    // Kosongkan tabel produk dulu (opsional, tapi disarankan untuk seeding bersih)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE products");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $categories = ['Premium Fashion', 'Luxury Watches', 'High-End Tech', 'Jewelry', 'Cosmetics'];
    $images = [
        'Fashion' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b',
        'Watches' => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314',
        'Tech' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c',
        'Jewelry' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338',
        'Cosmetics' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796'
    ];

    $product_count = 510;

    $sql = "INSERT INTO products (name, description, price, discount_percent, stock, category, image_url, rating) VALUES (:name, :desc, :price, :disc, :stock, :cat, :img, :rating)";
    $stmt = $pdo->prepare($sql);

    for ($i = 1; $i <= $product_count; $i++) {
        $cat = $categories[array_rand($categories)];
        $name = "$cat - Exclusive Item Vol. $i";
        $desc = "Ini adalah produk premium dari koleksi $cat. Dibuat dengan material berkualitas tinggi dan desain eksklusif untuk gaya hidup mewah Anda.";
        $price = rand(500000, 25000000);
        $disc = (rand(1, 100) > 70) ? rand(10, 50) : 0; // 30% chance for discount
        $stock = rand(5, 100);
        $rating = rand(40, 50) / 10; // Rating 4.0 - 5.0
        $img = $images[explode(' ', $cat)[1] ?? 'Fashion'] . "?sig=$i"; // Unique image via sig

        $stmt->execute([
            ':name' => $name,
            ':desc' => $desc,
            ':price' => $price,
            ':disc' => $disc,
            ':stock' => $stock,
            ':cat' => $cat,
            ':img' => $img,
            ':rating' => $rating
        ]);

        if ($i % 50 == 0)
            echo "Telah memasukkan $i produk... <br>";
    }

    echo "<h3>Seeding Selesai! 510 Produk berhasil dimasukkan ke database.</h3>";
    echo "<p><a href='dashboard.php'>Lihat Dashboard</a></p>";

} catch (PDOException $e) {
    die("Seeding gagal: " . $e->getMessage());
}
?>