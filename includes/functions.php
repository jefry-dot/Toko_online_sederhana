<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProducts($pdo, $category_id = null, $limit = null) {
    $sql = "SELECT ads.*, categories.name as category_name,
                   (SELECT image_path FROM ad_images WHERE ad_id = ads.id LIMIT 1) as main_image
            FROM ads
            LEFT JOIN categories ON ads.category_id = categories.id";

    $params = [];

    if ($category_id) {
        $sql .= " WHERE ads.category_id = ?";
        $params[] = $category_id;
    }

    $sql .= " ORDER BY ads.created_at DESC";

    // LIMIT harus langsung dimasukkan ke SQL sebagai integer, tidak bisa di-bind
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil iklan
function getAds($pdo, $category_id = null, $limit = null, $search = null) {
    $sql = "SELECT ads.*,
                   categories.name as category_name,
                   categories.icon as category_icon,
                   users.name as seller_name,
                   (SELECT image_path FROM ad_images WHERE ad_id = ads.id LIMIT 1) as main_image
            FROM ads
            LEFT JOIN categories ON ads.category_id = categories.id
            LEFT JOIN users ON ads.user_id = users.id
            WHERE 1=1";

    $params = [];

    if ($category_id) {
        $sql .= " AND ads.category_id = ?";
        $params[] = $category_id;
    }

    if ($search) {
        $sql .= " AND (ads.title LIKE ? OR ads.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY ads.created_at DESC";

    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil detail iklan berdasarkan ID
function getAdById($pdo, $ad_id) {
    $sql = "SELECT ads.*,
                   categories.name as category_name,
                   categories.icon as category_icon,
                   users.name as seller_name,
                   users.email as seller_email
            FROM ads
            LEFT JOIN categories ON ads.category_id = categories.id
            LEFT JOIN users ON ads.user_id = users.id
            WHERE ads.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ad_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil semua gambar iklan
function getAdImages($pdo, $ad_id) {
    $stmt = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ? ORDER BY id ASC");
    $stmt->execute([$ad_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk mengambil iklan milik user tertentu
function getMyAds($pdo, $user_id) {
    $sql = "SELECT ads.*,
                   categories.name as category_name,
                   categories.icon as category_icon,
                   (SELECT image_path FROM ad_images WHERE ad_id = ads.id LIMIT 1) as main_image,
                   (SELECT COUNT(*) FROM ad_images WHERE ad_id = ads.id) as image_count
            FROM ads
            LEFT JOIN categories ON ads.category_id = categories.id
            WHERE ads.user_id = ?
            ORDER BY ads.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menghapus iklan
function deleteAd($pdo, $ad_id, $user_id) {
    // Cek apakah iklan milik user ini
    $stmt = $pdo->prepare("SELECT id FROM ads WHERE id = ? AND user_id = ?");
    $stmt->execute([$ad_id, $user_id]);

    if (!$stmt->fetch()) {
        return false;
    }

    // Hapus gambar dari folder uploads
    $images = getAdImages($pdo, $ad_id);
    foreach ($images as $image) {
        $file_path = dirname(__DIR__) . '/uploads/' . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Hapus iklan (gambar di database akan terhapus otomatis karena ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
    $stmt->execute([$ad_id]);

    return true;
}
?>