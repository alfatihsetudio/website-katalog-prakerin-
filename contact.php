<?php
// contact.php
// Pastikan file ini disimpan sebagai contact.php

include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();
include 'templates/header.php';

// --- optional: buat CSRF token sederhana jika belum ada (berguna bila project lain memakai token) ---
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$csrf = $_SESSION['csrf_token'];

// ====== Pastikan tabel 'kontak' sudah ada (asumsi sudah ada) ======

// ====== Pastikan tabel 'halaman' ada untuk menyimpan 'about' ======
$create_hal_query = "
CREATE TABLE IF NOT EXISTS halaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) NOT NULL UNIQUE,
  judul VARCHAR(255),
  konten TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
mysqli_query($koneksi, $create_hal_query);

// ===== Ambil / buat data kontak (id=1) =====
$res = mysqli_query($koneksi, "SELECT * FROM kontak WHERE id=1");

if (!$res || mysqli_num_rows($res) === 0) {
  // default kontak
  $default_alamat = "Jl. KH Abdul Fatah Hasan No.61, Cipare, Kec. Serang, Kota Serang, Banten 42118";
  $default_email  = "sayuticom@yahoo.com";
  $default_wa     = "087773456733";

  $ins = mysqli_prepare($koneksi, "INSERT INTO kontak (alamat, email, wa) VALUES (?, ?, ?)");
  mysqli_stmt_bind_param($ins, "sss", $default_alamat, $default_email, $default_wa);
  mysqli_stmt_execute($ins);
  mysqli_stmt_close($ins);

  $res = mysqli_query($koneksi, "SELECT * FROM kontak WHERE id=1");
}
$kontak = mysqli_fetch_assoc($res);

// ===== Ambil / buat halaman 'about' =====
$res2 = mysqli_prepare($koneksi, "SELECT * FROM halaman WHERE slug = ?");
$slug = 'about';
mysqli_stmt_bind_param($res2, "s", $slug);
mysqli_stmt_execute($res2);
$r2 = mysqli_stmt_get_result($res2);

if (!$r2 || mysqli_num_rows($r2) === 0) {
  // default konten About
  $default_judul = "Tentang Kami";
  $default_konten = <<<HTML
<strong>CV.SAYUTI.COM</strong> adalah perusahaan yang bergerak di bidang percetakan 
dan media visual dengan komitmen untuk menghadirkan solusi cetak yang inovatif, berkualitas, 
dan tepat waktu. Sejak awal berdiri, kami telah melayani berbagai kebutuhan percetakan mulai 
dari <em>cetak digital</em>, <em>offset</em>, hingga media promosi visual yang mendukung keperluan 
bisnis, instansi, maupun individu.

Dengan dukungan mesin cetak modern, tenaga profesional, dan layanan yang responsif, 
CV SAYUTI.COM berfokus untuk memberikan hasil cetakan terbaik yang sesuai 
dengan harapan pelanggan. Kami tidak hanya menghadirkan produk berkualitas, tetapi juga 
menawarkan konsultasi desain dan solusi kreatif agar setiap media cetak mampu menyampaikan 
pesan secara maksimal.

Kami percaya bahwa <strong>kepuasan pelanggan</strong> adalah prioritas utama. Oleh karena itu, 
kami selalu berusaha menjaga kecepatan, presisi, serta mutu layanan dalam setiap proses produksi.

<h3>Visi</h3>
Menjadi perusahaan percetakan terpercaya dan terdepan dalam penyediaan media visual di Indonesia.

<h3>Misi</h3>
<ul>
<li>Menyediakan layanan percetakan berkualitas tinggi dengan harga kompetitif.</li>
<li>Memberikan solusi kreatif dan inovatif dalam media cetak serta visual.</li>
<li>Menjaga kepuasan pelanggan melalui pelayanan profesional dan tepat waktu.</li>
<li>Berkontribusi dalam mendukung perkembangan bisnis dan kebutuhan komunikasi visual masyarakat.</li>
</ul>
HTML;

  $ins2 = mysqli_prepare($koneksi, "INSERT INTO halaman (slug, judul, konten) VALUES (?, ?, ?)");
  mysqli_stmt_bind_param($ins2, "sss", $slug, $default_judul, $default_konten);
  mysqli_stmt_execute($ins2);
  mysqli_stmt_close($ins2);

  // ambil ulang
  $res2 = mysqli_prepare($koneksi, "SELECT * FROM halaman WHERE slug = ?");
  mysqli_stmt_bind_param($res2, "s", $slug);
  mysqli_stmt_execute($res2);
  $r2 = mysqli_stmt_get_result($res2);
}

$about = mysqli_fetch_assoc($r2);

// ===== Handle update dari admin (kontak + about) =====
$notice = "";
if (!empty($_SESSION['admin']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
  // CSRF check (jika token ada)
  if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $notice = "<div class='alert-err'>Token tidak valid. Coba lagi.</div>";
  } else {
    // ambil dan trim
    $alamat = trim($_POST['alamat'] ?? "");
    $email  = trim($_POST['email'] ?? "");
    $wa     = trim($_POST['wa'] ?? "");
    $judul_about = trim($_POST['judul_about'] ?? "");
    $konten_about = trim($_POST['konten_about'] ?? "");

    // validasi minimal
    if ($alamat === "" || $email === "" || $wa === "" || $judul_about === "" || $konten_about === "") {
      $notice = "<div class='alert-err'>Semua field harus diisi.</div>";
    } else {
      // update kontak
      $stmt = mysqli_prepare($koneksi, "UPDATE kontak SET alamat = ?, email = ?, wa = ? WHERE id = 1");
      mysqli_stmt_bind_param($stmt, "sss", $alamat, $email, $wa);
      $ok1 = mysqli_stmt_execute($stmt);
      mysqli_stmt_close($stmt);

      // update about (halaman)
      $stmt2 = mysqli_prepare($koneksi, "UPDATE halaman SET judul = ?, konten = ? WHERE slug = ?");
      mysqli_stmt_bind_param($stmt2, "sss", $judul_about, $konten_about, $slug);
      $ok2 = mysqli_stmt_execute($stmt2);
      mysqli_stmt_close($stmt2);

      if ($ok1 && $ok2) {
        $notice = "<div class='alert-success'>✅ Perubahan berhasil disimpan!</div>";
        // ambil ulang data terbaru
        $res = mysqli_query($koneksi, "SELECT * FROM kontak WHERE id=1");
        $kontak = mysqli_fetch_assoc($res);

        $res2 = mysqli_prepare($koneksi, "SELECT * FROM halaman WHERE slug = ?");
        mysqli_stmt_bind_param($res2, "s", $slug);
        mysqli_stmt_execute($res2);
        $r2 = mysqli_stmt_get_result($res2);
        $about = mysqli_fetch_assoc($r2);
      } else {
        $notice = "<div class='alert-err'>Terjadi kesalahan saat menyimpan. Coba lagi.</div>";
      }
    }
  }
}

?>

<style>
/* Judul halaman */
.page-title {
  text-align: center;
  font-size: 28px;
  font-weight: 700;
  margin: 40px 0 25px;
  color: #222;
}

/* Container utama */
.contact-container {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  max-width: 900px;
  margin: 0 auto 60px;
  padding: 0 20px;
}

/* Card */
.contact-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Form admin */
.contact-card form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.contact-card label {
  font-weight: 600;
  color: #1e293b;
}

.contact-card textarea,
.contact-card input {
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 10px;
  font-size: 15px;
  outline: none;
}

.contact-card textarea:focus,
.contact-card input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
}

/* Tombol simpan */
.contact-card button {
  background-color: #2563eb;
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
  width: fit-content;
}

.contact-card button:hover {
  background-color: #1d4ed8;
}

/* Notifikasi */
.alert-success {
  color: #0f5132;
  background: #d1e7dd;
  padding: 10px 15px;
  border-radius: 8px;
  margin-top: 15px;
  border: 1px solid #badbcc;
}

.alert-err {
  color: #842029;
  background: #f8d7da;
  padding: 10px 15px;
  border-radius: 8px;
  margin-top: 15px;
  border: 1px solid #f5c2c7;
}

/* about content styles */
.about-us {
  margin: 40px auto;
  line-height: 1.8;
  color: #1e293b;
}
.about-us h2 { font-size: 28px; margin-bottom: 15px; color:#0f172a; text-align:center; }
</style>

<h2 class="page-title">Kontak</h2>

<div class="contact-container">

  <div class="contact-card">
    <?php if (!empty($_SESSION['admin'])): ?>
      <!-- ADMIN MODE: form untuk kontak + about -->
      <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
        <h3 style="margin:0 0 8px 0;">Data Kontak</h3>
        <label>Alamat:</label>
        <textarea name="alamat" rows="3" required><?php echo htmlspecialchars($kontak['alamat'] ?? ''); ?></textarea>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($kontak['email'] ?? ''); ?>" required>

        <label>WhatsApp:</label>
        <input type="text" name="wa" value="<?php echo htmlspecialchars($kontak['wa'] ?? ''); ?>" required>

        <hr style="margin:15px 0;border:none;border-top:1px solid #eee;">

        <h3 style="margin:0 0 8px 0;">Konten Tentang Kami</h3>
        <label>Judul:</label>
        <input type="text" name="judul_about" value="<?php echo htmlspecialchars($about['judul'] ?? 'Tentang Kami'); ?>" required>

        <label>Konten (HTML diperbolehkan):</label>
        <textarea name="konten_about" rows="12" required><?php echo htmlspecialchars($about['konten'] ?? ''); ?></textarea>

        <button type="submit" name="simpan">Simpan Perubahan</button>
      </form>

      <?php echo $notice; ?>

    <?php else: ?>
      <!-- USER MODE: tampilkan kontak singkat -->
      <p><strong>Alamat:</strong><br><?php echo nl2br(htmlspecialchars($kontak['alamat'] ?? '')); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($kontak['email'] ?? '-'); ?> |
         <strong>WA:</strong> <?php echo htmlspecialchars($kontak['wa'] ?? '-'); ?></p>
    <?php endif; ?>
  </div>

  <div class="contact-card about-us">
    <?php if (!empty($_SESSION['admin'])): ?>
      <!-- admin melihat juga tampilan About (preview) -->
      <div style="margin-bottom:12px;">
        <strong>Preview "Tentang Kami"</strong>
      </div>
      <h2><?php echo htmlspecialchars($about['judul'] ?? 'Tentang Kami'); ?></h2>
      <div>
        <?php
        // karena konten disimpan HTML, tampilkan apa adanya (harus berhati-hati di produksi - XSS)
        // untuk keamanan, Anda bisa memfilter/clean HTML dengan library seperti HTMLPurifier.
        echo $about['konten'] ?? '';
        ?>
      </div>
    <?php else: ?>
      <!-- USER MODE: tampilkan About -->
      <h2><?php echo htmlspecialchars($about['judul'] ?? 'Tentang Kami'); ?></h2>
      <div>
        <?php
        // menampilkan konten HTML yang disimpan
        echo $about['konten'] ?? '';
        ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php include 'templates/footer.php'; ?>
