<?php 
include 'koneksi.php'; 
include 'templates/header.php'; 
?>

<style>
  .produk-img-wrapper {
  width: 100%;
  aspect-ratio: 890 / 600; 
  overflow: hidden;
  border-bottom: 1px solid #e5e7eb;
}

.produk-img {
  width: 100%;
  height: 100%;
  object-fit: cover; 
  display: block;
}
</style>

<main class="container">

  <h2 style="margin-bottom:20px;">Galeri Produk</h2>

  <div class="produk-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px;">
    <?php
    $q = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
    while($r = mysqli_fetch_assoc($q)): ?>
      
      <div class="card" style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 2px 5px rgba(0,0,0,0.1);display:flex;flex-direction:column;justify-content:space-between;min-height:350px;">
        
        
        <a href="produk_detail.php?id=<?php echo $r['id_produk']; ?>" 
           style="text-decoration:none;color:inherit;display:block;height:100%;">
          
          <div class="produk-img-wrapper">
            <img src="media/<?php echo htmlspecialchars($r['foto']); ?>" 
                 alt="<?php echo htmlspecialchars($r['nama_produk']); ?>" 
                 class="produk-img">
          </div>

          <div style="padding:15px;">
            <h3 style="font-size:18px;margin:0 0 10px;"><?php echo htmlspecialchars($r['nama_produk']); ?></h3>
            <p style="font-size:14px;color:#555;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;">
              <?php echo nl2br(htmlspecialchars($r['deskripsi'])); ?>
            </p>
          </div>
        </a>

      </div>
      
    <?php endwhile; ?>
  </div>

</main>

<?php include 'templates/footer.php'; ?>
