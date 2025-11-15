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
.page-header {
  text-align: center;
  margin: 30px 0;
}

.page-header h2 {
  font-size: 26px;
  font-weight: bold;
  color: #333;
}

.add-btn {
  display: inline-block;
  padding: 10px 16px;
  background: #22c55e;
  color: #fff;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: background 0.2s;
}
.add-btn:hover {
  background: #16a34a;
}

.table-container {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 20px;
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.table th, 
.table td {
  border-bottom: 1px solid #eee;
  padding: 12px;
  text-align: left;
  vertical-align: top;
}

.table th {
  background: #f9fafb;
  font-weight: 600;
  color: #444;
}

.table td img {
  border-radius: 8px;
  max-height: 120px;
  object-fit: cover;
  display: block;
}

.actions {
  display: flex;
  gap: 8px;
}

.actions a {
  padding: 6px 12px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: background 0.2s;
}

.actions a.edit {
  background: #2563eb;
  color: #fff;
}
.actions a.edit:hover {
  background: #1d4ed8;
}

.actions a.delete {
  background: #dc2626;
  color: #fff;
}
.actions a.delete:hover {
  background: #b91c1c;
}
</style>

<div class="page-header">
  <h2>Data Produk</h2>
</div>

<div class="table-container">
  <table class="table">
    <tr>
      <th>ID</th>
      <th>Foto</th>
      <th>Nama</th>
      <th>Deskripsi</th>
      <th>Harga</th>
      <th>Bahan</th>
      <th>Minimal Order</th>
      <th>Keterangan</th>
      <th>Aksi</th>
    </tr>
    <?php
    $q = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
    while($r = mysqli_fetch_assoc($q)): ?>
      <tr>
        <td><?php echo $r['id_produk']; ?></td>
        <td>
          <img src="media/<?php echo htmlspecialchars($r['foto']); ?>" 
               alt="<?php echo htmlspecialchars($r['nama_produk']); ?>">
        </td>
        <td>
          <a href="produk_detail.php?id=<?php echo $r['id_produk']; ?>" target="_blank">
            <?php echo htmlspecialchars($r['nama_produk']); ?>
          </a>
        </td>
        <td><?php echo nl2br(htmlspecialchars($r['deskripsi'])); ?></td>
        <td>
  <?php 
    if (!empty($r['harga'])) {
      echo 'Rp. ' . number_format((float)$r['harga'], 0, ',', '.');
    } else {
      echo '-';
    }
  ?>
</td>

        <td><?php echo htmlspecialchars($r['bahan']); ?></td>
        <td><?php echo htmlspecialchars($r['min_order']); ?></td>
        <td><?php echo nl2br(htmlspecialchars($r['keterangan'])); ?></td>
        <td class="actions">
          <a href="produk_edit.php?id=<?php echo $r['id_produk']; ?>" class="edit">Edit</a>
          <a href="produk_hapus.php?id=<?php echo $r['id_produk']; ?>" class="delete" onclick="return confirm('Hapus produk ini?');">Hapus</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<?php include 'templates/footer.php'; ?>
