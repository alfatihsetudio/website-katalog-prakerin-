<?php
include 'koneksi.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if(empty($_SESSION['admin'])){ header("Location: admin_login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$cur = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto FROM produk WHERE id_produk=$id"));
if($cur){
  $path = __DIR__ . '/media/' . $cur['foto'];
  if(is_file($path)) @unlink($path);
  mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk=$id");
}
header("Location: produk_list.php");
exit;
