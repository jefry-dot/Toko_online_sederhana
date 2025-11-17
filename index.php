<?php
require_once 'includes/header.php';
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>Selamat Datang di TokoOnline</h1>
        <p>Temukan produk terbaik dengan harga terjangkau</p>
        <a href="products/index.php" class="btn-primary">Belanja Sekarang</a>
    </div>
</div>

<div class="container">
    <section class="categories-section">
        <h2>Kategori Produk</h2>
        <div class="categories-grid">
            <?php foreach (getCategories($pdo) as $category): ?>
                <div class="category-card">
                    <?php if ($category['icon']): ?>
                        <i class="<?= $category['icon'] ?>"></i>
                    <?php endif; ?>
                    <h3><?= $category['name'] ?></h3>
                    <a href="products/index.php?category=<?= $category['id'] ?>" class="btn-secondary">Lihat Produk</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="products-section">
        <h2>Produk Terbaru</h2>
        <div class="products-grid">
            <?php $products = getProducts($pdo, null, 8); ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php if ($product['main_image']): ?>
                        <img src="uploads/<?= $product['main_image'] ?>" alt="<?= $product['title'] ?>">
                    <?php else: ?>
                        <img src="assets/default-product.jpg" alt="Default Image">
                    <?php endif; ?>
                    <div class="product-info">
                        <h3><?= $product['title'] ?></h3>
                        <p class="product-price">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                        <p class="product-location"><?= $product['location'] ?></p>
                        <a href="products/detail.php?id=<?= $product['id'] ?>" class="btn-primary">Lihat Detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="view-all">
            <a href="products/index.php" class="btn-secondary">Lihat Semua Produk</a>
        </div>
    </section>
</div>

<?php
require_once 'includes/footer.php';
?>