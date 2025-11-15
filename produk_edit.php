<?php 
include 'koneksi.php'; 
if (session_status()===PHP_SESSION_NONE) session_start();
if(empty($_SESSION['admin'])){ 
  header("Location: admin_login.php"); 
  exit; 
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cur = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk=$id"));
if(!$cur){ 
  header("Location: produk_list.php"); 
  exit; 
}

include 'templates/header.php'; 
?>

<h2 style="margin-left: 30px; color:#0f172a; margin-bottom:15px;">Edit Produk</h2>
<div class="card">
  <form class="form" method="post" enctype="multipart/form-data">
    <label>Nama Produk</label>
    <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($cur['nama_produk']); ?>" required>

    <label>Deskripsi</label>
    <textarea name="deskripsi" rows="4"><?php echo htmlspecialchars($cur['deskripsi']); ?></textarea>

    <!-- Tambahan baru -->
    <label>Harga Produk (Rp)</label>
    <input type="number" name="harga" value="<?php echo htmlspecialchars($cur['harga']); ?>" required min="0">

    <label>Jenis Bahan</label>
    <input type="text" name="bahan" value="<?php echo htmlspecialchars($cur['bahan']); ?>" required>
    <!-- Akhir tambahan baru -->

    <label>Ganti Foto</label>
    <input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">

    <p>Foto sekarang:</p>
    <img src="media/<?php echo htmlspecialchars($cur['foto']); ?>" alt="" style="width:140px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb">

    <button type="submit" name="update" style="margin-top:12px">Update</button>
    <a href="produk_list.php" class="btn-plain" style="margin-left:8px">Batal</a>
  </form>

  <?php
  if(isset($_POST['update'])){
    $nama = trim($_POST['nama_produk']);
    $desk = trim($_POST['deskripsi']);
    $harga = (int)$_POST['harga'];
    $bahan = trim($_POST['bahan']);
    $fotoBaru = $cur['foto'];

    // Upload foto baru jika ada
    if(isset($_FILES['foto']) && $_FILES['foto']['error']===UPLOAD_ERR_OK){
      $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
      if(in_array($ext,['jpg','jpeg','png','webp'])){
        $newName = 'prod_'.time().'_'.mt_rand(1000,9999).'.'.$ext;
        $dest = __DIR__ . '/media/' . $newName;
        if(move_uploaded_file($_FILES['foto']['tmp_name'], $dest)){
          $old = __DIR__ . '/media/' . $cur['foto'];
          if(is_file($old)) @unlink($old);
          $fotoBaru = $newName;
        }
      }
    }

    // Update query disesuaikan
    $stmt = mysqli_prepare($koneksi,"UPDATE produk SET nama_produk=?, deskripsi=?, harga=?, bahan=?, foto=? WHERE id_produk=?");
    mysqli_stmt_bind_param($stmt,"ssissi",$nama,$desk,$harga,$bahan,$fotoBaru,$id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: produk_list.php");
    exit;
  }
  ?>
</div>

<?php include 'templates/footer.php'; ?>
