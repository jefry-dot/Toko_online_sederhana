<?php
require_once 'includes/header.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    $_SESSION['error'] = "Silakan login terlebih dahulu";
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Proses hapus iklan
if (isset($_GET['delete']) && isset($_GET['confirm'])) {
    $ad_id = (int)$_GET['delete'];

    if (deleteAd($pdo, $ad_id, $user_id)) {
        $_SESSION['success'] = "Iklan berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus iklan";
    }

    redirect('my_ads.php');
}

// Ambil semua iklan milik user
$my_ads = getMyAds($pdo, $user_id);
$total_ads = count($my_ads);
?>

<div class="container">
    <div class="my-ads-wrapper">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-list"></i> Iklan Saya</h1>
                <p>Kelola semua iklan yang Anda pasang</p>
            </div>
            <a href="pasang_iklan.php" class="btn-primary">
                <i class="fas fa-plus-circle"></i> Pasang Iklan Baru
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-cards">
            <div class="stat-card">
                <i class="fas fa-bullhorn"></i>
                <div>
                    <h3><?= $total_ads ?></h3>
                    <p>Total Iklan</p>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Ads List -->
        <?php if ($total_ads > 0): ?>
            <div class="ads-list">
                <?php foreach ($my_ads as $ad): ?>
                    <div class="ad-item">
                        <div class="ad-image-container">
                            <?php if ($ad['main_image']): ?>
                                <img src="uploads/<?= htmlspecialchars($ad['main_image']) ?>"
                                     alt="<?= htmlspecialchars($ad['title']) ?>">
                            <?php else: ?>
                                <div class="ad-placeholder">
                                    <?= $ad['category_icon'] ? $ad['category_icon'] : '📦' ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($ad['image_count'] > 1): ?>
                                <span class="image-count">
                                    <i class="fas fa-images"></i> <?= $ad['image_count'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="ad-info">
                            <div class="ad-main-info">
                                <span class="ad-category">
                                    <?= $ad['category_icon'] ? $ad['category_icon'] : '📦' ?>
                                    <?= htmlspecialchars($ad['category_name']) ?>
                                </span>
                                <h3><?= htmlspecialchars($ad['title']) ?></h3>
                                <?php if ($ad['description']): ?>
                                    <p class="ad-desc">
                                        <?= htmlspecialchars(substr($ad['description'], 0, 100)) ?>
                                        <?= strlen($ad['description']) > 100 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="ad-details">
                                <div class="ad-detail-item">
                                    <i class="fas fa-tag"></i>
                                    <span class="ad-price">Rp <?= number_format($ad['price'], 0, ',', '.') ?></span>
                                </div>
                                <div class="ad-detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?= htmlspecialchars($ad['location']) ?></span>
                                </div>
                                <div class="ad-detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?= date('d M Y, H:i', strtotime($ad['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="ad-actions">
                            <a href="ads/detail.php?id=<?= $ad['id'] ?>"
                               class="btn-action btn-view"
                               title="Lihat Iklan">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <a href="edit_iklan.php?id=<?= $ad['id'] ?>"
                               class="btn-action btn-edit"
                               title="Edit Iklan">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button onclick="confirmDelete(<?= $ad['id'] ?>, '<?= htmlspecialchars(addslashes($ad['title'])) ?>')"
                                    class="btn-action btn-delete"
                                    title="Hapus Iklan">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>Belum Ada Iklan</h2>
                <p>Anda belum memasang iklan apapun. Mulai pasang iklan pertama Anda sekarang!</p>
                <a href="pasang_iklan.php" class="btn-primary">
                    <i class="fas fa-plus-circle"></i> Pasang Iklan Pertama
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.my-ads-wrapper {
    max-width: 1000px;
    margin: 40px auto;
}

.page-header {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.page-header h1 {
    color: #333;
    font-size: 2em;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header h1 i {
    color: #667eea;
}

.page-header p {
    color: #666;
    margin: 0;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-card i {
    font-size: 40px;
    color: #667eea;
}

.stat-card h3 {
    font-size: 2.5em;
    margin: 0;
    color: #333;
}

.stat-card p {
    margin: 5px 0 0 0;
    color: #666;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.ads-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.ad-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: grid;
    grid-template-columns: 150px 1fr auto;
    gap: 25px;
    align-items: center;
    transition: all 0.3s;
}

.ad-item:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.ad-image-container {
    position: relative;
    width: 150px;
    height: 150px;
    border-radius: 8px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.ad-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ad-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    color: white;
}

.image-count {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8em;
}

.ad-info {
    flex: 1;
}

.ad-category {
    display: inline-block;
    background: #f0f0f0;
    color: #667eea;
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 600;
    margin-bottom: 10px;
}

.ad-info h3 {
    color: #333;
    font-size: 1.5em;
    margin: 10px 0;
}

.ad-desc {
    color: #666;
    margin: 10px 0;
    line-height: 1.6;
}

.ad-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 15px;
}

.ad-detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #666;
    font-size: 0.95em;
}

.ad-detail-item i {
    color: #667eea;
    width: 18px;
}

.ad-price {
    color: #e74c3c;
    font-weight: bold;
    font-size: 1.1em;
}

.ad-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-action {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 0.9em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
}

.btn-view {
    background: #667eea;
    color: white;
}

.btn-view:hover {
    background: #5568d3;
}

.btn-edit {
    background: #ffc107;
    color: #333;
}

.btn-edit:hover {
    background: #e0a800;
}

.btn-delete {
    background: #f44336;
    color: white;
}

.btn-delete:hover {
    background: #d32f2f;
}

.btn-primary {
    padding: 12px 25px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
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
    margin-bottom: 25px;
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
    line-height: 1.6;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .ad-item {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .ad-image-container {
        margin: 0 auto;
    }

    .ad-actions {
        flex-direction: row;
        width: 100%;
    }

    .btn-action {
        flex: 1;
    }
}
</style>

<script>
function confirmDelete(adId, adTitle) {
    if (confirm('Apakah Anda yakin ingin menghapus iklan "' + adTitle + '"?\n\nIklan yang sudah dihapus tidak bisa dikembalikan!')) {
        window.location.href = 'my_ads.php?delete=' + adId + '&confirm=1';
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>
