    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>TokoOnline</h3>
                <p>Platform jual beli online terpercaya dengan berbagai produk berkualitas.</p>
            </div>
            <div class="footer-section">
                <h3>Kategori</h3>
                <ul>
                    <?php foreach (getCategories($pdo) as $category): ?>
                        <li><a href="<?= $base_url ?>ads/index.php?category=<?= $category['id'] ?>"><?= $category['name'] ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Kontak</h3>
                <p>Email: info@tokoonline.com</p>
                <p>Telepon: (021) 123-4567</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TokoOnline. All rights reserved.</p>
        </div>
    </footer>

    <script src="<?= $base_url ?>script.js"></script>
</body>
</html>