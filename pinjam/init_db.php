<?php
// Database configuration
$host = 'localhost';
$dbname = 'pinjam_db';
$username = 'root';
$password = '';

// Create database if it doesn't exist
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' created successfully.\n";
} catch(PDOException $e) {
    die("Error creating database: " . $e->getMessage() . "\n");
}

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error connecting to database: " . $e->getMessage() . "\n");
}

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS alat (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        deskripsi TEXT,
        kategori VARCHAR(100),
        stock INT DEFAULT 0,
        status ENUM('Tersedia', 'Tidak Tersedia') DEFAULT 'Tersedia',
        foto VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_nama (nama),
        INDEX idx_kategori (kategori),
        INDEX idx_stock (stock),
        INDEX idx_status (status)
    )",

    "CREATE TABLE IF NOT EXISTS anggota (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        kelas VARCHAR(100),
        kontak VARCHAR(50),
        status ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_nama (nama),
        INDEX idx_kelas (kelas),
        INDEX idx_status (status)
    )",

    "CREATE TABLE IF NOT EXISTS peminjaman (
        id INT AUTO_INCREMENT PRIMARY KEY,
        alat_id INT NOT NULL,
        nama_alat VARCHAR(255) NOT NULL,
        peminjam VARCHAR(255) NOT NULL,
        tanggal_mulai DATE NOT NULL,
        tanggal_selesai DATE NOT NULL,
        status ENUM('Aktif', 'Selesai', 'Terlambat') DEFAULT 'Aktif',
        keterangan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (alat_id) REFERENCES alat(id) ON DELETE CASCADE,
        INDEX idx_alat_id (alat_id),
        INDEX idx_status (status),
        INDEX idx_peminjam (peminjam),
        INDEX idx_tanggal_mulai (tanggal_mulai),
        INDEX idx_tanggal_selesai (tanggal_selesai)
    )",

    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('warning', 'error', 'info', 'success') NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        time VARCHAR(100),
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $table) {
    try {
        $pdo->exec($table);
        echo "Table created successfully.\n";
    } catch(PDOException $e) {
        echo "Error creating table: " . $e->getMessage() . "\n";
    }
}

// Insert sample data
$sampleData = [
    "INSERT IGNORE INTO alat (id, nama, deskripsi, kategori, stock, status, foto) VALUES
    (1, 'Laptop Dell XPS 15', 'Laptop untuk desain grafis dan programming dengan spesifikasi tinggi', 'Elektronik', 3, 'Tersedia', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&h=300&fit=crop'),
    (2, 'Proyektor Epson', 'Proyektor untuk presentasi dengan resolusi HD', 'Elektronik', 3, 'Tersedia', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&h=300&fit=crop'),
    (3, 'Kamera Canon EOS 80D', 'Kamera DSLR untuk fotografi profesional', 'Fotografi', 3, 'Tersedia', 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=500&h=300&fit=crop'),
    (4, 'Mikrofon Rode NT1', 'Mikrofon kondenser untuk rekaman studio', 'Audio', 3, 'Tersedia', 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=500&h=300&fit=crop'),
    (5, 'Tablet iPad Pro 12.9\"', 'Tablet premium untuk kreativitas dan produktivitas', 'Elektronik', 2, 'Tersedia', 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500&h=300&fit=crop'),
    (6, 'Speaker Bluetooth JBL', 'Speaker portabel dengan kualitas suara tinggi', 'Audio', 5, 'Tersedia', 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&h=300&fit=crop'),
    (7, 'Drone DJI Mini 3', 'Drone ringan untuk fotografi udara dan videografi', 'Fotografi', 1, 'Tersedia', 'https://images.unsplash.com/photo-1473968512647-3e447244af8f?w=500&h=300&fit=crop'),
    (8, 'Monitor LG 27\"', 'Monitor 4K untuk editing video dan desain', 'Elektronik', 4, 'Tersedia', 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&h=300&fit=crop'),
    (9, 'Headphone Sony WH-1000XM4', 'Headphone noise-cancelling untuk musik dan panggilan', 'Audio', 0, 'Tersedia', 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=500&h=300&fit=crop')",

    "INSERT IGNORE INTO anggota (id, nama, kelas, kontak, status) VALUES
    (1, 'Ahmad Rahman', 'XII RPL 1', '081234567890', 'Aktif'),
    (2, 'Siti Nurhaliza', 'XII RPL 2', '081234567891', 'Aktif'),
    (3, 'Budi Santoso', 'XII TKJ 1', '081234567892', 'Aktif'),
    (4, 'Maya Sari', 'XII MM 1', '081234567893', 'Aktif'),
    (5, 'Rizki Pratama', 'XII RPL 1', '081234567894', 'Aktif'),
    (6, 'Dewi Lestari', 'XII TKJ 2', '081234567895', 'Aktif'),
    (7, 'Fajar Nugroho', 'XII MM 2', '081234567896', 'Aktif'),
    (8, 'Intan Permata', 'XII RPL 2', '081234567897', 'Aktif'),
    (9, 'Gilang Ramadhan', 'XII TKJ 1', '081234567898', 'Aktif'),
    (10, 'Nadia Putri', 'XII MM 1', '081234567899', 'Aktif')",

    "INSERT IGNORE INTO peminjaman (id, alat_id, nama_alat, peminjam, tanggal_mulai, tanggal_selesai, status) VALUES
    (1, 2, 'Proyektor Epson', 'Budi Santoso', '2023-10-20', '2023-10-25', 'Aktif'),
    (2, 3, 'Kamera Canon EOS 80D', 'Siti Nurhaliza', '2023-10-15', '2023-10-18', 'Selesai'),
    (3, 1, 'Laptop Dell XPS 15', 'Ahmad Rahman', '2023-10-10', '2023-10-13', 'Selesai'),
    (4, 4, 'Mikrofon Rode NT1', 'Maya Sari', '2023-10-08', '2023-10-12', 'Selesai'),
    (5, 5, 'Tablet iPad Pro 12.9"', 'Rizki Pratama', '2023-10-05', '2023-10-09', 'Selesai'),
    (6, 6, 'Speaker Bluetooth JBL', 'Dewi Lestari', '2023-10-01', '2023-10-04', 'Selesai'),
    (7, 8, 'Monitor LG 27"', 'Fajar Nugroho', '2023-09-28', '2023-10-02', 'Selesai'),
    (8, 2, 'Proyektor Epson', 'Intan Permata', '2023-09-25', '2023-09-29', 'Selesai'),
    (9, 3, 'Kamera Canon EOS 80D', 'Gilang Ramadhan', '2023-09-20', '2023-09-23', 'Selesai'),
    (10, 1, 'Laptop Dell XPS 15', 'Nadia Putri', '2023-09-15', '2023-09-19', 'Selesai')"
];

foreach ($sampleData as $data) {
    try {
        $pdo->exec($data);
        echo "Sample data inserted successfully.\n";
    } catch(PDOException $e) {
        echo "Error inserting sample data: " . $e->getMessage() . "\n";
    }
}

echo "Database initialization completed!\n";
?>
