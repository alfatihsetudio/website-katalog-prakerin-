
<?php
// FILE: admin_login.php
// Tempatkan di root proyek atau folder auth. Menangani: LOGIN ADMIN (tabel admin)
// -----------------------------------------
session_start();
require_once 'koneksi.php';
include 'templates/header.php';

// helper: sanitize_redirect (sama seperti di user_auth.php)
function sanitize_redirect($url) {
  if (empty($url)) return '';
  $u = @parse_url($url);
  if (isset($u['host']) && $u['host'] !== ($_SERVER['HTTP_HOST'] ?? '')) return '';
  $path = $u['path'] ?? '';
  $query = isset($u['query']) ? ('?' . $u['query']) : '';
  return $path . $query;
}

$flash_msg = '';

/* ----------------- HANDLE: LOGIN ADMIN (only admin table here) ----------------- */
if (isset($_POST['login_admin'])) {
  $au = trim($_POST['a_username'] ?? '');
  $ap = $_POST['a_password'] ?? '';
  $redirect_target_admin = sanitize_redirect($_POST['redirect_admin'] ?? '');

  if ($au === '' || $ap === '') {
    $flash_msg = '<div class="alert err">Isi username dan password admin.</div>';
  } else {
    // Cek tabel admin (bukan super_admins)
    $stmt = $koneksi->prepare("SELECT id_admin, username, password FROM admin WHERE username = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param("s", $au);
      if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
          $row = $res->fetch_assoc();
          $hash = $row['password'];

          // cek hashed
          if (!empty($hash) && password_verify($ap, $hash)) {
            $_SESSION['admin'] = $row['username'];
            $_SESSION['id_admin'] = $row['id_admin'];
            $flash_msg = '<div class="alert success">Login admin berhasil! Mengarahkan...</div>';
            $target = $redirect_target_admin ?: 'admin_dashboard.php';
            echo $flash_msg;
            echo '<meta http-equiv="refresh" content="1;url=' . htmlspecialchars($target) . '">';
            include 'templates/footer.php';
            $stmt->close();
            exit;
          } else {
            // fallback plaintext -> upgrade to hash
            if ($ap === $hash) {
              $newHash = password_hash($ap, PASSWORD_DEFAULT);
              $upd = $koneksi->prepare("UPDATE admin SET password = ? WHERE id_admin = ?");
              if ($upd) {
                $upd->bind_param("si", $newHash, $row['id_admin']);
                $upd->execute();
                $upd->close();
              }
              $_SESSION['admin'] = $row['username'];
              $_SESSION['id_admin'] = $row['id_admin'];
              $flash_msg = '<div class="alert success">Login admin berhasil! (password di-upgrade) Mengarahkan...</div>';
              $target = $redirect_target_admin ?: 'admin_dashboard.php';
              echo $flash_msg;
              echo '<meta http-equiv="refresh" content="1;url=' . htmlspecialchars($target) . '">';
              include 'templates/footer.php';
              $stmt->close();
              exit;
            } else {
              $flash_msg = '<div class="alert err">Username atau password admin salah.</div>';
            }
          }
        } else {
          $flash_msg = '<div class="alert err">Username atau password admin salah.</div>';
        }
      } else {
        $flash_msg = '<div class="alert err">Gagal menjalankan query: ' . htmlspecialchars($koneksi->error) . '</div>';
      }
      $stmt->close();
    } else {
      $flash_msg = '<div class="alert err">Terjadi kesalahan: ' . htmlspecialchars($koneksi->error) . '</div>';
    }
  }
}
?>

<style>
.right-card { background:#fff; border-radius:14px; padding:26px; box-shadow:0 6px 18px rgba(15,23,42,0.06); width:100%; max-width:420px; margin:60px auto; }
label { display:block; font-weight:600; margin-bottom:6px; color:#0f172a; }
input[type="text"], input[type="password"] { width:100%; padding:10px; border-radius:8px; border:1px solid #e6edf3; font-size:14px; }
.btn-main { background:#0ea5a4; color:#fff; padding:10px 14px; border-radius:10px; border:none; font-weight:700; cursor:pointer; }
.btn-secondary { background:#e2e8f0; color:#0f172a; padding:10px 14px; border-radius:10px; border:none; font-weight:700; cursor:pointer; }
.alert { margin-top:12px; padding:12px; border-radius:10px; font-size:14px; }
.alert.err { background:#fff1f2; color:#991b1b; border:1px solid #fecaca; }
.alert.success { background:#ecfdf5; color:#065f46; border:1px solid #bbf7d0; }
</style>

<main>
  <div class="right-card" aria-label="Admin login panel">
    <div class="admin-title" style="font-weight:800;color:#0f172a;margin-bottom:6px;font-size:18px;">Area Admin</div>
    <div class="admin-desc" style="color:#475569;margin-bottom:12px;">Halaman ini khusus untuk administrator. Jika Anda bukan admin, jangan gunakan form ini.</div>

    <?php if ($flash_msg) echo $flash_msg; ?>

    <form method="post" id="form-admin">
      <div class="form-group">
        <label>Username Admin</label>
        <input type="text" name="a_username" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="a_password" required>
      </div>

      <?php
      // optional redirect for admin (if provided and safe)
      $redirect_admin = sanitize_redirect($_GET['redirect_admin'] ?? '');
      if (!empty($redirect_admin)) {
        echo '<input type="hidden" name="redirect_admin" value="' . htmlspecialchars($redirect_admin) . '">';
      }
      ?>

      <div style="display:flex;gap:10px;align-items:center;">
        <button type="submit" name="login_admin" class="btn-main">Login Admin</button>
        <a href="user_auth.php" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;padding:10px 12px;border-radius:8px;">Kembali ke User</a>
      </div>
    </form>
  </div>
</main>

<?php include 'templates/footer.php'; ?>
