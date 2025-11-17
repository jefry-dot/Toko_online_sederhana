<?php
// Deteksi root directory
$root_dir = dirname(__DIR__);

// Include files dengan path absolut
require_once $root_dir . '/config/database.php';
require_once $root_dir . '/includes/functions.php';

// Tentukan base URL untuk link yang dinamis
$current_dir = basename(getcwd());
$base_url = ($current_dir === 'Toko_online_sederhana') ? '' : '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Online</title>
    <link rel="stylesheet" href="<?= $base_url ?>style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="<?= $base_url ?>index.php">TokoOnline</a>
                </div>

                <div class="nav-menu">
                    <a href="<?= $base_url ?>index.php">Beranda</a>

                    <a href="<?= $base_url ?>ads/index.php"><i class="fas fa-bullhorn"></i> Katalog Iklan</a>

                    <?php $categories = getCategories($pdo); ?>
                    <?php if ($categories): ?>
                    <div class="dropdown">
                        <a href="#" class="dropbtn">Kategori <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-content">
                            <?php foreach ($categories as $category): ?>
                                <a href="<?= $base_url ?>ads/index.php?category=<?= $category['id'] ?>">
                                    <?= $category['icon'] ? $category['icon'] : '📦' ?>
                                    <?= $category['name'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (isLoggedIn()): ?>
                        <a href="<?= $base_url ?>pasang_iklan.php" class="btn-pasang-iklan">
                            <i class="fas fa-plus-circle"></i> Pasang Iklan
                        </a>
                        <div class="dropdown">
                            <a href="#" class="dropbtn"><?= $_SESSION['user_name'] ?> <i class="fas fa-user"></i></a>
                            <div class="dropdown-content">
                                <a href="<?= $base_url ?>profile.php"><i class="fas fa-user-circle"></i> Profile</a>
                                <a href="<?= $base_url ?>my_ads.php"><i class="fas fa-list"></i> Iklan Saya</a>
                                <a href="<?= $base_url ?>admin/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                <a href="<?= $base_url ?>auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= $base_url ?>auth/login.php">Login</a>
                        <a href="<?= $base_url ?>auth/register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main></main>