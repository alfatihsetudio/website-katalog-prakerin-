<?php
// templates/header.php  (fixed)
//  - output buffering aktifkan untuk mencegah "headers already sent" jika suatu file melakukan header() setelah include
//  - jangan letakkan spasi / BOM sebelum tag <?php
if (ob_get_level() === 0) {
    // mulai output buffering agar header() di file lain tetap bisa dipanggil walau header.php meng-output HTML
    ob_start();
}

// start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// NOTE: Jangan echo apapun sebelum kode PHP di atas.
// Header markup dimulai di bawah -- desain & fungsi dipertahankan.
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
  <title>Toko Sekolah</title>

  <style>
  /* ---------- Variables & base ---------- */
  :root{
    --bg:#061028;
    --glass: rgba(255,255,255,0.04);
    --muted:#94a3b8;
    --accent:#2563eb;
    --accent-2:#0ea5a4;
    --danger:#ef4444;
    --white:#ffffff;
    --radius:12px;
    --header-height:72px;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial;background:#f6f8fb;color:#0f172a}

  /* ---------- Header ---------- */
  .site-header{
    position:sticky;top:0;z-index:1200;height:var(--header-height);
    background: linear-gradient(90deg, rgba(6,10,24,0.98), rgba(8,13,36,0.98));
    border-bottom:1px solid rgba(255,255,255,0.04);
    backdrop-filter: blur(6px);
    box-shadow: 0 8px 30px rgba(2,6,23,0.35);
    display:flex;align-items:center;
  }
  .header-inner{max-width:1200px;margin:0 auto;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;gap:16px;width:100%}

  /* Brand */
  .brand{display:flex;align-items:center;gap:12px}
  .logo{height:44px;display:block;border-radius:8px;transition:transform .18s ease}
  .logo:hover{transform:scale(1.03)}
  .brand-title{color:var(--white);font-weight:800;font-size:18px;text-decoration:none}

  /* Nav */
  .nav{display:flex;align-items:center;gap:10px}
  .nav .link{color:var(--white);text-decoration:none;padding:8px 12px;border-radius:10px;font-weight:600;transition:all .15s}
  .nav .link:hover{background:var(--glass);color:var(--muted);transform:translateY(-1px)}
  .nav .primary{background:linear-gradient(180deg,var(--accent),#1d4ed8);box-shadow:0 8px 30px rgba(37,99,235,0.12);border:0;color:var(--white)}
  .nav .ghost{border:1px solid rgba(255,255,255,0.06);background:transparent;color:var(--white)}

  /* Cart (for user & guest only) */
  .cart-btn{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:10px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.04);color:var(--white);font-weight:700}
  .cart-badge{background:var(--danger);color:#fff;padding:4px 8px;border-radius:999px;font-weight:800;font-size:12px;box-shadow:0 6px 18px rgba(239,68,68,0.12)}

  /* account / admin / super */
  .account{display:flex;align-items:center;gap:8px}
  .account-btn{padding:8px 12px;border-radius:10px;background:linear-gradient(180deg,var(--accent-2),#059669);color:#fff;border:0;font-weight:700;cursor:pointer;display:flex;gap:8px;align-items:center}
  .account-btn.secondary{background:transparent;border:1px solid rgba(255,255,255,0.06);font-weight:700}
  .dropdown{position:relative}
  .dropdown-menu{position:absolute;right:0;top:110%;min-width:190px;background:#071229;border-radius:10px;border:1px solid rgba(255,255,255,0.04);box-shadow:0 12px 40px rgba(2,6,23,0.6);overflow:hidden;display:none}
  .dropdown:hover .dropdown-menu{display:block}
  .dropdown-item{display:block;padding:10px 12px;color:var(--white);text-decoration:none;font-weight:600}
  .dropdown-item:hover{background:rgba(255,255,255,0.02);color:var(--muted)}
  .dropdown-item.warn{color:var(--danger);font-weight:800}

  /* super */
  .super-pill{padding:8px 12px;border-radius:10px;background:linear-gradient(180deg,#6ee7b7,#10b981);color:#04201a;font-weight:800;box-shadow:0 10px 30px rgba(16,185,129,0.08)}
  .super-link{padding:8px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:var(--white);font-weight:700}

  /* modal: upgraded */
  .modal-backdrop{position:fixed;inset:0;background:rgba(3,7,18,0.55);display:flex;align-items:center;justify-content:center;z-index:1500;display:none;padding:16px}
  .modal{background:#071229;color:var(--white);padding:22px;border-radius:12px;max-width:560px;width:100%;box-shadow:0 20px 60px rgba(2,6,23,0.6);display:flex;gap:18px;align-items:flex-start}
  .modal-icon{width:64px;height:64px;border-radius:12px;background:linear-gradient(135deg,#111827,#0b1220);display:flex;align-items:center;justify-content:center;font-size:28px;color:#fff;flex-shrink:0}
  .modal-content h3{margin:0 0 8px;font-size:18px}
  .modal-content p{margin:0 0 12px;color:var(--muted)}
  .modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
  .btn-cta{background:linear-gradient(180deg,var(--accent),#1d4ed8);color:#fff;padding:10px 14px;border-radius:10px;border:0;font-weight:800;cursor:pointer}
  .btn-ghost{padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.06);background:transparent;color:var(--white);cursor:pointer}

  /* toast */
  .toast{position:fixed;right:16px;bottom:16px;background:#0b1220;color:var(--white);padding:12px 14px;border-radius:10px;box-shadow:0 8px 36px rgba(2,6,23,0.5);display:none;z-index:1600}

  /* responsive */
  @media (max-width:860px){
    .header-inner{padding:10px}
    .brand-title{display:none}
    .nav{gap:6px}
    .cart-btn{padding:6px 8px}
    .modal {flex-direction:column;align-items:stretch}
    .modal-icon{width:56px;height:56px}
  }
  </style>
</head>
<body>
<header class="site-header" role="banner" aria-label="Main header">
  <div class="header-inner">
    <div class="brand">
      <a href="index.php"><img src="assets/img/logo.png" alt="Logo" class="logo"></a>
    </div>

    <nav class="nav" role="navigation" aria-label="Primary">
      <a class="link" href="index.php">Home</a>
      <a class="link" href="contact.php">Contact</a>

      <?php
      // determine roles from session (keep same semantics as project)
      $is_super = !empty($_SESSION['super_admin']);
      $is_admin = !$is_super && !empty($_SESSION['admin']);
      $is_user  = !$is_super && !$is_admin && !empty($_SESSION['user']);

      // compute cart_count only when user and koneksi exists
      $cart_count = 0;
      if ($is_user && isset($koneksi) && $koneksi) {
        $stmtU = $koneksi->prepare("SELECT id_user FROM users WHERE username = ? LIMIT 1");
        if ($stmtU) {
          $stmtU->bind_param("s", $_SESSION['user']);
          $stmtU->execute();
          $resU = $stmtU->get_result();
          if ($resU && $resU->num_rows === 1) {
            $rowU = $resU->fetch_assoc();
            $uid = (int)$rowU['id_user'];
            $stmtC = $koneksi->prepare("SELECT COALESCE(SUM(ci.qty),0) AS total_qty FROM carts c LEFT JOIN cart_items ci ON ci.id_cart = c.id_cart WHERE c.id_user = ?");
            if ($stmtC) {
              $stmtC->bind_param("i", $uid);
              $stmtC->execute();
              $resC = $stmtC->get_result();
              if ($resC && $resC->num_rows > 0) {
                $r = $resC->fetch_assoc();
                $cart_count = (int)$r['total_qty'];
              }
              $stmtC->close();
            }
          }
          $stmtU->close();
        }
      }
      ?>

      <!-- Show cart only for guest OR user (not for admin/superadmin) -->
      <?php if (!$is_admin && !$is_super): ?>
        <button id="btn-cart" class="cart-btn" aria-label="Keranjang belanja">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="10.5" cy="19.5" r="1.5" fill="currentColor"/>
            <circle cx="18.5" cy="19.5" r="1.5" fill="currentColor"/>
          </svg>
          <span style="font-weight:700">Keranjang</span>
          <?php if ($is_user && $cart_count > 0): ?>
            <span class="cart-badge" aria-live="polite"><?php echo $cart_count; ?></span>
          <?php endif; ?>
        </button>
      <?php endif; ?>

      <!-- USER -->
      <?php if ($is_user): ?>
        <div class="dropdown" style="margin-left:6px;">
          <button class="account-btn secondary" aria-haspopup="true" aria-expanded="false">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 100-8 4 4 0 000 8zM3 21a9 9 0 0118 0" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
          </button>
          <div class="dropdown-menu" role="menu" aria-label="Akun user">
            <a class="dropdown-item" href="edit_akun.php">✏️ Edit Akun</a>
            <a class="dropdown-item" href="hapus_akun.php">🗑️ Hapus Akun</a>
            <a class="dropdown-item warn" href="logout.php">🚪 Logout</a>
          </div>
        </div>

      <!-- ADMIN -->
      <?php elseif ($is_admin): ?>
        <a class="link" href="admin_dashboard.php">Dashboard</a>
        

        <div class="account" style="margin-left:6px">
          <div class="account-btn secondary" style="padding:8px 12px;background:transparent;border:1px solid rgba(255,255,255,0.04)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 12a4 4 0 100-8 4 4 0 000 8zM3 21a9 9 0 0118 0" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span><?php echo htmlspecialchars($_SESSION['admin']); ?></span>
          </div>
          <a class="link" href="logout.php" style="margin-left:8px">Logout</a>
        </div>

      
      <!-- GUEST -->
      <?php else: ?>
        <a class="link primary" href="user_login.php?as=user" style="margin-left:6px">Login</a>
      <?php endif; ?>

    </nav>
  </div>
</header>

<!-- Modal: prompt login for cart (shown for guests) - improved -->
<div id="modal-login-cart" class="modal-backdrop" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="modal" role="document" aria-labelledby="modal-login-cart-title">
    <div class="modal-icon" aria-hidden="true">🛒</div>
    <div class="modal-content">
      <h3 id="modal-login-cart-title">Silakan masuk terlebih dahulu</h3>
      <p>Fitur keranjang hanya tersedia untuk pengguna yang masuk. Masuk sekarang untuk melihat isi keranjang, menambahkan produk, dan melanjutkan pesanan.</p>
      <ul style="color:var(--muted);margin:8px 0 12px;padding-left:18px">
        <li>Setelah login, Anda akan diarahkan kembali ke halaman Keranjang.</li>
        <li>Jika belum punya akun, pilih "Daftar" pada halaman login.</li>
      </ul>

      <div class="modal-actions">
        <button id="modal-close" class="btn-ghost" type="button">Batal</button>
        <a id="modal-login-btn" class="btn-cta" href="admin_login.php?as=user&redirect=cart.php">Login / Daftar</a>
      </div>
    </div>
  </div>
</div>

<!-- Toast (for short messages) -->
<div id="toast" class="toast" role="status" aria-live="polite"></div>

<script>
(function(){
  const isUser = <?php echo json_encode($is_user ? true : false); ?>;
  const isAdmin = <?php echo json_encode($is_admin ? true : false); ?>;
  const isSuper = <?php echo json_encode($is_super ? true : false); ?>;

  const btnCart = document.getElementById('btn-cart');
  const modal = document.getElementById('modal-login-cart');
  const toast = document.getElementById('toast');
  const modalClose = document.getElementById('modal-close');
  const modalLoginBtn = document.getElementById('modal-login-btn');

  function showModal() {
    if (!modal) return;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden','false');
    // focus the primary action for keyboard users
    setTimeout(()=>{ if (modalLoginBtn) modalLoginBtn.focus(); }, 120);
  }
  function hideModal() {
    if (!modal) return;
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden','true');
  }
  function showToast(msg,ms=1600) {
    if (!toast) return;
    toast.textContent = msg;
    toast.style.display = 'block';
    setTimeout(()=>{ toast.style.display='none'; }, ms);
  }

  if (btnCart) {
    btnCart.addEventListener('click', function(e){
      // - user -> go to cart
      // - admin/super -> show toast (not available)
      // - guest -> show modal prompting login
      if (isUser) {
        window.location.href = 'cart.php';
        return;
      }
      if (isAdmin || isSuper) {
        showToast('Fitur keranjang hanya untuk pengguna biasa.');
        return;
      }
      // guest
      showModal();
    });
  }

  modalClose && modalClose.addEventListener('click', hideModal);

  // close modal on backdrop click (but not on inner modal)
  window.addEventListener('click', function(e){
    if (e.target === modal) hideModal();
  });

  // ESC to close
  window.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && modal && modal.style.display === 'flex') hideModal();
  });

})();
</script>

<main>
<!-- page content starts here -->
