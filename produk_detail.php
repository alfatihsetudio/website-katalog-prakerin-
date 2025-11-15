<?php
// produk_detail.php
require_once 'koneksi.php';
include 'templates/header.php';

// Ambil id produk dari URL (safety cast)
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// ambil produk dengan prepared statement
$stmt = $koneksi->prepare("SELECT id_produk, nama_produk, deskripsi, foto, harga, bahan, min_order FROM produk WHERE id_produk = ? LIMIT 1");
if (!$stmt) {
  echo "<p style='text-align:center;margin:50px 0;font-size:18px;'>Terjadi kesalahan: " . htmlspecialchars($koneksi->error) . "</p>";
  include 'templates/footer.php';
  exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$r = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$r) {
  echo "<p style='text-align:center;margin:50px 0;font-size:18px;'>Produk tidak ditemukan!</p>";
  include 'templates/footer.php';
  exit;
}

// notifikasi after add_to_cart
$added_ok = isset($_GET['added_cart']) && $_GET['added_cart'] === '1';
$added_fail = isset($_GET['added_cart']) && $_GET['added_cart'] === '0';

// build safe redirect (relative path)
$current_request = $_SERVER['REQUEST_URI'];
$redirect_param = rawurlencode($current_request);
$login_url_for_user = "admin_login.php?redirect={$redirect_param}&as=user";
?>

<style>
/* menjaga tampilan konsisten dengan tema yang sudah ada */
.detail-container {
  margin: 36px auto;
  max-width: 1100px;
  display: flex;
  flex-wrap: wrap;
  gap: 32px;
  align-items: flex-start;
  padding: 0 16px;
}

.detail-image {
  flex: 1;
  min-width: 320px;
  max-width: 520px;
}
.detail-image img {
  width: 100%;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 3px 8px rgba(0,0,0,0.06);
  object-fit: cover;
}

.detail-info {
  flex: 1;
  min-width: 320px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.detail-info h2 {
  margin: 0;
  font-size: 28px;
  font-weight: 700;
  color: #0f172a;
}
.detail-info .price {
  font-size: 22px;
  font-weight: 700;
  color: #2563eb;
  margin-bottom: 6px;
}

.detail-meta p {
  margin: 6px 0;
  font-size: 15px;
  color: #334155;
}
.detail-meta strong { color: #0b1220; }

.detail-description {
  margin-top: 12px;
  padding: 14px;
  background: #f8fafc;
  border-radius: 10px;
  border: 1px solid #e6edf3;
  font-size: 15px;
  line-height: 1.6;
  color: #334155;
}

/* tombol */
.actions {
  display:flex;
  gap:12px;
  align-items:center;
  margin-top:10px;
  flex-wrap:wrap;
}

.btn-wa {
  background: #25D366;
  color: white;
  padding: 10px 18px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: background .15s;
  border: none;
  cursor: pointer;
}
.btn-wa:hover { background: #1ebe5d; }

.btn-cart {
  background: #1f6feb;
  color: #fff;
  padding: 10px 18px;
  border-radius: 10px;
  border: none;
  font-weight: 700;
  cursor: pointer;
  transition: background .15s;
  text-decoration: none;
  display:inline-flex;
  align-items:center;
  gap:8px;
}
.btn-cart:hover { background: #174fc4; }

.input-qty {
  width:80px;
  padding:8px 10px;
  border-radius:8px;
  border:1px solid #e6edf3;
  font-size:14px;
}

/* hint for guest */
.login-hint {
  font-size:14px;
  color:#475569;
  margin-top:6px;
}

/* notifikasi */
.notice { padding:12px;border-radius:10px;margin-bottom:14px; font-size:14px; }
.notice.success { background:#ecfdf5;border:1px solid #bbf7d0;color:#065f46; }
.notice.error   { background:#fff1f2;border:1px solid #fecaca;color:#991b1b; }

/* responsive */
@media (max-width: 880px) {
  .detail-container { flex-direction: column; }
  .detail-image, .detail-info { max-width: 100%; }
}
</style>

<main>
  <div class="detail-container">
    <div style="flex-basis:100%; max-width:1100px;">
      <?php if ($added_ok): ?>
        <div class="notice success">Produk berhasil ditambahkan ke keranjang. <a href="cart.php" style="text-decoration:underline;color:#065f46;">Lihat Keranjang</a></div>
      <?php elseif ($added_fail): ?>
        <div class="notice error">Gagal menambahkan produk ke keranjang. Coba lagi.</div>
      <?php endif; ?>
    </div>

    <!-- GAMBAR PRODUK -->
    <div class="detail-image">
      <?php
        $fotoPath = 'media/' . ($r['foto'] ?: 'no-image.png');
      ?>
      <img src="<?php echo htmlspecialchars($fotoPath); ?>" alt="<?php echo htmlspecialchars($r['nama_produk']); ?>">
    </div>

    <!-- DETAIL PRODUK -->
    <div class="detail-info">
      <h2><?php echo htmlspecialchars($r['nama_produk']); ?></h2>
      <div class="price">Rp <?php echo number_format($r['harga'], 0, ',', '.'); ?> / pcs</div>

      <div class="detail-meta">
        <p><strong>Bahan:</strong> <?php echo $r['bahan'] ? htmlspecialchars($r['bahan']) : '-'; ?></p>
        <p><strong>Min. Order:</strong> <?php echo $r['min_order'] ? (int)$r['min_order'] : 1; ?> pcs</p>
      </div>

      <div class="detail-description">
        <?php echo $r['deskripsi'] ? nl2br(htmlspecialchars($r['deskripsi'])) : 'Belum ada deskripsi.'; ?>
      </div>

      <div class="actions">
        <?php if (!empty($_SESSION['user'])): ?>
          <!-- USER logged in: tampilkan form add_to_cart -->
          <form method="post" action="add_to_cart.php" style="display:inline-flex; align-items:center; gap:8px;">
            <input type="hidden" name="id_produk" value="<?php echo (int)$r['id_produk']; ?>">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <input type="number" name="qty" class="input-qty" value="<?php echo max(1, (int)$r['min_order']); ?>" min="<?php echo max(1, (int)$r['min_order']); ?>">
            <button type="submit" class="btn-cart">🛒 Tambah ke Keranjang</button>
          </form>

        <?php else: ?>
          <!-- GUEST: arahkan ke login user -->
          <a href="<?php echo $login_url_for_user; ?>" class="btn-cart" title="Harus login untuk menambah ke keranjang">🛒 Tambah ke Keranjang</a>
          <div class="login-hint">Harap <a href="<?php echo $login_url_for_user; ?>" style="color:#2563eb;text-decoration:underline;">login sebagai user</a> untuk dapat menambahkan produk ke keranjang.</div>
        <?php endif; ?>

        <!-- Tombol Tanya CS via WA -->
        <a class="btn-wa" target="_blank"
          href="https://wa.me/6287773456733?text=<?php echo urlencode('Halo, saya mau tanya tentang ' . $r['nama_produk']); ?>">
          <img src="https://img.icons8.com/ios-filled/18/ffffff/whatsapp.png" alt="WA"> Tanya CS
        </a>

        <!-- Link cepat lihat keranjang -->
        <a href="cart.php" class="btn-cart" style="background:#10b981;">Lihat Keranjang</a>
      </div>
    </div>
  </div>
</main>

<?php include 'templates/footer.php'; ?>
