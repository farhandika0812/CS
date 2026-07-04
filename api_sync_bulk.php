<?php
// Set Header JSON
header('Content-Type: application/json');

// Kunci Keamanan
$api_key = "PanglimaNet_Sync_2026"; 
if (!isset($_POST['api_key']) || $_POST['api_key'] !== $api_key) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'pesan' => 'Akses Ditolak: API Key tidak valid.']));
}

// Menangkap payload dari DirectAdmin
$data_bulk = json_decode($_POST['data_pelanggan'], true);
if(!$data_bulk) {
    die(json_encode(['status' => 'error', 'pesan' => 'Data kosong atau gagal di-decode']));
}

// ==============================================================
// KONEKSI DATABASE MANUAL (Ganti dengan kredensial FastPanel Anda)
// ==============================================================
$host = "localhost";
$user = "cspanglimanet";
$pass = "bcwJl%yLs1r/)8v2";
$db   = "cs_panglima";

$conn = new mysqli($host, $user, $pass, $db);

// Cek jika koneksi gagal
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'pesan' => 'Koneksi DB Gagal: ' . $conn->connect_error]));
}

$jumlah_sukses = 0;

// Memulai Transaksi agar proses simpan 5000 data secepat kilat
$conn->begin_transaction();

foreach($data_bulk as $row) {
    $id_billing     = $conn->real_escape_string($row['va']);
    $nama           = $conn->real_escape_string($row['nama']);
    
    // Default ke hari ini jika tanggal kosong
    $tanggal_daftar = !empty($row['tanggal_daftar']) ? $conn->real_escape_string($row['tanggal_daftar']) : date('Y-m-d'); 
    
    // Cek keberadaan data
    $cek = $conn->query("SELECT id_billing FROM pelanggan WHERE id_billing = '$id_billing'");
    
    if ($cek && $cek->num_rows > 0) {
        // UPDATE jika sudah ada
        $conn->query("UPDATE pelanggan SET 
            id_pelanggan = '$nama', 
            nama = '$nama',
            created_at = '$tanggal_daftar 00:00:00',
            last_update = NOW()
            WHERE id_billing = '$id_billing'");
    } else {
        // INSERT jika data baru
        $insert = $conn->query("INSERT INTO pelanggan (id_pelanggan, id_billing, nama, status_ping, status_berlangganan, created_at) 
            VALUES ('$nama', '$id_billing', '$nama', 'OFFLINE', 'Aktif', '$tanggal_daftar 00:00:00')");
        
        // Proteksi nama ganda (Duplicate Entry Primary Key)
        if (!$insert) {
            $nama_unik = $nama . "-" . rand(10,99);
            $conn->query("INSERT INTO pelanggan (id_pelanggan, id_billing, nama, status_ping, status_berlangganan, created_at) 
            VALUES ('$nama_unik', '$id_billing', '$nama', 'OFFLINE', 'Aktif', '$tanggal_daftar 00:00:00')");
        }
    }
    $jumlah_sukses++;
}

// Eksekusi semua secara bersamaan
$conn->commit(); 
$conn->close();

echo json_encode(['status' => 'success', 'diproses' => $jumlah_sukses]);
?>