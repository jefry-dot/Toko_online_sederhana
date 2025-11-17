<?php
require_once '../includes/header.php';

// Ambil ID iklan dari URL
$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$ad_id) {
    $_SESSION['error'] = "Iklan tidak ditemukan";
    redirect('index.php');
}

// Ambil data iklan
$ad = getAdById($pdo, $ad_id);

if (!$ad) {
    $_SESSION['error'] = "Iklan tidak ditemukan";
    redirect('index.php');
}

// Ambil semua gambar iklan
$images = getAdImages($pdo, $ad_id);

// Ambil iklan terkait (kategori yang sama)
$related_ads = getAds($pdo, $ad['category_id'], 4);
// Hapus iklan saat ini dari related ads
$related_ads = array_filter($related_ads, function($item) use ($ad_id) {
    return $item['id'] != $ad_id;
});
?>

<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="../index.php"><i class="fas fa-home"></i> Beranda</a>
        <i class="fas fa-chevron-right"></i>
        <a href="index.php">Katalog Iklan</a>
        <i class="fas fa-chevron-right"></i>
        <a href="index.php?category=<?= $ad['category_id'] ?>"><?= htmlspecialchars($ad['category_name']) ?></a>
        <i class="fas fa-chevron-right"></i>
        <span><?= htmlspecialchars($ad['title']) ?></span>
    </div>

    <div class="detail-wrapper">
        <!-- Main Content -->
        <div class="detail-main">
            <!-- Image Gallery -->
            <div class="image-gallery">
                <?php if (!empty($images)): ?>
                    <div class="main-image">
                        <img src="../uploads/<?= htmlspecialchars($images[0]['image_path']) ?>"
                             alt="<?= htmlspecialchars($ad['title']) ?>"
                             id="mainImage">
                    </div>

                    <?php if (count($images) > 1): ?>
                        <div class="thumbnail-container">
                            <?php foreach ($images as $index => $image): ?>
                                <img src="../uploads/<?= htmlspecialchars($image['image_path']) ?>"
                                     alt="Thumbnail <?= $index + 1 ?>"
                                     class="thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                     onclick="changeImage('../uploads/<?= htmlspecialchars($image['image_path']) ?>', this)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="main-image placeholder">
                        <div class="placeholder-icon">
                            <?= $ad['category_icon'] ? $ad['category_icon'] : '📦' ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ad Details -->
            <div class="ad-details-section">
                <div class="category-badge">
                    <?= $ad['category_icon'] ? $ad['category_icon'] : '📦' ?>
                    <?= htmlspecialchars($ad['category_name']) ?>
                </div>

                <h1 class="ad-title"><?= htmlspecialchars($ad['title']) ?></h1>

                <div class="price-section">
                    <div class="price">Rp <?= number_format($ad['price'], 0, ',', '.') ?></div>
                    <div class="ad-info">
                        <span>
                            <i class="fas fa-map-marker-alt"></i>
                            <?= $ad['location'] ? htmlspecialchars($ad['location']) : 'Tidak disebutkan' ?>
                        </span>
                        <span>
                            <i class="fas fa-clock"></i>
                            <?= date('d M Y, H:i', strtotime($ad['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <div class="description-section">
                    <h2><i class="fas fa-align-left"></i> Deskripsi</h2>
                    <div class="description-content">
                        <?php if ($ad['description']): ?>
                            <?= nl2br(htmlspecialchars($ad['description'])) ?>
                        <?php else: ?>
                            <p class="no-description">Tidak ada deskripsi untuk iklan ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="detail-sidebar">
            <!-- Seller Info -->
            <div class="seller-card">
                <h3><i class="fas fa-user-circle"></i> Informasi Penjual</h3>

                <div class="seller-info">
                    <div class="seller-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="seller-details">
                        <h4><?= htmlspecialchars($ad['seller_name']) ?></h4>
                        <p>
                            <i class="fas fa-envelope"></i>
                            <?= htmlspecialchars($ad['seller_email']) ?>
                        </p>
                    </div>
                </div>

                <div class="contact-buttons">
                    <a href="mailto:<?= htmlspecialchars($ad['seller_email']) ?>"
                       class="btn-contact btn-email">
                        <i class="fas fa-envelope"></i> Email Penjual
                    </a>

                    <?php if (isLoggedIn() && $_SESSION['user_id'] == $ad['user_id']): ?>
                        <a href="../edit_iklan.php?id=<?= $ad['id'] ?>"
                           class="btn-contact btn-edit">
                            <i class="fas fa-edit"></i> Edit Iklan
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Safety Tips -->
            <div class="safety-tips">
                <h3><i class="fas fa-shield-alt"></i> Tips Keamanan</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Bertemu di tempat umum</li>
                    <li><i class="fas fa-check"></i> Periksa barang sebelum membeli</li>
                    <li><i class="fas fa-check"></i> Jangan transfer sebelum melihat barang</li>
                    <li><i class="fas fa-check"></i> Waspadai harga yang terlalu murah</li>
                </ul>
            </div>

            <!-- Share -->
            <div class="share-section">
                <h3><i class="fas fa-share-alt"></i> Bagikan Iklan</h3>
                <div class="share-buttons">
                    <a href="https://wa.me/?text=<?= urlencode($ad['title'] . ' - ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>"
                       target="_blank"
                       class="btn-share btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <button onclick="copyLink()" class="btn-share btn-copy">
                        <i class="fas fa-link"></i> Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Ads -->
    <?php if (!empty($related_ads)): ?>
        <div class="related-section">
            <h2><i class="fas fa-tags"></i> Iklan Terkait</h2>
            <div class="related-grid">
                <?php foreach (array_slice($related_ads, 0, 3) as $related): ?>
                    <a href="detail.php?id=<?= $related['id'] ?>" class="related-card">
                        <div class="related-image">
                            <?php if ($related['main_image']): ?>
                                <img src="../uploads/<?= htmlspecialchars($related['main_image']) ?>"
                                     alt="<?= htmlspecialchars($related['title']) ?>">
                            <?php else: ?>
                                <div class="related-placeholder">
                                    <?= $related['category_icon'] ? $related['category_icon'] : '📦' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="related-content">
                            <h4><?= htmlspecialchars($related['title']) ?></h4>
                            <div class="related-price">
                                Rp <?= number_format($related['price'], 0, ',', '.') ?>
                            </div>
                            <div class="related-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($related['location']) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.breadcrumb {
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    font-size: 0.9em;
}

.breadcrumb a {
    color: #667eea;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb i.fa-chevron-right {
    color: #999;
    font-size: 0.8em;
}

.breadcrumb span {
    color: #666;
}

.detail-wrapper {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
    margin-bottom: 40px;
}

.detail-main {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.image-gallery {
    margin-bottom: 30px;
}

.main-image {
    width: 100%;
    height: 450px;
    border-radius: 12px;
    overflow: hidden;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.main-image.placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.placeholder-icon {
    font-size: 120px;
    color: white;
}

.thumbnail-container {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    overflow-x: auto;
}

.thumbnail {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    object-fit: cover;
    cursor: pointer;
    border: 3px solid transparent;
    transition: all 0.3s;
}

.thumbnail:hover,
.thumbnail.active {
    border-color: #667eea;
}

.category-badge {
    display: inline-block;
    background: #f0f0f0;
    color: #667eea;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 600;
    margin-bottom: 15px;
}

.ad-title {
    color: #333;
    font-size: 2em;
    margin: 15px 0 20px 0;
    line-height: 1.3;
}

.price-section {
    padding: 20px 0;
    border-top: 2px solid #f0f0f0;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 30px;
}

.price {
    color: #e74c3c;
    font-size: 2.5em;
    font-weight: bold;
    margin-bottom: 15px;
}

.ad-info {
    display: flex;
    gap: 25px;
    flex-wrap: wrap;
}

.ad-info span {
    color: #666;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ad-info i {
    color: #667eea;
}

.description-section h2 {
    color: #333;
    font-size: 1.5em;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.description-section h2 i {
    color: #667eea;
}

.description-content {
    color: #666;
    line-height: 1.8;
    font-size: 1em;
}

.no-description {
    color: #999;
    font-style: italic;
}

.detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.seller-card,
.safety-tips,
.share-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.seller-card h3,
.safety-tips h3,
.share-section h3 {
    color: #333;
    font-size: 1.2em;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.seller-card h3 i,
.safety-tips h3 i,
.share-section h3 i {
    color: #667eea;
}

.seller-info {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.seller-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 30px;
}

.seller-details h4 {
    color: #333;
    margin-bottom: 8px;
}

.seller-details p {
    color: #666;
    font-size: 0.9em;
    display: flex;
    align-items: center;
    gap: 6px;
}

.contact-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-contact {
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-email {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-email:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-edit {
    background: #f0f0f0;
    color: #333;
}

.btn-edit:hover {
    background: #e0e0e0;
}

.safety-tips ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.safety-tips li {
    color: #666;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.safety-tips li:last-child {
    border-bottom: none;
}

.safety-tips li i {
    color: #28a745;
}

.share-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-share {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    text-align: center;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-whatsapp {
    background: #25D366;
    color: white;
}

.btn-whatsapp:hover {
    background: #20ba5a;
}

.btn-copy {
    background: #f0f0f0;
    color: #333;
}

.btn-copy:hover {
    background: #e0e0e0;
}

.related-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.related-section h2 {
    color: #333;
    font-size: 1.8em;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.related-section h2 i {
    color: #667eea;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.related-card {
    background: #f8f9fa;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s;
}

.related-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.related-image {
    width: 100%;
    height: 180px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.related-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
    color: white;
}

.related-content {
    padding: 15px;
}

.related-content h4 {
    color: #333;
    margin: 0 0 10px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-price {
    color: #e74c3c;
    font-size: 1.3em;
    font-weight: bold;
    margin: 10px 0;
}

.related-location {
    color: #666;
    font-size: 0.9em;
}

@media (max-width: 1024px) {
    .detail-wrapper {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .main-image {
        height: 300px;
    }

    .ad-title {
        font-size: 1.5em;
    }

    .price {
        font-size: 2em;
    }

    .related-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function changeImage(src, element) {
    document.getElementById('mainImage').src = src;

    // Update active thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}

function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        alert('Link berhasil disalin!');
    }, function() {
        alert('Gagal menyalin link');
    });
}
</script>

<?php
require_once '../includes/footer.php';
?>
