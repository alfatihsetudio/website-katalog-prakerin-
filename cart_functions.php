<?php
// cart_functions.php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Cari cart yang aktif untuk session: prioritas id_user (login) lalu session_id()
 * Jika tidak ada, buat baru dan kembalikan id_cart.
 */
function get_or_create_cart($koneksi) {
  // jika user login
  if (!empty($_SESSION['user'])) {
    $username = $_SESSION['user'];
    // ambil id_user
    $stmt = $koneksi->prepare("SELECT id_user FROM users WHERE username = ?");
    if ($stmt) {
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $id_user = (int)$row['id_user'];
        // cek cart user
        $stmt2 = $koneksi->prepare("SELECT id_cart FROM carts WHERE id_user = ? LIMIT 1");
        if ($stmt2) {
          $stmt2->bind_param("i", $id_user);
          $stmt2->execute();
          $r2 = $stmt2->get_result();
          if ($r2 && $r2->num_rows === 1) {
            $c = $r2->fetch_assoc();
            $stmt2->close();
            $stmt->close();
            return (int)$c['id_cart'];
          } else {
            // buat cart baru untuk user
            $ins = $koneksi->prepare("INSERT INTO carts (id_user) VALUES (?)");
            if ($ins) {
              $ins->bind_param("i", $id_user);
              $ins->execute();
              $new = $koneksi->insert_id;
              $ins->close();
              $stmt->close();
              return (int)$new;
            }
          }
          $stmt2->close();
        }
      }
      $stmt->close();
    }
  }

  // guest via session_id
  $sess = session_id();
  if (empty($sess)) {
    // pastikan session aktif
    session_start();
    $sess = session_id();
  }

  $stmt3 = $koneksi->prepare("SELECT id_cart FROM carts WHERE session_id = ? LIMIT 1");
  if ($stmt3) {
    $stmt3->bind_param("s", $sess);
    $stmt3->execute();
    $r3 = $stmt3->get_result();
    if ($r3 && $r3->num_rows === 1) {
      $c = $r3->fetch_assoc();
      $stmt3->close();
      return (int)$c['id_cart'];
    } else {
      $ins2 = $koneksi->prepare("INSERT INTO carts (session_id) VALUES (?)");
      if ($ins2) {
        $ins2->bind_param("s", $sess);
        $ins2->execute();
        $new2 = $koneksi->insert_id;
        $ins2->close();
        return (int)$new2;
      }
    }
    $stmt3->close();
  }

  return null;
}

/**
 * Tambah item ke cart: jika sudah ada, tambahkan qty; jika belum, insert.
 * Mengambil harga produk dari tabel produk (harga saat ini).
 */
function add_to_cart($koneksi, $id_cart, $id_produk, $qty = 1) {
  $id_cart = (int)$id_cart;
  $id_produk = (int)$id_produk;
  $qty = (int)$qty;
  if ($id_cart <= 0 || $id_produk <= 0 || $qty <= 0) return false;

  // ambil harga produk saat ini
  $stmtP = $koneksi->prepare("SELECT harga FROM produk WHERE id_produk = ? LIMIT 1");
  if (!$stmtP) return false;
  $stmtP->bind_param("i", $id_produk);
  $stmtP->execute();
  $resP = $stmtP->get_result();
  if (!$resP || $resP->num_rows === 0) { $stmtP->close(); return false; }
  $rowP = $resP->fetch_assoc();
  $price = (float)$rowP['harga'];
  $stmtP->close();

  // cek apakah item sudah ada di cart
  $stmt = $koneksi->prepare("SELECT id_item, qty FROM cart_items WHERE id_cart = ? AND id_produk = ? LIMIT 1");
  if (!$stmt) return false;
  $stmt->bind_param("ii", $id_cart, $id_produk);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $id_item = (int)$row['id_item'];
    // update qty (jumlahkan)
    $newQty = $row['qty'] + $qty;
    $upd = $koneksi->prepare("UPDATE cart_items SET qty = ? WHERE id_item = ?");
    if ($upd) {
      $upd->bind_param("ii", $newQty, $id_item);
      $ok = $upd->execute();
      $upd->close();
      $stmt->close();
      return (bool)$ok;
    } else {
      $stmt->close();
      return false;
    }
  } else {
    // insert baru (snapshot price)
    $ins = $koneksi->prepare("INSERT INTO cart_items (id_cart, id_produk, qty, price) VALUES (?, ?, ?, ?)");
    if ($ins) {
      $ins->bind_param("iiid", $id_cart, $id_produk, $qty, $price);
      $ok2 = $ins->execute();
      $ins->close();
      $stmt->close();
      return (bool)$ok2;
    } else {
      $stmt->close();
      return false;
    }
  }
}

/**
 * Ambil semua item cart (join ke produk untuk nama/foto jika perlu)
 */
function get_cart_items($koneksi, $id_cart) {
  $id_cart = (int)$id_cart;
  $stmt = $koneksi->prepare("
    SELECT ci.id_item, ci.id_produk, ci.qty, ci.price, p.nama_produk, p.foto
    FROM cart_items ci
    LEFT JOIN produk p ON p.id_produk = ci.id_produk
    WHERE ci.id_cart = ?
    ORDER BY ci.added_at DESC
  ");
  if (!$stmt) return [];
  $stmt->bind_param("i", $id_cart);
  $stmt->execute();
  $res = $stmt->get_result();
  $items = [];
  while ($row = $res->fetch_assoc()) $items[] = $row;
  $stmt->close();
  return $items;
}

/**
 * Fungsi bantu: hitung total cart
 */
function cart_total($koneksi, $id_cart) {
  $id_cart = (int)$id_cart;
  $stmt = $koneksi->prepare("SELECT SUM(qty * price) AS total FROM cart_items WHERE id_cart = ?");
  if (!$stmt) return 0;
  $stmt->bind_param("i", $id_cart);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();
  $stmt->close();
  return (float)($row['total'] ?? 0);
}
