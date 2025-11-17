<?php
require_once 'includes/header.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    $_SESSION['error'] = "Silakan login terlebih dahulu";
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi
    if (empty($current_password)) {
        $errors[] = "Password lama harus diisi";
    }

    if (empty($new_password)) {
        $errors[] = "Password baru harus diisi";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Password baru minimal 6 karakter";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }

    // Jika tidak ada error, lanjutkan validasi password lama
    if (empty($errors)) {
        // Ambil password user dari database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cek apakah password lama benar
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Password lama tidak sesuai";
        } else {
            // Update password baru
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);

                $success = true;
            } catch (PDOException $e) {
                $errors[] = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="container">
    <div class="change-password-wrapper">
        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="success-card">
                <i class="fas fa-check-circle"></i>
                <h1>Password Berhasil Diubah!</h1>
                <p>Password Anda telah berhasil diupdate. Gunakan password baru untuk login berikutnya.</p>
                <div class="success-actions">
                    <a href="profile.php" class="btn-primary">
                        <i class="fas fa-user"></i> Kembali ke Profile
                    </a>
                    <a href="index.php" class="btn-secondary">
                        <i class="fas fa-home"></i> Ke Beranda
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Change Password Form -->
            <div class="form-card">
                <div class="form-header">
                    <i class="fas fa-key"></i>
                    <h1>Ganti Password</h1>
                    <p>Pastikan password baru Anda kuat dan mudah diingat</p>
                </div>

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

                <form method="POST" class="password-form">
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-lock"></i> Password Lama
                        </label>
                        <div class="input-wrapper">
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   placeholder="Masukkan password lama"
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('current_password')"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i> Password Baru
                        </label>
                        <div class="input-wrapper">
                            <input type="password"
                                   id="new_password"
                                   name="new_password"
                                   placeholder="Masukkan password baru"
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('new_password')"></i>
                        </div>
                        <small class="help-text">
                            <i class="fas fa-info-circle"></i> Minimal 6 karakter
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Konfirmasi Password Baru
                        </label>
                        <div class="input-wrapper">
                            <input type="password"
                                   id="confirm_password"
                                   name="confirm_password"
                                   placeholder="Masukkan ulang password baru"
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
                        </div>
                    </div>

                    <div class="password-tips">
                        <h3><i class="fas fa-lightbulb"></i> Tips Password Aman:</h3>
                        <ul>
                            <li>Gunakan kombinasi huruf besar dan kecil</li>
                            <li>Tambahkan angka dan simbol</li>
                            <li>Minimal 6 karakter (lebih panjang lebih baik)</li>
                            <li>Jangan gunakan informasi pribadi yang mudah ditebak</li>
                        </ul>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Ubah Password
                        </button>
                        <a href="profile.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.change-password-wrapper {
    max-width: 600px;
    margin: 40px auto;
}

.success-card,
.form-card {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.success-card {
    text-align: center;
}

.success-card i {
    font-size: 80px;
    color: #28a745;
    margin-bottom: 20px;
}

.success-card h1 {
    color: #333;
    margin-bottom: 15px;
    font-size: 2em;
}

.success-card p {
    color: #666;
    margin-bottom: 30px;
    font-size: 1.1em;
    line-height: 1.6;
}

.success-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.form-header {
    text-align: center;
    margin-bottom: 30px;
}

.form-header i {
    font-size: 60px;
    color: #667eea;
    margin-bottom: 15px;
}

.form-header h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 2em;
}

.form-header p {
    color: #666;
    font-size: 1em;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-error ul {
    margin: 10px 0 0 20px;
}

.password-form {
    margin-top: 25px;
}

.form-group {
    margin-bottom: 25px;
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

.input-wrapper {
    position: relative;
}

.form-group input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #999;
    transition: color 0.3s;
}

.toggle-password:hover {
    color: #667eea;
}

.help-text {
    display: block;
    margin-top: 6px;
    color: #666;
    font-size: 0.85em;
}

.help-text i {
    color: #667eea;
}

.password-tips {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #667eea;
    margin: 25px 0;
}

.password-tips h3 {
    color: #333;
    margin-bottom: 12px;
    font-size: 1em;
}

.password-tips h3 i {
    color: #ffc107;
    margin-right: 8px;
}

.password-tips ul {
    margin: 0;
    padding-left: 20px;
}

.password-tips li {
    color: #666;
    margin-bottom: 8px;
    font-size: 0.9em;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-primary,
.btn-secondary {
    flex: 1;
    padding: 14px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
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

@media (max-width: 768px) {
    .change-password-wrapper {
        margin: 20px;
    }

    .success-card,
    .form-card {
        padding: 25px;
    }

    .form-actions,
    .success-actions {
        flex-direction: column;
    }
}
</style>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.parentElement.querySelector('.toggle-password');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>
