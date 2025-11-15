<?php
session_start();
include 'koneksi.php';
include 'templates/header.php';

// Cek login
if(!isset($_SESSION['admin'])){
  header("Location: admin_login.php");
  exit;
}

$admin = $_SESSION['admin'];
?>

<style>
body {
  background: #f8fafc;
  font-family: 'Poppins', sans-serif;
  margin: 0;
  color: #1e293b;
}

/* ===== NAVBAR ===== */
.navbar-admin {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #1e293b;
  color: #fff;
  padding: 15px 30px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.15);
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
}

.navbar-left .avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #3b82f6;
  display: flex;
  justify-content: center;
  align-items: center;
  color: white;
  font-weight: bold;
  font-size: 18px;
}

.navbar-right {
  display: flex;
  gap: 10px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  color: #fff;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-blue { background: #2563eb; }
.btn-blue:hover { background: #1d4ed8; }

.btn-green { background: #16a34a; }
.btn-green:hover { background: #15803d; }

.btn-red { background: #dc2626; }
.btn-red:hover { background: #b91c1c; }

/* ===== DASHBOARD ===== */
.dashboard-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 70vh;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
  width: 90%;
  max-width: 1000px;
  margin: 40px auto;
}

.dashboard-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  padding: 25px;
  text-align: center;
  transition: transform 0.2s ease;
}
.dashboard-card:hover {
  transform: translateY(-5px);
}

.dashboard-card h3 {
  font-size: 18px;
  margin-bottom: 10px;
  color: #1e293b;
}

.dashboard-card .value {
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 15px;
  color: #2563eb;
}

/* ===== MODAL ===== */
.modal {
  display: none;
  position: fixed;
  z-index: 10;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
}

.modal-content {
  background-color: #fff;
  margin: 10% auto;
  padding: 25px 20px;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.modal-content input {
  width: 90%;
  padding: 10px;
  margin-bottom: 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
}

.modal-content h3 {
  margin-bottom: 15px;
  color: #0f172a;
}
</style>

<!-- ===== NAVBAR ===== -->
<div class="navbar-admin">
  <div class="navbar-left">
    <div class="avatar"><?php echo strtoupper(substr($admin, 0, 1)); ?></div>
    <span>Login sebagai <strong><?php echo htmlspecialchars($admin); ?></strong></span>
  </div>
  <div class="navbar-right">
    <a href="tambah_admin.php" class="btn btn-green">Tambah Akun</a>
    <a href="hapus_akun.php" class="btn btn-red">Hapus Akun</a>
  </div>
</div>

<!-- ===== DASHBOARD CARD ===== -->
<div class="dashboard-container">
  <div class="dashboard-grid">

    <!-- Total Produk -->
    <div class="dashboard-card">
      <h3>Total Produk</h3>
      <div class="value">
        <?php
          $q = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM produk");
          $d = mysqli_fetch_assoc($q);
          echo $d['total'];
        ?>
      </div>
      <a href="produk_list.php" class="btn btn-blue">Kelola Produk</a>
    </div>

    <!-- Tambah Produk -->
    <div class="dashboard-card">
      <h3>Tambah Produk</h3>
      <div class="value">+</div>
      <a href="produk_tambah.php" class="btn btn-green">Tambah Baru</a>
    </div>

  </div>
</div>

<!-- ===== MODAL HAPUS AKUN ===== -->


<script>
function openModal() {
  document.getElementById("deleteModal").style.display = "block";
}
function closeModal() {
  document.getElementById("deleteModal").style.display = "none";
}
</script>

<?php
// ===== PROSES HAPUS AKUN =====
if(isset($_POST['hapus_akun'])){
  $password = mysqli_real_escape_string($koneksi, $_POST['password']);
  $username = $admin; // hanya akun login sendiri yang bisa dihapus

  $cek = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username' AND password='$password'");
  if(mysqli_num_rows($cek) > 0){
    mysqli_query($koneksi, "DELETE FROM admin WHERE username='$username'");
    echo "<script>alert('Akun anda berhasil dihapus.'); window.location='logout.php';</script>";
  } else {
    echo "<script>alert('Password salah! Akun tidak dihapus.');</script>";
  }
}
?>

<?php include 'templates/footer.php'; ?>
