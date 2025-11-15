<?php
// checkout.php — buka WhatsApp di tab baru tanpa mengganti halaman utama
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';
require_once 'cart_functions.php';

// Pastikan user login sebagai USER
if (empty($_SESSION['user'])) {
  $redirect = urlencode('cart.php');
  header("Location: admin_login.php?redirect={$redirect}&as=user");
  exit;
}

// Ambil cart
$id_cart = get_or_create_cart($koneksi);
if (!$id_cart) {
  header("Location: cart.php?msg=" . urlencode(json_encode([['type'=>'error','text'=>'Gagal mengakses keranjang.']]) ));
  exit;
}

// Ambil item keranjang
$items = get_cart_items($koneksi, $id_cart);
if (empty($items)) {
  header("Location: cart.php?msg=" . urlencode(json_encode([['type'=>'error','text'=>'Keranjang kosong.']]) ));
  exit;
}

// Rangkai teks pesanan
$parts = [];
foreach ($items as $it) {
  $name = trim($it['nama_produk'] ?? 'Produk');
  $qty = (int)($it['qty'] ?? 0);
  if ($qty <= 0) continue;
  $safeName = preg_replace("/[\r\n\t]+/", ' ', $name);
  $safeName = str_replace(',', ' ', $safeName);
  $parts[] = "{$safeName} {$qty}pcs";
}

if (empty($parts)) {
  header("Location: cart.php?msg=" . urlencode(json_encode([['type'=>'error','text'=>'Tidak ada item valid di keranjang.']]) ));
  exit;
}

if (count($parts) === 1) {
  $listText = $parts[0];
} else {
  $last = array_pop($parts);
  $listText = implode(', ', $parts) . ' dan ' . $last;
}

// Format pesan
$message = "kak saya ingin memesan " . $listText . ".";
$wa_number = "6287773456733";
$wa_url = "https://wa.me/{$wa_number}?text=" . urlencode($message);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menghubungkan ke WhatsApp...</title>
  <style>
    body {
      background: #f9fafb;
      font-family: 'Inter', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: #0f172a;
    }
    .box {
      background: #fff;
      padding: 30px 40px;
      border-radius: 16px;
      box-shadow: 0 6px 16px rgba(15,23,42,0.1);
      text-align: center;
      max-width: 400px;
    }
    .box h2 { margin-top: 0; color: #2563eb; }
    .btn {
      display: inline-block;
      margin-top: 18px;
      background: #25D366;
      color: white;
      padding: 10px 16px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.2s ease;
    }
    .btn:hover { background: #1ebe5d; }
    .note {
      margin-top: 10px;
      font-size: 14px;
      color: #475569;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2>Menghubungkan ke WhatsApp...</h2>
    <p>Jika WhatsApp tidak terbuka otomatis, klik tombol di bawah ini.</p>
    <a href="<?php echo htmlspecialchars($wa_url); ?>" target="_blank" class="btn">Buka WhatsApp</a>
    <p class="note">Tab baru akan terbuka untuk mengirim pesan pemesanan Anda.</p>
    <a href="cart.php" style="display:inline-block;margin-top:14px;font-size:14px;color:#2563eb;text-decoration:underline;">← Kembali ke keranjang</a>
  </div>

  <script>
    // Buka WhatsApp di tab baru secara otomatis
    window.addEventListener('load', () => {
      setTimeout(() => {
        window.open("<?php echo htmlspecialchars($wa_url); ?>", "_blank");
      }, 800);
    });
  </script>
</body>
</html>
