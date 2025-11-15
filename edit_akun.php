<?php
// edit_akun.php
session_start();
require_once 'koneksi.php';
require_once 'templates/header.php';

// Hanya user (bukan admin)
if (empty($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$username_session = $_SESSION['user'];
$errors = [];
$success = '';

// Ambil data user berdasarkan username session
$stmt = $koneksi->prepare("SELECT id_user, username, password FROM users WHERE username = ?");
if (!$stmt) {
  die("Prepare failed: " . $koneksi->error);
}
$stmt->bind_param("s", $username_session);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
  echo "<script>alert('Akun tidak ditemukan.'); window.location='logout.php';</script>";
  $stmt->close();
  exit;
}
$user = $res->fetch_assoc();
$stmt->close();

$user_id = (int)$user['id_user'];
$stored_password = $user['password'];
$display_username = $user['username'];

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_username = trim($_POST['username'] ?? '');
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  $display_username = $new_username !== '' ? $new_username : $user['username'];

  if ($new_username === '') $errors[] = 'Username tidak boleh kosong.';

  // Verifikasi password saat ini
  $password_ok = false;
  if (!empty($stored_password) && password_verify($current_password, $stored_password)) {
    $password_ok = true;
  } elseif ($current_password === $stored_password) {
    $password_ok = true;
  }
  if (!$password_ok) $errors[] = 'Password saat ini salah.';

  // Ganti password jika diminta
  $change_password = false;
  if ($new_password !== '' || $confirm_password !== '') {
    if ($new_password !== $confirm_password) {
      $errors[] = 'Password baru dan konfirmasi tidak cocok.';
    } else {
      $change_password = true;
      $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
    }
  }

  // Cek username duplicate jika berubah
  if (empty($errors) && $new_username !== $username_session) {
    $stmtCheck = $koneksi->prepare("SELECT id_user FROM users WHERE username = ? AND id_user != ?");
    if ($stmtCheck) {
      $stmtCheck->bind_param("si", $new_username, $user_id);
      $stmtCheck->execute();
      $resCheck = $stmtCheck->get_result();
      if ($resCheck && $resCheck->num_rows > 0) $errors[] = 'Username sudah digunakan oleh pengguna lain.';
      $stmtCheck->close();
    } else {
      $errors[] = 'Gagal memeriksa username.';
    }
  }

  // Update jika tidak ada error
  if (empty($errors)) {
    if ($change_password) {
      $stmtUpd = $koneksi->prepare("UPDATE users SET username = ?, password = ? WHERE id_user = ?");
      $stmtUpd->bind_param("ssi", $new_username, $new_password_hashed, $user_id);
    } else {
      $stmtUpd = $koneksi->prepare("UPDATE users SET username = ? WHERE id_user = ?");
      $stmtUpd->bind_param("si", $new_username, $user_id);
    }

    if ($stmtUpd && $stmtUpd->execute()) {
      if ($new_username !== $username_session) {
        $_SESSION['user'] = $new_username;
        $username_session = $new_username;
      }
      $user['username'] = $new_username;
      $display_username = $new_username;
      $success = 'Profil berhasil diperbarui.';
      if ($change_password) $stored_password = $new_password_hashed;
    } else {
      $errors[] = 'Terjadi kesalahan saat menyimpan.';
    }
    $stmtUpd->close();
  }
}
?>

<main style="padding: 30px 20px; max-width:900px; margin:0 auto;">
  <div class="container" style="background: #fff; padding:22px; border-radius:12px; box-shadow:0 6px 18px rgba(15,23,42,0.06);">
    <h2 style="margin-top:0;">Edit Akun</h2>
    <p>Ubah username atau password akunmu. Untuk menyimpan perubahan, masukkan password saat ini di bawah.</p>

    <?php if (!empty($errors)): ?>
      <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:12px;">
        <ul style="margin:0;padding-left:18px;">
          <?php foreach($errors as $err) echo "<li>" . htmlspecialchars($err) . "</li>"; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div style="background:#ecfdf5;border:1px solid #bbf7d0;color:#065f46;padding:10px 14px;border-radius:8px;margin-bottom:12px;">
        <?php echo htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>

    <form method="post" action="edit_akun.php" style="display:grid; gap:12px;">

      <!-- Username -->
      <label>
        <div style="font-weight:600;margin-bottom:6px;">Username</div>
        <input type="text" name="username" value="<?php echo htmlspecialchars($display_username); ?>" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6edf3;">
      </label>

      <hr style="border:none;border-top:1px dashed #e6edf3;margin:6px 0 8px;">

      <!-- Password baru -->
      <label>
        <div style="font-weight:600;margin-bottom:6px;">Password Baru <small style="color:#666;font-weight:400;">(kosongkan jika tidak ingin mengganti)</small></div>
        <input type="password" name="new_password" placeholder="Password baru" style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6edf3;">
      </label>

      <!-- Konfirmasi password baru -->
      <label>
        <div style="font-weight:600;margin-bottom:6px;">Konfirmasi Password Baru</div>
        <input type="password" name="confirm_password" placeholder="Konfirmasi password baru" style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6edf3;">
      </label>

      <hr style="border:none;border-top:1px dashed #e6edf3;margin:6px 0 8px;">

      <!-- Password saat ini -->
      <label>
        <div style="font-weight:600;margin-bottom:6px;">Password Saat Ini <small style="color:#666;font-weight:400;">(wajib diisi untuk menyimpan perubahan)</small></div>
        <input type="password" name="current_password" required placeholder="Masukkan password saat ini" style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6edf3;">
      </label>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px;">
        <a href="index.php" class="btn-plain" style="background:#9ca3af; color:#fff; text-decoration:none; padding:8px 14px; border-radius:8px; display:inline-flex; align-items:center;">Batal</a>
        <button type="submit" class="btn-plain">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</main>

<?php
require_once 'templates/footer.php';
?>
