<?php
require_once '../includes/header.php';

// Ambil parameter dari URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;

// Ambil semua kategori
$categories = getCategories($pdo);

// Ambil iklan berdasarkan filter
$ads = getAds($pdo, $category_id, null, $search);

// Hitung total iklan
$total_ads = count($ads);

// Nama kategori yang dipilih
$selected_category_name = "Semua Iklan";
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $selected_category_name = $cat['name'];
            break;
        }
    }
}
?>

<div class="container">
    <!-- Header Section -->
    <div class="ads-header">
        <h1>📢 Katalog Iklan</h1>
        <p>Temukan berbagai penawaran menarik dari penjual terpercaya</p>
    </div>

    <!-- Search & Filter Section -->
    <div class="search-filter-section">
        <!-- Search Bar -->
        <form method="GET" class="search-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text"
                       name="search"
                       placeholder="Cari iklan..."
                       value="<?= htmlspecialchars($search ?? '') ?>">
                <?php if ($category_id): ?>
                    <input type="hidden" name="category" value="<?= $category_id ?>">
                <?php endif; ?>
                <button type="submit" class="btn-search">Cari</button>
            </div>
        </form>

        <!-- Category Filter -->
        <div class="category-filter">
            <h3><i class="fas fa-filter"></i> Filter Kategori:</h3>
            <div class="category-buttons">
                <a href="index.php" class="category-btn <?= !$category_id ? 'active' : '' ?>">
                    <i class="fas fa-th"></i> Semua
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="index.php?category=<?= $category['id'] ?>"
                       class="category-btn <?= $category_id == $category['id'] ? 'active' : '' ?>">
                        <?= $category['icon'] ? $category['icon'] : '<i class="fas fa-tag"></i>' ?>
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <h2><?= $selected_category_name ?></h2>
        <p><?= $total_ads ?> iklan ditemukan
            <?php if ($search): ?>
                untuk pencarian "<strong><?= htmlspecialchars($search) ?></strong>"
            <?php endif; ?>
        </p>
        <?php if ($category_id || $search): ?>
            <a href="index.php" class="btn-clear-filter">
                <i class="fas fa-times"></i> Clear Filter
            </a>
        <?php endif; ?>
    </div>

    <!-- Ads Grid -->
    <?php if ($total_ads > 0): ?>
        <div class="ads-grid">
            <?php foreach ($ads as $ad): ?>
                <a href="detail.php?id=<?= $ad['id'] ?>" class="ad-card">
                    <div class="ad-image">
                        <?php if ($ad['main_image']): ?>
                            <img src="../uploads/<?= htmlspecialchars($ad['main_image']) ?>"
                                 alt="<?= htmlspecialchars($ad['title']) ?>">
                        <?php else: ?>
                            <div class="ad-image-placeholder">
                                <?= $ad['category_icon'] ? $ad['category_icon'] : '📦' ?>
                            </div>
                        <?php endif; ?>
                        <span class="ad-category-badge">
                            <?= htmlspecialchars($ad['category_name']) ?>
                        </span>
                    </div>

                    <div class="ad-content">
                        <h3 class="ad-title"><?= htmlspecialchars($ad['title']) ?></h3>

                        <?php if ($ad['description']): ?>
                            <p class="ad-description">
                                <?= htmlspecialchars(substr($ad['description'], 0, 80)) ?>
                                <?= strlen($ad['description']) > 80 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>

                        <div class="ad-price">
                            Rp <?= number_format($ad['price'], 0, ',', '.') ?>
                        </div>

                        <div class="ad-meta">
                            <span class="ad-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= $ad['location'] ? htmlspecialchars($ad['location']) : 'Tidak disebutkan' ?>
                            </span>
                            <span class="ad-seller">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($ad['seller_name']) ?>
                            </span>
                        </div>

                        <div class="ad-footer">
                            <span class="ad-date">
                                <i class="fas fa-clock"></i>
                                <?= date('d M Y', strtotime($ad['created_at'])) ?>
                            </span>
                            <span class="btn-detail">
                                Lihat Detail <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h2>Tidak Ada Iklan</h2>
            <p>
                <?php if ($search): ?>
                    Tidak ada iklan yang cocok dengan pencarian Anda.
                <?php elseif ($category_id): ?>
                    Belum ada iklan di kategori ini.
                <?php else: ?>
                    Belum ada iklan yang tersedia saat ini.
                <?php endif; ?>
            </p>
            <?php if (isLoggedIn()): ?>
                <a href="../pasang_iklan.php" class="btn-primary">
                    <i class="fas fa-plus-circle"></i> Pasang Iklan Pertama
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.ads-header {
    text-align: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    margin-bottom: 30px;
}

.ads-header h1 {
    font-size: 2.5em;
    margin-bottom: 10px;
}

.ads-header p {
    font-size: 1.1em;
    opacity: 0.9;
}

.search-filter-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.search-form {
    margin-bottom: 25px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 600px;
    margin: 0 auto;
    position: relative;
}

.search-box i {
    position: absolute;
    left: 15px;
    color: #999;
}

.search-box input {
    flex: 1;
    padding: 12px 15px 12px 45px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
}

.btn-search {
    padding: 12px 25px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-search:hover {
    background: #5568d3;
}

.category-filter h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 1.1em;
}

.category-filter h3 i {
    color: #667eea;
}

.category-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.category-btn {
    padding: 10px 20px;
    background: #f0f0f0;
    border: 2px solid #ddd;
    border-radius: 25px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.category-btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
    transform: translateY(-2px);
}

.category-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.results-info {
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.results-info h2 {
    color: #333;
    font-size: 1.5em;
    margin: 0;
}

.results-info p {
    color: #666;
    margin: 5px 0 0 0;
}

.btn-clear-filter {
    padding: 8px 20px;
    background: #f44336;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9em;
    transition: all 0.3s;
}

.btn-clear-filter:hover {
    background: #d32f2f;
}

.ads-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.ad-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
    text-decoration: none;
    color: inherit;
    display: block;
}

.ad-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.ad-image {
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.ad-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ad-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
    color: white;
}

.ad-category-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: rgba(255,255,255,0.95);
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    color: #667eea;
}

.ad-content {
    padding: 20px;
}

.ad-title {
    color: #333;
    font-size: 1.3em;
    margin: 0 0 10px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ad-description {
    color: #666;
    font-size: 0.9em;
    margin: 10px 0;
    line-height: 1.5;
}

.ad-price {
    color: #e74c3c;
    font-size: 1.8em;
    font-weight: bold;
    margin: 15px 0;
}

.ad-meta {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin: 15px 0;
    padding: 15px 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.ad-meta span {
    color: #666;
    font-size: 0.85em;
    display: flex;
    align-items: center;
    gap: 5px;
}

.ad-meta i {
    color: #667eea;
}

.ad-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.ad-date {
    color: #999;
    font-size: 0.85em;
}

.btn-detail {
    color: #667eea;
    font-weight: 600;
    font-size: 0.9em;
}

.empty-state {
    background: white;
    padding: 80px 40px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 100px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h2 {
    color: #666;
    font-size: 2em;
    margin-bottom: 15px;
}

.empty-state p {
    color: #999;
    font-size: 1.1em;
    margin-bottom: 30px;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1em;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

@media (max-width: 768px) {
    .ads-header h1 {
        font-size: 2em;
    }

    .search-box {
        flex-direction: column;
    }

    .search-box input {
        width: 100%;
    }

    .results-info {
        flex-direction: column;
        align-items: flex-start;
    }

    .ads-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
require_once '../includes/footer.php';
?>
