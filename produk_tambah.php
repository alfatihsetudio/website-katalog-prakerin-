<?php 
include 'koneksi.php'; 
if (session_status()===PHP_SESSION_NONE) session_start();
if(empty($_SESSION['admin'])){ 
  header("Location: admin_login.php"); 
  exit; 
}
include 'templates/header.php'; 
?>

<style>
/* ===== Header Halaman ===== */
.page-header {
  text-align: center;
  margin: 30px 0;
}
.page-header h2 {
  font-size: 26px;
  font-weight: bold;
  color: #333;
}

/* ===== Card Form ===== */
.card {
  max-width: 700px;
  margin: 0 auto 40px;
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* ===== Form ===== */
.form label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #444;
}
.form input[type="text"],
.form input[type="number"],
.form input[type="file"],
.form textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  margin-bottom: 15px;
  font-size: 14px;
  transition: border 0.2s;
}
.form input:focus,
.form textarea:focus {
  border-color: #2563eb;
  outline: none;
}

/* ===== Tombol Seragam ===== */
.btn {
  display: inline-block;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  font-size: 14px;
  transition: background 0.2s, border 0.2s;
}

/* Primary Button */
.btn-primary {
  background: #2563eb;
  color: #fff;
  border: none;
}
.btn-primary:hover {
  background: #1d4ed8;
}

/* Secondary Button */
.btn-secondary {
  background: #f3f4f6;
  color: #333;
  border: 1px solid #ddd;
}
.btn-secondary:hover {
  background: #e5e7eb;
}

/* ===== Alert ===== */
.alert {
  margin-top: 15px;
  padding: 12px 16px;
  border-radius: 8px;
  font-weight: 500;
}
.alert.ok {
  background: #dcfce7;
  color: #166534;
}
.alert.err {
  background: #fee2e2;
  color: #991b1b;
}
</style>

<div class="page-header">
  <h2>Tambah Produk</h2>
</div>

<div class="card">
  <form class="form" method="post" enctype="multipart/form-data">
    <label>Nama Produk</label>
    <input type="text" name="nama_produk" required>

    <label>Deskripsi</label>
    <textarea name="deskripsi" rows="4"></textarea>

    <label>Harga</label>
    <input type="number" name="harga" required>

    <label>Bahan</label>
    <input type="text" name="bahan" required>

    <label>Minimal Order</label>
    <input type="number" name="min_order" required>

    <label>Keterangan</label>
    <textarea name="keterangan" rows="3"></textarea>

    <label>Foto (jpg/png/webp)</label>
    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp" required>

    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
    <a href="produk_list.php" class="btn btn-secondary" style="margin-left:8px">Batal</a>
  </form>

  <?php
  if(isset($_POST['simpan'])){
    $nama   = trim($_POST['nama_produk']);
    $desk   = trim($_POST['deskripsi']);
    $harga  = (int)$_POST['harga'];
    $bahan  = trim($_POST['bahan']);
    $min    = (int)$_POST['min_order'];
    $ket    = trim($_POST['keterangan']);

    if(!isset($_FILES['foto']) || $_FILES['foto']['error']!==UPLOAD_ERR_OK){
      echo '<div class="alert err">Upload foto gagal.</div>';
    } else {
      $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','webp'];
      if(!in_array($ext,$allowed)){
        echo '<div class="alert err">Format file tidak didukung.</div>';
      } else {
        $newName = 'prod_'.time().'_'.mt_rand(1000,9999).'.'.$ext;
        $dest = __DIR__ . '/media/' . $newName;
        if(move_uploaded_file($_FILES['foto']['tmp_name'], $dest)){
          $stmt = mysqli_prepare($koneksi,
            "INSERT INTO produk (nama_produk, deskripsi, harga, bahan, min_order, keterangan, foto) 
             VALUES (?,?,?,?,?,?,?)"
          );
          mysqli_stmt_bind_param($stmt,"ssisiis",$nama,$desk,$harga,$bahan,$min,$ket,$newName);
          mysqli_stmt_execute($stmt);
          mysqli_stmt_close($stmt);

          echo '<div class="alert ok">Produk tersimpan. <a href="produk_list.php">Kembali ke daftar</a></div>';
        } else {
          echo '<div class="alert err">Gagal memindahkan file.</div>';
        }
      }
    }
  }
  ?>
</div>

<?php include 'templates/footer.php'; ?>
