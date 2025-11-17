<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Online Sederhana - Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .product-img {
            width: 100%;
            height: 200px;
            background: #e0e0e0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            font-size: 48px;
        }
        .product-name {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }
        .product-price {
            color: #e74c3c;
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #4caf50;
        }
        .server-info {
            background: #fff3e0;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #ff9800;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 Toko Online Sederhana</h1>

        <div class="info">
            <strong>Status:</strong> Testing Mode ✅<br>
            <strong>Waktu Server:</strong> <?php echo date('d-m-Y H:i:s'); ?>
        </div>

        <div class="server-info">
            <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
            <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
        </div>

        <h2 style="margin-top: 20px; color: #555;">Produk Tersedia</h2>

        <div class="products">
            <?php
            // Data produk dummy untuk testing
            $products = [
                [
                    'id' => 1,
                    'name' => 'Laptop Gaming',
                    'price' => 15000000,
                    'icon' => '💻'
                ],
                [
                    'id' => 2,
                    'name' => 'Smartphone',
                    'price' => 5000000,
                    'icon' => '📱'
                ],
                [
                    'id' => 3,
                    'name' => 'Headphone',
                    'price' => 750000,
                    'icon' => '🎧'
                ],
                [
                    'id' => 4,
                    'name' => 'Keyboard Mechanical',
                    'price' => 1200000,
                    'icon' => '⌨️'
                ],
                [
                    'id' => 5,
                    'name' => 'Mouse Gaming',
                    'price' => 500000,
                    'icon' => '🖱️'
                ],
                [
                    'id' => 6,
                    'name' => 'Webcam HD',
                    'price' => 800000,
                    'icon' => '📷'
                ]
            ];

            foreach ($products as $product) {
                echo '<div class="product-card">';
                echo '<div class="product-img">' . $product['icon'] . '</div>';
                echo '<div class="product-name">' . htmlspecialchars($product['name']) . '</div>';
                echo '<div class="product-price">Rp ' . number_format($product['price'], 0, ',', '.') . '</div>';
                echo '<button class="btn" onclick="addToCart(' . $product['id'] . ', \'' . htmlspecialchars($product['name']) . '\')">Tambah ke Keranjang</button>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script>
        function addToCart(id, name) {
            alert('Produk "' + name + '" berhasil ditambahkan ke keranjang!\n\nID: ' + id);
            console.log('Product added:', {id: id, name: name});
        }
    </script>
</body>
</html>
