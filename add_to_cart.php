<?php
// add_to_cart.php (require menggantikan file lama)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';
require_once 'cart_functions.php';

// hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

// jika bukan user yang login, arahkan ke login (user)
if (empty($_SESSION['user'])) {
  // catatan: admin saja tidak cukup, kita butuh session user
  $current = (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
  $redirect = urlencode($current);
  // as=user memberi tanda ke halaman login untuk membuka tab user
  header("Location: admin_login.php?redirect={$redirect}&as=user");
  exit;
}

// ambil input
$id_produk = isset($_POST['id_produk']) ? (int) $_POST['id_produk'] : 0;
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;
$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php';

// validasi sederhana
if ($id_produk <= 0 || $qty <= 0) {
  $sep = (strpos($redirect, '?') === false) ? '?' : '&';
  header('Location: ' . $redirect . $sep . 'added_cart=0');
  exit;
}

// dapatkan atau buat cart milik user (get_or_create_cart akan membuat cart yang terkait user)
$id_cart = get_or_create_cart($koneksi);
if (!$id_cart) {
  $sep = (strpos($redirect, '?') === false) ? '?' : '&';
  header('Location: ' . $redirect . $sep . 'added_cart=0');
  exit;
}

// jalankan tambah ke cart
$ok = add_to_cart($koneksi, $id_cart, $id_produk, $qty);

// redirect kembali dengan indikator
$sep = (strpos($redirect, '?') === false) ? '?' : '&';
if ($ok) {
  header('Location: ' . $redirect . $sep . 'added_cart=1');
} else {
  header('Location: ' . $redirect . $sep . 'added_cart=0');
}
exit;
