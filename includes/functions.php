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
?>