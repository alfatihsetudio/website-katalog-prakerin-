<?php
// cart.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';
require_once 'cart_functions.php';

// ⚠️ jangan include templates/header.php DI SINI
// karena nanti kita akan include setelah semua redirect selesai

// dapatkan atau buat cart untuk session/user saat ini
$id_cart = get_or_create_cart($koneksi);
if (!$id_cart) {
  // belum ada output apa pun, jadi aman pakai header
  header("Location: index.php?err_cart=1");
  exit;
}

$messages = [];

// HANDLE POST ACTIONS: update qty, remove item, clear cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // --------- PENTING: pastikan hanya USER (bukan guest atau admin saja) boleh melakukan aksi tulis ----------
  if (empty($_SESSION['user'])) {
    // arahkan ke halaman login user; admin saja juga tidak cukup
    // redirect kembali ke cart.php setelah login
    $redirect = urlencode('cart.php');
    header("Location: admin_login.php?redirect={$redirect}&as=user");
    exit;
  }
  // --------------------------------------------------------------------------------------------------------

  // update qty action
  if (isset($_POST['action']) && $_POST['action'] === 'update_qty' && isset($_POST['id_item'])) {
    $id_item = (int) ($_POST['id_item'] ?? 0);
    $qty = (int) ($_POST['qty'] ?? 1);
    if ($id_item > 0 && $qty > 0) {
      $stmt = $koneksi->prepare("UPDATE cart_items SET qty = ? WHERE id_item = ? AND id_cart = ?");
      if ($stmt) {
        $stmt->bind_param("iii", $qty, $id_item, $id_cart);
        if ($stmt->execute()) {
          $messages[] = ['type' => 'success', 'text' => 'Jumlah item diperbarui.'];
        } else {
          $messages[] = ['type' => 'error', 'text' => 'Gagal mengupdate jumlah.'];
        }
        $stmt->close();
      } else {
        $messages[] = ['type' => 'error', 'text' => 'Gagal menyiapkan query update.'];
      }
    } else {
      $messages[] = ['type' => 'error', 'text' => 'Input jumlah tidak valid.'];
    }
    // redirect untuk mencegah resubmit
    header("Location: cart.php?msg=" . urlencode(json_encode($messages)));
    exit;
  }

  // remove item action
  if (isset($_POST['action']) && $_POST['action'] === 'remove_item' && isset($_POST['id_item'])) {
    $id_item = (int) ($_POST['id_item'] ?? 0);
    if ($id_item > 0) {
      $stmt = $koneksi->prepare("DELETE FROM cart_items WHERE id_item = ? AND id_cart = ?");
      if ($stmt) {
        $stmt->bind_param("ii", $id_item, $id_cart);
        if ($stmt->execute()) {
          $messages[] = ['type' => 'success', 'text' => 'Item dihapus dari keranjang.'];
        } else {
          $messages[] = ['type' => 'error', 'text' => 'Gagal menghapus item.'];
        }
        $stmt->close();
      } else {
        $messages[] = ['type' => 'error', 'text' => 'Gagal menyiapkan query hapus.'];
      }
    } else {
      $messages[] = ['type' => 'error', 'text' => 'ID item tidak valid.'];
    }
    header("Location: cart.php?msg=" . urlencode(json_encode($messages)));
    exit;
  }

  // clear cart action
  if (isset($_POST['action']) && $_POST['action'] === 'clear_cart') {
    $stmt = $koneksi->prepare("DELETE FROM cart_items WHERE id_cart = ?");
    if ($stmt) {
      $stmt->bind_param("i", $id_cart);
      if ($stmt->execute()) {
        $messages[] = ['type' => 'success', 'text' => 'Keranjang dibersihkan.'];
      } else {
        $messages[] = ['type' => 'error', 'text' => 'Gagal membersihkan keranjang.'];
      }
      $stmt->close();
    } else {
      $messages[] = ['type' => 'error', 'text' => 'Gagal menyiapkan query pembersihan.'];
    }
    header("Location: cart.php?msg=" . urlencode(json_encode($messages)));
    exit;
  }
}

// Menampilkan pesan dari redirect (jika ada)
if (isset($_GET['msg'])) {
  $decoded = json_decode($_GET['msg'], true);
  if (is_array($decoded)) {
    foreach ($decoded as $m) $messages[] = $m;
  }
}

// Ambil item cart
$items = get_cart_items($koneksi, $id_cart);
$total = cart_total($koneksi, $id_cart);

// ✅ sekarang aman include header — tidak akan ganggu header() sebelumnya
require_once 'templates/header.php';
?>
<!-- (Selanjutnya HTML/CSS dari cart.php seperti sebelumnya) -->
<style>
/* Styling konsisten dengan tampilan lain */
.cart-wrap {
  max-width: 1000px;
  margin: 36px auto;
  padding: 0 16px;
}

.cart-card {
  background: #fff;
  padding: 18px;
  border-radius: 12px;
  box-shadow: 0 6px 18px rgba(15,23,42,0.04);
}

.cart-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 12px;
}

.cart-table th, .cart-table td {
  padding: 12px 10px;
  text-align: left;
  border-bottom: 1px solid #eef2f7;
  vertical-align: middle;
  font-size: 14px;
  color: #0f172a;
}

.cart-table th { font-weight: 700; color: #334155; }

.product-thumb {
  width: 80px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e6edf3;
}

.qty-input {
  width: 90px;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #e6edf3;
}

.btn-primary {
  background: #2563eb;
  color: #fff;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
}
.btn-danger {
  background: #ef4444;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
}
.btn-muted {
  background: #9ca3af;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
}

.summary {
  margin-top: 18px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
}
.summary .total { font-size:18px; font-weight:800; color:#0f172a; }
.actions-right { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

/* notifikasi */
.notice { padding:12px;border-radius:10px;margin-bottom:14px; font-size:14px; }
.notice.success { background:#ecfdf5;border:1px solid #bbf7d0;color:#065f46; }
.notice.error   { background:#fff1f2;border:1px solid #fecaca;color:#991b1b; }

/* responsive */
@media (max-width:720px) {
  .product-thumb { width:64px; height:48px; }
  .qty-input { width:70px; }
  .cart-table th:nth-child(3), .cart-table td:nth-child(3) { display:none; } /* sembunyikan kolom harga di mobile */
}
</style>

<main class="cart-wrap">
  <div class="cart-card">
    <h2 style="margin:0 0 8px;">Keranjang Belanja</h2>
    <p style="margin:0 0 12px;color:#475569;">Berikut item yang kamu tambahkan ke keranjang.</p>

    <?php foreach ($messages as $m): ?>
      <?php if ($m['type'] === 'success'): ?>
        <div class="notice success"><?php echo htmlspecialchars($m['text']); ?></div>
      <?php else: ?>
        <div class="notice error"><?php echo htmlspecialchars($m['text']); ?></div>
      <?php endif; ?>
    <?php endforeach; ?>

    <?php if (empty($items)): ?>
      <p style="padding:18px 0;color:#64748b;">Keranjang masih kosong. <a href="index.php" style="color:#2563eb;text-decoration:underline;">Lanjutkan belanja</a></p>
    <?php else: ?>
      <table class="cart-table">
        <thead>
          <tr>
            <th>Produk</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it):
            $subtotal = (float)$it['qty'] * (float)$it['price'];
            $foto = $it['foto'] ? 'media/' . $it['foto'] : 'media/no-image.png';
          ?>
          <tr>
            <td><img src="<?php echo htmlspecialchars($foto); ?>" class="product-thumb" alt=""></td>
            <td><?php echo htmlspecialchars($it['nama_produk'] ?? 'Produk'); ?></td>
            <td>Rp <?php echo number_format($it['price'],0,',','.'); ?></td>
            <td>
              <form method="post" style="display:inline-flex; gap:8px; align-items:center;">
                <input type="hidden" name="action" value="update_qty">
                <input type="hidden" name="id_item" value="<?php echo (int)$it['id_item']; ?>">
                <input type="number" name="qty" class="qty-input" value="<?php echo (int)$it['qty']; ?>" min="1">
                <button type="submit" class="btn-primary">Update</button>
              </form>
            </td>
            <td>Rp <?php echo number_format($subtotal,0,',','.'); ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Hapus item dari keranjang?');">
                <input type="hidden" name="action" value="remove_item">
                <input type="hidden" name="id_item" value="<?php echo (int)$it['id_item']; ?>">
                <button type="submit" class="btn-danger">Hapus</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="summary">
        <div class="total">Total: Rp <?php echo number_format($total,0,',','.'); ?></div>
        <div class="actions-right">
          <form method="post" onsubmit="return confirm('Kosongkan seluruh keranjang?');" style="display:inline;">
            <input type="hidden" name="action" value="clear_cart">
            <button type="submit" class="btn-muted">Kosongkan Keranjang</button>
          </form>
          <a href="index.php" class="btn-muted" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Lanjut Belanja</a>
          <!-- tombol checkout sederhana: kirim order via WA atau lanjut ke sistem checkout lain -->
          <?php
// Pastikan variabel $items sudah ada (hasil get_cart_items) dan $_SESSION['user'] tersedia
// ---------------------------
$wa_number = '6287773456733'; // tujuan WA (format internasional tanpa +)
$is_user = !empty($_SESSION['user']);
?>

<!-- Tempelkan data cart ke JS (safe) -->
<script>
  // data items diisi dari PHP — setiap item: {name: "...", qty: N}
  const cartItems = <?php
    // build minimal JSON array
    $jsItems = [];
    foreach ($items as $it) {
      $name = trim($it['nama_produk'] ?? 'Produk');
      $qty = (int)($it['qty'] ?? 0);
      if ($qty <= 0) continue;
      // escape untuk JSON
      $jsItems[] = ['name' => $name, 'qty' => $qty];
    }
    echo json_encode($jsItems, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
  ?>;
  const waNumber = "<?php echo $wa_number; ?>";
  const isUserLoggedIn = <?php echo $is_user ? 'true' : 'false'; ?>;
  const loginUrlForUser = "admin_login.php?redirect=cart.php&as=user";
  
  function openWhatsAppOrder() {
    if (!isUserLoggedIn) {
      // jika belum login, arahkan ke halaman login (mengganti halaman) — ini sesuai kebijakan fitur
      window.location.href = loginUrlForUser;
      return;
    }

    if (!Array.isArray(cartItems) || cartItems.length === 0) {
      alert('Keranjang kosong. Tambahkan produk terlebih dahulu.');
      return;
    }

    // build daftar seperti: "baju 10pcs dan spanduk 30pcs"
    const parts = cartItems.map(i => {
      // bersihkan nama dari baris baru
      const safeName = (i.name || '').replace(/[\r\n\t]+/g, ' ').replace(/,/g, ' ');
      return `${safeName} ${Number(i.qty)}pcs`;
    }).filter(Boolean);

    let listText = '';
    if (parts.length === 1) {
      listText = parts[0];
    } else if (parts.length > 1) {
      const last = parts.pop();
      listText = parts.join(', ') + ' dan ' + last;
    }

    const message = `kak saya ingin memesan ${listText}.`;
    const waUrl = `https://wa.me/${waNumber}?text=` + encodeURIComponent(message);

    // buka WA di tab baru (user gesture, seharusnya tidak diblokir)
    window.open(waUrl, '_blank');

    // opsional: beri notifikasi kecil di halaman (tidak mengganti halaman)
    const note = document.createElement('div');
    note.style.cssText = "position:fixed;bottom:18px;right:18px;background:#10b981;color:#fff;padding:10px 14px;border-radius:10px;box-shadow:0 6px 18px rgba(2,6,23,0.2);font-weight:700;z-index:9999;";
    note.textContent = 'WhatsApp terbuka di tab baru — silakan kirim pesanan.';
    document.body.appendChild(note);
    setTimeout(() => note.remove(), 4000);
  }
</script>

<!-- Tombol checkout baru (gantikan anchor lama) -->
<!-- Gunakan onclick => membuka WA di tab baru; tombol ini tidak mengubah halaman ini -->
<button type="button" class="btn-primary" onclick="openWhatsAppOrder()" title="Buka WhatsApp di tab baru untuk mengirim order">
  Lanjut ke Checkout
</button>

      </div>
    <?php endif; ?>

  </div>
</main>

<?php
require_once 'templates/footer.php';
?>
