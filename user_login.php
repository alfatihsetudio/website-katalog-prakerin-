<?php
// FILE: user_auth.php
// Tempatkan di root proyek atau folder auth. Menangani: LOGIN USER + REGISTER USER + UI (kiri)
// -----------------------------------------
session_start();
require_once 'koneksi.php';
include 'templates/header.php';

// helper: validasi redirect agar hanya path internal (hindari open-redirect)
function sanitize_redirect($url) {
  if (empty($url)) return '';
  $u = @parse_url($url);
  // jika ada host dan bukan host kita -> tolak
  if (isset($u['host']) && $u['host'] !== ($_SERVER['HTTP_HOST'] ?? '')) return '';
  // allow relative path or same-host path
  $path = $u['path'] ?? '';
  $query = isset($u['query']) ? ('?' . $u['query']) : '';
  return $path . $query;
}

// ambil redirect dari GET (misalnya ?redirect=/produk_detail.php?id=1)
$redirect_get = isset($_GET['redirect']) ? $_GET['redirect'] : '';
$redirect_get = sanitize_redirect($redirect_get);

// ambil redirect yang disertakan di POST (jika submit)
$redirect_post = $_POST['redirect'] ?? '';

// tempat menyimpan pesan untuk ditampilkan setelah proses
$flash_msg = '';

/* ----------------- HANDLE: LOGIN USER ----------------- */
if (isset($_POST['login_user'])) {
  $lu = trim($_POST['username'] ?? '');
  $lp = $_POST['password'] ?? '';
  $redirect_target = sanitize_redirect($_POST['redirect'] ?? '');

  if ($lu === '' || $lp === '') {
    $flash_msg = '<div class="alert err">Isi username dan password.</div>';
  } else {
    $stmt = $koneksi->prepare("SELECT id_user, username, password FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param("s", $lu);
      if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
          $row = $res->fetch_assoc();
          $hash = $row['password'];

          // jika password disimpan hashed -> password_verify
          if (!empty($hash) && password_verify($lp, $hash)) {
            $_SESSION['user'] = $row['username'];
            $_SESSION['id_user'] = $row['id_user'];
            $flash_msg = '<div class="alert success">Login berhasil! Mengarahkan...</div>';
            $target = $redirect_target ?: 'index.php';
            echo $flash_msg;
            echo '<meta http-equiv="refresh" content="1;url=' . htmlspecialchars($target) . '">';
            include 'templates/footer.php';
            $stmt->close();
            exit;
          } else {
            // fallback: plaintext (legacy) -> upgrade ke hash
            if ($lp === $hash) {
              $newHash = password_hash($lp, PASSWORD_DEFAULT);
              $upd = $koneksi->prepare("UPDATE users SET password = ? WHERE id_user = ?");
              if ($upd) {
                $upd->bind_param("si", $newHash, $row['id_user']);
                $upd->execute();
                $upd->close();
              }
              $_SESSION['user'] = $row['username'];
              $_SESSION['id_user'] = $row['id_user'];
              $flash_msg = '<div class="alert success">Login berhasil! (password di-upgrade) Mengarahkan...</div>';
              $target = $redirect_target ?: 'index.php';
              echo $flash_msg;
              echo '<meta http-equiv="refresh" content="1;url=' . htmlspecialchars($target) . '">';
              include 'templates/footer.php';
              $stmt->close();
              exit;
            } else {
              $flash_msg = '<div class="alert err">Username atau password salah.</div>';
            }
          }
        } else {
          $flash_msg = '<div class="alert err">Username atau password salah.</div>';
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

/* ----------------- HANDLE: REGISTER USER ----------------- */
if (isset($_POST['register_user'])) {
  $ru = trim($_POST['r_username'] ?? '');
  $rp = $_POST['r_password'] ?? '';
  $redirect_target = sanitize_redirect($_POST['redirect'] ?? '');

  if ($ru === '' || $rp === '') {
    $flash_msg = '<div class="alert err">Isi username dan password.</div>';
  } else {
    $stmt = $koneksi->prepare("SELECT id_user FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param("s", $ru);
      if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
          $flash_msg = '<div class="alert err">Username sudah digunakan.</div>';
        } else {
          $hash = password_hash($rp, PASSWORD_DEFAULT);
          $ins = $koneksi->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
          if ($ins) {
            $ins->bind_param("ss", $ru, $hash);
            if ($ins->execute()) {
              $flash_msg = '<div class="alert success">Akun berhasil dibuat! Silakan login.</div>';
            } else {
              $flash_msg = '<div class="alert err">Gagal membuat akun: ' . htmlspecialchars($koneksi->error) . '</div>';
            }
            $ins->close();
          } else {
            $flash_msg = '<div class="alert err">Gagal mempersiapkan query: ' . htmlspecialchars($koneksi->error) . '</div>';
          }
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
/* Layout: split user (left) and admin (right) */
body { background-color: #f9fafb; font-family: 'Inter', sans-serif; }
.container-auth { display:flex; gap:24px; justify-content:center; align-items:flex-start; min-height:72vh; padding:36px 16px; }
.left-card { background:#fff; border-radius:14px; padding:26px; box-shadow:0 6px 18px rgba(15,23,42,0.06); width:100%; max-width:640px; }

/* tabs inside left card */
.tabs { display:flex; gap:8px; margin-bottom:18px; }
.tab { cursor:pointer; padding:10px 14px; font-weight:600; border-radius:10px; background: #f1f5f9; color:#0f172a; border:1px solid transparent; }
.tab.active { background:#2563eb; color:#fff; border-color:rgba(0,0,0,0.04); }
.form-block { display:none; margin-top:8px; }
.form-block.active { display:block; }

.form-group { margin-bottom:12px; }
label { display:block; font-weight:600; margin-bottom:6px; color:#0f172a; }
input[type="text"], input[type="password"] { width:100%; padding:10px; border-radius:8px; border:1px solid #e6edf3; font-size:14px; }
.btn-main { background:#2563eb; color:#fff; padding:10px 14px; border-radius:10px; border:none; font-weight:700; cursor:pointer; }
.btn-secondary { background:#e2e8f0; color:#0f172a; padding:10px 14px; border-radius:10px; border:none; font-weight:700; cursor:pointer; }

.small-note { font-size:13px; color:#475569; margin-top:8px; }

.alert { margin-top:12px; padding:12px; border-radius:10px; font-size:14px; }
.alert.err { background:#fff1f2; color:#991b1b; border:1px solid #fecaca; }
.alert.success { background:#ecfdf5; color:#065f46; border:1px solid #bbf7d0; }

@media (max-width:880px) {
  .container-auth { flex-direction:column; align-items:center; }
  .left-card { max-width:100%; width:100%; }
}
</style>

<main>
  <div class="container-auth">
    <!-- LEFT: USER (Login / Register) -->
    <div class="left-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <h3 style="margin:0;">Masuk / Daftar</h3>
        <div style="font-size:13px;color:#64748b;">Akun pengguna untuk pemesanan</div>
      </div>

      <?php
      // tampilkan flash message bila ada (dari proses PHP di atas)
      if ($flash_msg) echo $flash_msg;
      // jika ada redirect GET kita tambahkan input hidden di form via PHP variable
      $effective_redirect = $redirect_post ?: $redirect_get;
      ?>

      <div class="tabs" role="tablist" aria-label="Auth tabs">
        <button class="tab active" data-target="login-block" id="tab-login">Masuk</button>
        <button class="tab" data-target="register-block" id="tab-register">Buat Akun</button>
      </div>

      <!-- LOGIN USER -->
      <div id="login-block" class="form-block active">
        <form method="post" id="form-login">
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
          </div>

          <?php if ($effective_redirect): ?>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($effective_redirect); ?>">
          <?php endif; ?>

          <div style="display:flex;gap:10px;align-items:center;">
            <button type="submit" name="login_user" class="btn-main">Masuk</button>
            <a href="index.php" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;">Kembali</a>
          </div>

          <div class="small-note">Belum punya akun? Daftar untuk menyimpan riwayat pesanan.</div>
        </form>
      </div>

      <!-- REGISTER USER -->
      <div id="register-block" class="form-block">
        <form method="post" id="form-register">
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="r_username" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="r_password" required>
          </div>

          <?php if ($effective_redirect): ?>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($effective_redirect); ?>">
          <?php endif; ?>

          <div style="display:flex;gap:10px;align-items:center;">
            <button type="submit" name="register_user" class="btn-main">Buat Akun</button>
            <button type="button" class="btn-secondary" onclick="openLogin()">Sudah Punya Akun</button>
          </div>

          <div class="small-note">Password akan disimpan dengan aman.</div>
        </form>
      </div>
    </div>



  </div>
</main>

<script>
// tab switching
const tabs = document.querySelectorAll('.tab');
const blocks = document.querySelectorAll('.form-block');
tabs.forEach(t => t.addEventListener('click', () => {
  tabs.forEach(x => x.classList.remove('active'));
  t.classList.add('active');
  const target = t.getAttribute('data-target');
  blocks.forEach(b => b.classList.remove('active'));
  document.getElementById(target).classList.add('active');
}));

function openLogin() {
  document.querySelector('#tab-login').click();
}

// jika ada parameter ?as=user -> buka tab user (login)
(function(){
  const params = new URLSearchParams(window.location.search);
  const as = params.get('as');
  if (as === 'user') {
    document.querySelector('#tab-login').click();
    const redirect = params.get('redirect');
    if (redirect) {
      ['form-login','form-register'].forEach(id => {
        const f = document.getElementById(id);
        if (f && !f.querySelector('input[name="redirect"]')) {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = 'redirect';
          inp.value = redirect;
          f.appendChild(inp);
        }
      });
    }
  }
})();
</script>

<?php include 'templates/footer.php'; ?>


