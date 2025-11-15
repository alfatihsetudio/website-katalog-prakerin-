<?php
// hapus_akun.php
// Menghapus akun user atau admin (tergantung session yang aktif)

if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';
require_once 'templates/header.php';

$errors = [];
$success = '';
$isAdmin = !empty($_SESSION['admin']);
$isUser  = !empty($_SESSION['user']);

// Jika tidak ada session, redirect ke index
if (!$isAdmin && !$isUser) {
  header('Location: index.php');
  exit;
}

// Ambil identitas dari session
if ($isAdmin) {
  $session_name = $_SESSION['admin'];
} else {
  $session_name = $_SESSION['user'];
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_akun'])) {
  $password = $_POST['password'] ?? '';

  if (trim($password) === '') {
    $errors[] = 'Masukkan password untuk konfirmasi.';
  } else {
    if ($isAdmin) {
      // Ambil data admin
      $stmt = $koneksi->prepare("SELECT id_admin, username, password FROM admin WHERE username = ?");
      if (!$stmt) {
        $errors[] = 'Terjadi kesalahan (prepare): ' . $koneksi->error;
      } else {
        $stmt->bind_param("s", $session_name);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
          $row = $res->fetch_assoc();
          $id = (int)$row['id_admin'];
          $hash = $row['password'];

          // Verifikasi password (hash + fallback)
          $ok = false;
          if (!empty($hash) && password_verify($password, $hash)) $ok = true;
          elseif ($password === $hash) $ok = true;

          if ($ok) {
            // Cek jumlah admin, jangan hapus jika hanya 1 admin tersisa
            $countQ = $koneksi->query("SELECT COUNT(*) AS cnt FROM admin");
            $cntRow = $countQ->fetch_assoc();
            $countAdmin = (int)$cntRow['cnt'];
            if ($countAdmin <= 1) {
              $errors[] = 'Tidak dapat menghapus akun. Harus ada minimal 1 akun admin.';
            } else {
              // Lakukan hapus
              $del = $koneksi->prepare("DELETE FROM admin WHERE id_admin = ?");
              if ($del) {
                $del->bind_param("i", $id);
                if ($del->execute()) {
                  // logout dan redirect ke admin_login
                  session_unset();
                  session_destroy();
                  $success = 'Akun admin berhasil dihapus. Mengarahkan...';
                  echo '<div class="alert success" style="margin:20px 0;">' . htmlspecialchars($success) . '</div>';
                  echo '<meta http-equiv="refresh" content="1;url=admin_login.php">';
                  $del->close();
                  $stmt->close();
                  require_once 'templates/footer.php';
                  exit;
                } else {
                  $errors[] = 'Gagal menghapus akun: ' . $koneksi->error;
                }
                $del->close();
              } else {
                $errors[] = 'Gagal menyiapkan query hapus: ' . $koneksi->error;
              }
            }
          } else {
            $errors[] = 'Password salah. Akun tidak dihapus.';
          }
        } else {
          $errors[] = 'Akun admin tidak ditemukan.';
        }
        $stmt->close();
      }
    } else { // user
      // Ambil data user
      $stmt = $koneksi->prepare("SELECT id_user, username, password FROM users WHERE username = ?");
      if (!$stmt) {
        $errors[] = 'Terjadi kesalahan (prepare): ' . $koneksi->error;
      } else {
        $stmt->bind_param("s", $session_name);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
          $row = $res->fetch_assoc();
          $id = (int)$row['id_user'];
          $hash = $row['password'];

          // Verifikasi password (hash + fallback)
          $ok = false;
          if (!empty($hash) && password_verify($password, $hash)) $ok = true;
          elseif ($password === $hash) $ok = true;

          if ($ok) {
            // Lakukan hapus
            $del = $koneksi->prepare("DELETE FROM users WHERE id_user = ?");
            if ($del) {
              $del->bind_param("i", $id);
              if ($del->execute()) {
                // logout dan redirect ke index
                session_unset();
                session_destroy();
                $success = 'Akun berhasil dihapus. Mengarahkan...';
                echo '<div class="alert success" style="margin:20px 0;">' . htmlspecialchars($success) . '</div>';
                echo '<meta http-equiv="refresh" content="1;url=index.php">';
                $del->close();
                $stmt->close();
                require_once 'templates/footer.php';
                exit;
              } else {
                $errors[] = 'Gagal menghapus akun: ' . $koneksi->error;
              }
              $del->close();
            } else {
              $errors[] = 'Gagal menyiapkan query hapus: ' . $koneksi->error;
            }
          } else {
            $errors[] = 'Password salah. Akun tidak dihapus.';
          }
        } else {
          $errors[] = 'Akun tidak ditemukan.';
        }
        $stmt->close();
      }
    } // end user/admin branch
  } // end password non-empty
} // end POST

// Tampilkan form konfirmasi penghapusan
?>

<main style="padding: 30px 20px; max-width:700px; margin:0 auto;">
  <div class="container" style="background:#fff;padding:22px;border-radius:12px;box-shadow:0 6px 18px rgba(15,23,42,0.06);">
    <h2 style="margin-top:0;">Hapus Akun</h2>
    <p>
      Anda akan menghapus akun <strong><?php echo htmlspecialchars($session_name); ?></strong> secara permanen. 
      Tindakan ini <strong>tidak dapat dibatalkan</strong> dan seluruh data yang terkait dengan akun ini akan dihapus. 
      Silakan masukkan <strong>password Anda saat ini</strong> untuk mengonfirmasi penghapusan akun.
    </p>

    <?php if (!empty($errors)): ?>
      <div class="alert err" style="margin-bottom:12px;">
        <ul style="margin:0; padding-left:18px;">
          <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="hapus_akun.php" style="display:grid; gap:12px;">
      <label style="font-weight:600;">Konfirmasi Password Saat Ini</label>
      <input type="password" name="password" required placeholder="Masukkan password Anda" style="padding:10px;border-radius:8px;border:1px solid #e6edf3;">

      <div style="display:flex; gap:10px; justify-content:flex-end;">
        <a href="<?php echo ($isAdmin ? 'admin_dashboard.php' : 'index.php'); ?>" 
           class="btn-plain" 
           style="background:#9ca3af; color:#fff; text-decoration:none; padding:8px 14px; border-radius:8px; display:inline-flex; align-items:center;">
           Batal
        </a>
        <button type="submit" name="hapus_akun" class="btn-plain" style="background:#dc2626;">
          Hapus Akun
        </button>
      </div>
    </form>
  </div>
</main>


<?php
require_once 'templates/footer.php';
?>
