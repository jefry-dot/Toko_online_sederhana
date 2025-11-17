<?php
require_once 'includes/header.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    $_SESSION['error'] = "Silakan login terlebih dahulu";
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User tidak ditemukan";
    redirect('auth/logout.php');
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');

    // Validasi
    if (empty($name)) {
        $errors[] = "Nama harus diisi";
    }

    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    } else {
        // Cek apakah email sudah digunakan oleh user lain
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah digunakan oleh user lain";
        }
    }

    // Jika tidak ada error, update data
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $user_id]);

            // Update session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            // Refresh data user
            $user['name'] = $name;
            $user['email'] = $email;

            $success = "Profil berhasil diupdate!";
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Hitung statistik user
$stmt = $pdo->prepare("SELECT COUNT(*) as total_ads FROM ads WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="profile-wrapper">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="profile-info">
                <h1><?= htmlspecialchars($user['name']) ?></h1>
                <p><?= htmlspecialchars($user['email']) ?></p>
                <small>Bergabung sejak <?= date('d M Y', strtotime($user['created_at'])) ?></small>
            </div>
        </div>

        <div class="profile-stats">
            <div class="stat-card">
                <i class="fas fa-bullhorn"></i>
                <div class="stat-info">
                    <h3><?= $stats['total_ads'] ?></h3>
                    <p>Total Iklan</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-alt"></i>
                <div class="stat-info">
                    <h3><?= date('d', strtotime($user['created_at'])) ?></h3>
                    <p>Hari Bergabung</p>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <!-- Edit Profil -->
            <div class="profile-section">
                <h2><i class="fas fa-user-edit"></i> Edit Profil</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <strong><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> Nama Lengkap
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="<?= htmlspecialchars($user['name']) ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="<?= htmlspecialchars($user['email']) ?>"
                               required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Keamanan -->
            <div class="profile-section">
                <h2><i class="fas fa-shield-alt"></i> Keamanan</h2>

                <div class="security-options">
                    <div class="security-item">
                        <div class="security-info">
                            <i class="fas fa-lock"></i>
                            <div>
                                <h3>Ganti Password</h3>
                                <p>Ubah password akun Anda untuk keamanan</p>
                            </div>
                        </div>
                        <a href="change_password.php" class="btn-secondary">
                            <i class="fas fa-key"></i> Ganti Password
                        </a>
                    </div>
                </div>
            </div>

            <!-- My Ads -->
            <div class="profile-section">
                <h2><i class="fas fa-list"></i> Iklan Saya</h2>

                <div class="my-ads-link">
                    <p>Kelola semua iklan yang Anda pasang</p>
                    <a href="my_ads.php" class="btn-secondary">
                        <i class="fas fa-external-link-alt"></i> Lihat Iklan Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-wrapper {
    max-width: 900px;
    margin: 40px auto;
}

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 30px;
    color: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.profile-avatar i {
    font-size: 100px;
    opacity: 0.9;
}

.profile-info h1 {
    margin: 0 0 10px 0;
    font-size: 2em;
}

.profile-info p {
    margin: 5px 0;
    opacity: 0.9;
}

.profile-info small {
    opacity: 0.7;
    font-size: 0.9em;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.stat-card i {
    font-size: 40px;
    color: #667eea;
}

.stat-info h3 {
    margin: 0;
    font-size: 2em;
    color: #333;
}

.stat-info p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9em;
}

.profile-content {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.profile-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.profile-section h2 {
    color: #333;
    margin-bottom: 25px;
    font-size: 1.5em;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-section h2 i {
    color: #667eea;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
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

.alert-error ul {
    margin: 10px 0 0 20px;
}

.profile-form {
    margin-top: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
    font-size: 1em;
}

.form-group label i {
    color: #667eea;
    width: 20px;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
}

.form-actions {
    margin-top: 25px;
    display: flex;
    gap: 15px;
}

.btn-primary, .btn-secondary {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

.security-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.security-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.security-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.security-info i {
    font-size: 30px;
    color: #667eea;
}

.security-info h3 {
    margin: 0;
    color: #333;
    font-size: 1.1em;
}

.security-info p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 0.9em;
}

.my-ads-link {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #667eea;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.my-ads-link p {
    margin: 0;
    color: #666;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }

    .security-item,
    .my-ads-link {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>

<?php
require_once 'includes/footer.php';
?>
