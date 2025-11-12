
<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Include configuration and cache
require_once 'config.php';
require_once 'cache.php';

// Get admin info from session
$adminUsername = $_SESSION['admin_username'] ?? 'Admin';
$adminNama = $_SESSION['admin_nama'] ?? 'Administrator';
$adminEmail = $_SESSION['admin_email'] ?? '';

// Function to get dashboard stats
function getDashboardStats() {
    global $pdo;

    $cache = new SimpleCache();
    $cacheKey = 'dashboard_stats';

    // Try to get from cache first
    $stats = $cache->get($cacheKey);
    if ($stats !== false) {
        return $stats;
    }

    // Get dashboard statistics with combined query for better performance
    $stats = [];

    // Combined query for total_alat, total_stock, peminjaman_aktif, peminjaman_selesai
    $stmt = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM alat) as total_alat,
            (SELECT SUM(stock) FROM alat) as total_stock,
            (SELECT COUNT(*) FROM peminjaman WHERE status = 'Aktif') as peminjaman_aktif,
            (SELECT COUNT(*) FROM peminjaman WHERE status = 'Selesai') as peminjaman_selesai
    ");
    $result = $stmt->fetch();
    $stats['total_alat'] = $result['total_alat'];
    $stats['total_stock'] = $result['total_stock'];
    $stats['peminjaman_aktif'] = $result['peminjaman_aktif'];
    $stats['peminjaman_selesai'] = $result['peminjaman_selesai'];

    // Peminjaman terbaru
    $stmt = $pdo->query("SELECT nama_alat, peminjam, tanggal_mulai, status FROM peminjaman ORDER BY id DESC LIMIT 5");
    $stats['peminjaman_terbaru'] = $stmt->fetchAll();

    // Cache the result
    $cache->set($cacheKey, $stats);

    return $stats;
}

// Function to get alat data
function getAlatData($page = 1, $limit = 10) {
    global $pdo;

    $offset = ($page - 1) * $limit;

    $stmt = $pdo->prepare("SELECT * FROM alat ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $alat = $stmt->fetchAll();

    // Get total count for pagination
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM alat");
    $total = $stmt->fetch()['total'];

    return [
        'data' => $alat,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
}

// Function to get anggota data
function getAnggotaData($page = 1, $limit = 10) {
    global $pdo;

    $offset = ($page - 1) * $limit;

    $stmt = $pdo->prepare("SELECT * FROM anggota ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $anggota = $stmt->fetchAll();

    // Get total count for pagination
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM anggota");
    $total = $stmt->fetch()['total'];

    return [
        'data' => $anggota,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
}

// Function to get peminjaman data
function getPeminjamanData($page = 1, $limit = 10) {
    global $pdo;

    $offset = ($page - 1) * $limit;

    $stmt = $pdo->prepare("SELECT * FROM peminjaman ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $peminjaman = $stmt->fetchAll();

    // Get total count for pagination
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM peminjaman");
    $total = $stmt->fetch()['total'];

    return [
        'data' => $peminjaman,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];
}

// Get data for initial page load
$dashboardStats = getDashboardStats();
$alatData = getAlatData();
$anggotaData = getAnggotaData();
$peminjamanData = getPeminjamanData();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alat Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="pinjam.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>SMK AL-BASTHOMI</h1>
            <p>Sistem Peminjaman Alat</p>
        </div>
        
        <nav class="sidebar-menu">
            <a href="#" class="menu-item active" data-tab="dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item" data-tab="dataAlat">
                <i class="fas fa-tools"></i>
                <span>Data Alat</span>
            </a>
            <a href="#" class="menu-item" data-tab="dataAnggota">
                <i class="fas fa-users"></i>
                <span>Data Anggota</span>
            </a>
            <a href="#" class="menu-item" data-tab="peminjaman">
                <i class="fas fa-handshake"></i>
                <span>Peminjaman</span>
            </a>
            <a href="#" class="menu-item" data-tab="laporan">
                <i class="fas fa-chart-bar"></i>
                <span>Laporan</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-info">
                    <h4><?php echo htmlspecialchars($adminNama); ?></h4>
                    <p><?php echo htmlspecialchars($adminUsername); ?></p>
                </div>
                <button id="btnLogout" class="logout-btn" title="Keluar">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title">Dashboard</h1>
            <div class="header-actions">
                <div class="notification-btn">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="user-menu">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <section id="dashboard" class="tab-content active">
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(67, 97, 238, 0.1); color: var(--primary);">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalAlat"><?php echo $dashboardStats['total_alat']; ?></h3>
                        <p>Total Alat</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(74, 222, 128, 0.1); color: var(--success);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="alatTersedia"><?php echo $dashboardStats['total_stock']; ?></h3>
                        <p>Alat Tersedia</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.1); color: var(--warning);">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="alatDipinjam"><?php echo $dashboardStats['total_alat'] - $dashboardStats['total_stock']; ?></h3>
                        <p>Alat Dipinjam</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(239, 68, 68, 0.1); color: var(--danger);">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="peminjamanAktif"><?php echo $dashboardStats['peminjaman_aktif']; ?></h3>
                        <p>Peminjaman Aktif</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(168, 85, 247, 0.1); color: #a855f7;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="peminjamanSelesai"><?php echo $dashboardStats['peminjaman_selesai']; ?></h3>
                        <p>Peminjaman Selesai</p>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Peminjaman Terbaru</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Alat</th>
                                <th>Peminjam</th>
                                <th>Tanggal Mulai</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="peminjamanTerbaruTable">
                            <?php foreach ($dashboardStats['peminjaman_terbaru'] as $peminjaman): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($peminjaman['nama_alat']); ?></td>
                                <td><?php echo htmlspecialchars($peminjaman['peminjam']); ?></td>
                                <td><?php echo htmlspecialchars($peminjaman['tanggal_mulai']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($peminjaman['status']); ?>">
                                        <?php echo htmlspecialchars($peminjaman['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Data Alat Content -->
        <section id="dataAlat" class="tab-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Data Alat</h2>
                    <button class="btn btn-primary" id="tambahAlatBtn">
                        <i class="fas fa-plus"></i>
                        Tambah Alat
                    </button>
                </div>

                <div class="alat-grid">
                    <?php foreach ($alatData['data'] as $alat): ?>
                    <div class="alat-card" data-id="<?php echo $alat['id']; ?>">
                        <div class="alat-image">
                            <?php if ($alat['foto'] && file_exists($alat['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($alat['foto']); ?>" alt="<?php echo htmlspecialchars($alat['nama']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-tools"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="alat-info">
                            <h3><?php echo htmlspecialchars($alat['nama']); ?></h3>
                            <p><?php echo htmlspecialchars($alat['deskripsi']); ?></p>
                            <div class="alat-meta">
                                <span class="kategori"><?php echo htmlspecialchars($alat['kategori']); ?></span>
                                <span class="stock">Stock: <?php echo $alat['stock']; ?></span>
                            </div>
                            <div class="alat-status">
                                <span class="status-badge status-<?php echo strtolower($alat['status']); ?>">
                                    <?php echo htmlspecialchars($alat['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="alat-actions">
                            <button class="btn btn-sm btn-primary pinjam-btn" data-id="<?php echo $alat['id']; ?>" data-nama="<?php echo htmlspecialchars($alat['nama']); ?>">
                                <i class="fas fa-handshake"></i>
                                Pinjam
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Data Anggota Content -->
        <section id="dataAnggota" class="tab-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Data Anggota</h2>
                <div class="header-actions">
                    <input type="text" id="searchAnggota" placeholder="Cari siswa..." style="flex: 1; margin-right: 1rem; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    <button class="btn btn-primary" id="tambahAnggotaBtn">
                        <i class="fas fa-plus"></i>
                        Tambah Anggota
                    </button>
                </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kelas/Jabatan</th>
                                <th>Kontak</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="anggotaTable">
                            <?php foreach ($anggotaData['data'] as $anggota): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($anggota['nama']); ?></td>
                                <td><?php echo htmlspecialchars($anggota['kelas']); ?></td>
                                <td><?php echo htmlspecialchars($anggota['kontak']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($anggota['status']); ?>">
                                        <?php echo htmlspecialchars($anggota['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" data-id="<?php echo $anggota['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $anggota['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Peminjaman Content -->
        <section id="peminjaman" class="tab-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Peminjaman Alat</h2>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Alat</th>
                                <th>Peminjam</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="peminjamanTable">
                            <?php foreach ($peminjamanData['data'] as $peminjaman): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($peminjaman['nama_alat']); ?></td>
                                <td><?php echo htmlspecialchars($peminjaman['peminjam']); ?></td>
                                <td><?php echo htmlspecialchars($peminjaman['tanggal_mulai']); ?></td>
                                <td><?php echo htmlspecialchars($peminjaman['tanggal_selesai']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($peminjaman['status']); ?>">
                                        <?php echo htmlspecialchars($peminjaman['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($peminjaman['status'] === 'Aktif'): ?>
                                    <button class="btn btn-sm btn-success return-btn" data-id="<?php echo $peminjaman['id']; ?>">
                                        <i class="fas fa-undo"></i>
                                        Kembalikan
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-warning edit-btn" data-id="<?php echo $peminjaman['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $peminjaman['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Laporan Content -->
        <section id="laporan" class="tab-content">
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Laporan Peminjaman</h2>
                </div>
                
                <div class="filter-container">
                    <div class="form-group">
                        <label class="form-label">Periode</label>
                        <select class="form-control" id="filterPeriode">
                            <option value="minggu">Minggu Ini</option>
                            <option value="bulan">Bulan Ini</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bulan</label>
                        <select class="form-control" id="filterBulan">
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tahun</label>
                        <input type="number" class="form-control" id="filterTahun" value="2025">
                    </div>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Alat</th>
                                <th>Peminjam</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="laporanTable">
                            <!-- Data akan diisi oleh JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal Tambah Alat -->
    <div class="modal" id="tambahAlatModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Alat Baru</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="alatForm">
                    <div class="form-group">
                        <label class="form-label">Nama Alat</label>
                        <input type="text" class="form-control" id="namaAlat" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsiAlat" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select class="form-control" id="kategoriAlat" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Fotografi">Fotografi</option>
                            <option value="Perkakas">Perkakas</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-control" id="stockAlat" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto Alat</label>
                        <input type="file" class="form-control" id="fotoAlat" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" id="batalAlatBtn" type="button">Batal</button>
                <button class="btn btn-primary" id="simpanAlatBtn" type="button">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Anggota -->
    <div class="modal" id="tambahAnggotaModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Anggota Baru</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="anggotaForm">
                    <div class="form-group">
                        <label class="form-label">Nama Anggota</label>
                        <input type="text" class="form-control" id="namaAnggota" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kelas/Jabatan</label>
                        <input type="text" class="form-control" id="kelasAnggota" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kontak</label>
                        <input type="text" class="form-control" id="kontakAnggota" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" id="batalAnggotaBtn" type="button">Batal</button>
                <button class="btn btn-primary" id="simpanAnggotaBtn" type="button">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Modal Peminjaman -->
    <div class="modal" id="peminjamanModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Catat Peminjaman Alat</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="peminjamanForm">
                    <div class="form-group">
                        <label class="form-label">Pilih Alat</label>
                        <select class="form-control" id="alatDipinjam" required>
                            <option value="">Pilih Alat</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <select class="form-control" id="kelasPeminjam" required>
                            <option value="">Pilih Kelas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Peminjam</label>
                        <input type="text" class="form-control" id="namaPeminjam" placeholder="Cari nama peminjam..." required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="tanggalMulai" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="tanggalSelesai" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keteranganPeminjaman" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" id="batalPeminjamanBtn" type="button">Batal</button>
                <button class="btn btn-primary" id="simpanPeminjamanBtn" type="button">Simpan</button>
            </div>
        </div>
    </div>
    <script>
        // Pass PHP data to JavaScript
        const initialData = {
            dashboard: <?php echo json_encode($dashboardStats); ?>,
            alat: <?php echo json_encode($alatData); ?>,
            anggota: <?php echo json_encode($anggotaData); ?>,
            peminjaman: <?php echo json_encode($peminjamanData); ?>
        };
    </script>
    <script src="pinjam.js"></script>
</body>
</html>