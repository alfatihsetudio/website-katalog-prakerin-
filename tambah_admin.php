<?php
session_start();
include 'koneksi.php';
include 'templates/header.php';

// Cek apakah admin sudah login
if(!isset($_SESSION['admin'])){
  header("Location: login.php");
  exit;
}
?>

<style>
.add-admin-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 70vh;
}
.add-admin-card {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  width: 100%;
  max-width: 400px;
}
.add-admin-card h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #333;
}
.add-admin-card form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.add-admin-card label {
  font-weight: 500;
}
.add-admin-card input {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
}
.add-admin-card button {
  padding: 12px;
  border: none;
  border-radius: 8px;
  background: #2563eb;
  color: white;
  font-weight: 600;
  cursor: pointer;
}
.add-admin-card button:hover {
  background: #1d4ed8;
}
.alert {
  margin-top: 15px;
  padding: 12px;
  border-radius: 8px;
  font-size: 14px;
}
.alert.success {
  background: #dcfce7;
  color: #166534;
}
.alert.err {
  background: #fee2e2;
  color: #b91c1c;
}
</style>

<div class="add-admin-container">
  <div class="add-admin-card">
    <h2>Tambah Admin Baru</h2>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button type="submit" name="tambah">Tambah Admin</button>
    </form>

    <?php
    if(isset($_POST['tambah'])){
      $username = $_POST['username'];
      $password = $_POST['password'];

      // Cek apakah username sudah ada
      $cek = mysqli_prepare($koneksi, "SELECT id_admin FROM admin WHERE username=?");
      mysqli_stmt_bind_param($cek, "s", $username);
      mysqli_stmt_execute($cek);
      $result = mysqli_stmt_get_result($cek);

      if(mysqli_num_rows($result) > 0){
        echo '<div class="alert err">Username sudah terdaftar.</div>';
      } else {
        // Simpan admin baru
        $stmt = mysqli_prepare($koneksi, "INSERT INTO admin (username, password) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        if(mysqli_stmt_execute($stmt)){
          echo '<div class="alert success">Admin baru berhasil ditambahkan!</div>';
        } else {
          echo '<div class="alert err">Gagal menambah admin. Coba lagi.</div>';
        }
        mysqli_stmt_close($stmt);
      }
      mysqli_stmt_close($cek);
    }
    ?>
  </div>
</div>

<?php include 'templates/footer.php'; ?>
