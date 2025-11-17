<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Online</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">TokoOnline</a>
                </div>
                
                <div class="nav-menu">
                    <a href="index.php">Beranda</a>
                    
                    <?php $categories = getCategories($pdo); ?>
                    <?php if ($categories): ?>
                    <div class="dropdown">
                        <a href="#" class="dropbtn">Kategori <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-content">
                            <?php foreach ($categories as $category): ?>
                                <a href="products/index.php?category=<?= $category['id'] ?>">
                                    <?= $category['name'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <a href="products/index.php">Semua Produk</a>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i> Keranjang</a>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <a href="#" class="dropbtn"><?= $_SESSION['user_name'] ?> <i class="fas fa-user"></i></a>
                            <div class="dropdown-content">
                                <a href="admin/index.php">Dashboard</a>
                                <a href="auth/logout.php">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="auth/login.php">Login</a>
                        <a href="auth/register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main></main>