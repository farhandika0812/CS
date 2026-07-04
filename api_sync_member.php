<?php
// Memanggil koneksi database bawaan CS_Monitor
require_once 'config/config.php'; 
header('Content-Type: application/json');

// Kunci keamanan
$api_key = "PanglimaNet_Sync_2026"; 
if (!isset($_POST['api_key']) || $_POST['api_key'] !== $api_key) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'pesan' => 'Akses Ditolak: API Key tidak valid.']));
}

global $conn; 

// Menangkap 3 data utama dari server DirectAdmin
$id_billing     = $conn->real_escape_string($_POST['va']);
$nama_member    = $conn->real_escape_string($_POST['nama']); 
$tanggal_daftar = $conn->real_escape_string($_POST['tanggal_daftar']); 

// Cek apakah pelanggan dengan VA/id_billing ini sudah ada
$cek = $conn->query("SELECT id_billing FROM pelanggan WHERE id_billing = '$id_billing'");

if ($cek && $cek->num_rows > 0) {
    // UPDATE: Memperbarui id_pelanggan dan created_at
    // Karena format tanggal_daftar biasanya YYYY-MM-DD, kita tambahkan waktu default 00:00:00 untuk format DATETIME
    $sql = "UPDATE pelanggan SET 
            id_pelanggan = '$nama_member', 
            nama = '$nama_member',
            created_at = '$tanggal_daftar 00:00:00',
            last_update = NOW()
            WHERE id_billing = '$id_billing'";
} else {
    // INSERT: Memasukkan nama member langsung ke id_pelanggan
    $sql = "INSERT INTO pelanggan (id_pelanggan, id_billing, nama, status_ping, status_berlangganan, created_at) 
            VALUES ('$nama_member', '$id_billing', '$nama_member', 'OFFLINE', 'Aktif', '$tanggal_daftar 00:00:00')";
}

if ($conn->query($sql)) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'pesan' => $conn->error]);
}
?>