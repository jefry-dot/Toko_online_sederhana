<?php
require_once 'includes/header.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    $_SESSION['error'] = "Silakan login terlebih dahulu";
    redirect('auth/login.php');
}

$user_id = $_SESSION['user_id'];
$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$ad_id) {
    $_SESSION['error'] = "Iklan tidak ditemukan";
    redirect('my_ads.php');
}

// Ambil data iklan
$ad = getAdById($pdo, $ad_id);

// Cek apakah iklan ada dan milik user ini
if (!$ad || $ad['user_id'] != $user_id) {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk mengedit iklan ini";
    redirect('my_ads.php');
}

// Ambil gambar iklan
$ad_images = getAdImages($pdo, $ad_id);

$errors = [];
$success = false;

// Proses form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $location = sanitize($_POST['location'] ?? '');
    $category_id = $_POST['category_id'] ?? '';

    // Validasi
    if (empty($title)) {
        $errors[] = "Judul iklan harus diisi";
    }
    if (empty($price) || !is_numeric($price) || $price < 0) {
        $errors[] = "Harga harus berupa angka positif";
    }
    if (empty($category_id)) {
        $errors[] = "Kategori harus dipilih";
    }
    if (empty($location)) {
        $errors[] = "Lokasi harus diisi";
    }

    // Jika tidak ada error, update iklan
    if (empty($errors)) {
        try {
            // Update data iklan
            $stmt = $pdo->prepare("UPDATE ads SET category_id = ?, title = ?, description = ?, price = ?, location = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$category_id, $title, $description, $price, $location, $ad_id, $user_id]);

            // Upload gambar baru jika ada
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $upload_dir = 'uploads/';
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_type = $_FILES['images']['type'][$key];
                        $file_size = $_FILES['images']['size'][$key];

                        if (!in_array($file_type, $allowed_types)) {
                            $errors[] = "File harus berupa gambar (JPG, PNG, GIF)";
                            continue;
                        }

                        if ($file_size > $max_size) {
                            $errors[] = "Ukuran file maksimal 5MB";
                            continue;
                        }

                        $extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                        $new_filename = uniqid('img_') . '_' . time() . '.' . $extension;
                        $upload_path = $upload_dir . $new_filename;

                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            $stmt = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path) VALUES (?, ?)");
                            $stmt->execute([$ad_id, $new_filename]);
                        }
                    }
                }
            }

            // Hapus gambar jika diminta
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    $stmt = $pdo->prepare("SELECT image_path FROM ad_images WHERE id = ? AND ad_id = ?");
                    $stmt->execute([$image_id, $ad_id]);
                    $image = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($image) {
                        // Hapus file
                        $file_path = 'uploads/' . $image['image_path'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }

                        // Hapus dari database
                        $stmt = $pdo->prepare("DELETE FROM ad_images WHERE id = ?");
                        $stmt->execute([$image_id]);
                    }
                }
            }

            if (empty($errors)) {
                $success = true;
                $_SESSION['success'] = "Iklan berhasil diupdate!";

                // Refresh data
                $ad = getAdById($pdo, $ad_id);
                $ad_images = getAdImages($pdo, $ad_id);
            }
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Ambil semua kategori
$categories = getCategories($pdo);
?>

<div class="container">
    <div class="edit-iklan-wrapper">
        <h1><i class="fas fa-edit"></i> Edit Iklan</h1>
        <p class="subtitle">Perbarui informasi iklan Anda</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Iklan berhasil diupdate!
                <a href="ads/detail.php?id=<?= $ad_id ?>">Lihat Iklan</a> |
                <a href="my_ads.php">Kembali ke Daftar</a>
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

        <form method="POST" enctype="multipart/form-data" class="edit-form">
            <div class="form-group">
                <label for="title">Judul Iklan <span class="required">*</span></label>
                <input type="text"
                       id="title"
                       name="title"
                       value="<?= htmlspecialchars($ad['title']) ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="category_id">Kategori <span class="required">*</span></label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"
                                <?= $ad['category_id'] == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Harga (Rp) <span class="required">*</span></label>
                <input type="number"
                       id="price"
                       name="price"
                       value="<?= htmlspecialchars($ad['price']) ?>"
                       min="0"
                       step="1000"
                       required>
            </div>

            <div class="form-group">
                <label for="location">Lokasi <span class="required">*</span></label>
                <input type="text"
                       id="location"
                       name="location"
                       value="<?= htmlspecialchars($ad['location']) ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description"
                          name="description"
                          rows="6"><?= htmlspecialchars($ad['description']) ?></textarea>
            </div>

            <!-- Gambar yang ada -->
            <?php if (!empty($ad_images)): ?>
                <div class="form-group">
                    <label>Gambar Saat Ini</label>
                    <div class="current-images">
                        <?php foreach ($ad_images as $image): ?>
                            <div class="image-item">
                                <img src="uploads/<?= htmlspecialchars($image['image_path']) ?>" alt="Image">
                                <label class="delete-checkbox">
                                    <input type="checkbox"
                                           name="delete_images[]"
                                           value="<?= $image['id'] ?>">
                                    <span>Hapus</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Upload gambar baru -->
            <div class="form-group">
                <label for="images">Tambah Foto Baru (Opsional)</label>
                <input type="file"
                       id="images"
                       name="images[]"
                       accept="image/*"
                       multiple
                       onchange="previewNewImages(event)">
                <small>Format: JPG, PNG, GIF | Maksimal 5MB per file</small>
                <div id="new-image-preview" class="image-preview"></div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="my_ads.php" class="btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.edit-iklan-wrapper {
    max-width: 800px;
    margin: 40px auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.edit-iklan-wrapper h1 {
    color: #333;
    margin-bottom: 10px;
    font-size: 2em;
    display: flex;
    align-items: center;
    gap: 12px;
}

.edit-iklan-wrapper h1 i {
    color: #667eea;
}

.subtitle {
    color: #666;
    margin-bottom: 30px;
    font-size: 1.1em;
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

.alert-success a {
    color: #155724;
    font-weight: bold;
    text-decoration: underline;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-error ul {
    margin: 10px 0 0 20px;
}

.edit-form {
    margin-top: 30px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.required {
    color: #e74c3c;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1em;
    transition: border-color 0.3s;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
}

.form-group input[type="file"] {
    padding: 10px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    width: 100%;
    cursor: pointer;
}

.form-group small {
    display: block;
    margin-top: 8px;
    color: #999;
    font-size: 0.85em;
}

.current-images {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.image-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #ddd;
}

.image-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    display: block;
}

.delete-checkbox {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(244, 67, 54, 0.9);
    color: white;
    padding: 8px;
    text-align: center;
    font-size: 0.85em;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.delete-checkbox input {
    cursor: pointer;
}

.image-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.image-preview img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #ddd;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 35px;
    padding-top: 25px;
    border-top: 2px solid #f0f0f0;
}

.btn-primary, .btn-secondary {
    padding: 14px 30px;
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
    flex: 1;
    justify-content: center;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #f0f0f0;
    color: #666;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

@media (max-width: 768px) {
    .edit-iklan-wrapper {
        margin: 20px;
        padding: 25px;
    }

    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
function previewNewImages(event) {
    const files = event.target.files;
    const preview = document.getElementById('new-image-preview');
    preview.innerHTML = '';

    if (files.length > 5) {
        alert('Maksimal 5 foto baru');
        event.target.value = '';
        return;
    }

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();

        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        }

        reader.readAsDataURL(file);
    }
}
</script>

<?php
require_once 'includes/footer.php';
?>
