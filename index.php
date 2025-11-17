<?php
require_once 'includes/header.php';
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>Selamat Datang di TokoOnline</h1>
        <p>Jual Beli Mudah, Cepat & Terpercaya</p>
        <div class="hero-buttons">
            <a href="ads/index.php" class="btn-primary">
                <i class="fas fa-search"></i> Lihat Iklan
            </a>
            <?php if (isLoggedIn()): ?>
                <a href="pasang_iklan.php" class="btn-secondary-hero">
                    <i class="fas fa-plus-circle"></i> Pasang Iklan
                </a>
            <?php else: ?>
                <a href="auth/login.php" class="btn-secondary-hero">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <section class="categories-section">
        <h2><i class="fas fa-th-large"></i> Kategori</h2>
        <div class="categories-grid">
            <?php foreach (getCategories($pdo) as $category): ?>
                <a href="ads/index.php?category=<?= $category['id'] ?>" class="category-card">
                    <div class="category-icon">
                        <?= $category['icon'] ? $category['icon'] : '📦' ?>
                    </div>
                    <h3><?= $category['name'] ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="ads-section">
        <div class="section-header">
            <h2><i class="fas fa-bullhorn"></i> Iklan Terbaru</h2>
            <a href="ads/index.php" class="view-all-link">
                Lihat Semua <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="ads-grid">
            <?php $latest_ads = getAds($pdo, null, 6); ?>
            <?php if (!empty($latest_ads)): ?>
                <?php foreach ($latest_ads as $ad): ?>
                    <a href="ads/detail.php?id=<?= $ad['id'] ?>" class="ad-card-home">
                        <div class="ad-image-home">
                            <?php if ($ad['main_image']): ?>
                                <img src="uploads/<?= $ad['main_image'] ?>" alt="<?= $ad['title'] ?>">
                            <?php else: ?>
                                <div class="ad-placeholder-home">
                                    <?= $ad['category_icon'] ? $ad['category_icon'] : '📦' ?>
                                </div>
                            <?php endif; ?>
                            <span class="ad-badge"><?= htmlspecialchars($ad['category_name']) ?></span>
                        </div>
                        <div class="ad-content-home">
                            <h3><?= htmlspecialchars($ad['title']) ?></h3>
                            <p class="ad-price-home">Rp <?= number_format($ad['price'], 0, ',', '.') ?></p>
                            <div class="ad-meta-home">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ad['location']) ?></span>
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($ad['seller_name']) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-ads-home">
                    <i class="fas fa-inbox"></i>
                    <p>Belum ada iklan. Jadilah yang pertama pasang iklan!</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="pasang_iklan.php" class="btn-primary">
                            <i class="fas fa-plus-circle"></i> Pasang Iklan Pertama
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
.hero-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 25px;
}

.btn-secondary-hero {
    padding: 14px 30px;
    background: white;
    color: #667eea;
    border: 2px solid white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-secondary-hero:hover {
    background: transparent;
    color: white;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.section-header h2 {
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h2 i {
    color: #667eea;
}

.view-all-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
}

.view-all-link:hover {
    gap: 10px;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 20px;
}

.category-card {
    background: white;
    padding: 30px 20px;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.category-icon {
    font-size: 50px;
    margin-bottom: 15px;
}

.category-card h3 {
    color: #333;
    font-size: 1.1em;
    margin: 0;
}

.ads-section {
    margin-top: 50px;
}

.ads-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.ad-card-home {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
    text-decoration: none;
    color: inherit;
}

.ad-card-home:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.ad-image-home {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.ad-image-home img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ad-placeholder-home {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 70px;
    color: white;
}

.ad-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(255,255,255,0.95);
    padding: 5px 12px;
    border-radius: 15px;
    font-size: 0.8em;
    font-weight: 600;
    color: #667eea;
}

.ad-content-home {
    padding: 20px;
}

.ad-content-home h3 {
    color: #333;
    font-size: 1.2em;
    margin: 0 0 12px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.4em;
}

.ad-price-home {
    color: #e74c3c;
    font-size: 1.6em;
    font-weight: bold;
    margin: 12px 0;
}

.ad-meta-home {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid #eee;
}

.ad-meta-home span {
    color: #666;
    font-size: 0.85em;
    display: flex;
    align-items: center;
    gap: 6px;
}

.ad-meta-home i {
    color: #667eea;
}

.no-ads-home {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.no-ads-home i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.no-ads-home p {
    color: #999;
    font-size: 1.1em;
    margin-bottom: 25px;
}

@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }

    .ads-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>