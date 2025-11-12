<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';
require_once 'cache.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';

switch ($endpoint) {
    case 'alat':
        handleAlat($method, $request);
        break;
    case 'anggota':
        handleAnggota($method, $request);
        break;
    case 'peminjaman':
        handlePeminjaman($method, $request);
        break;
    case 'dashboard':
        handleDashboard($method);
        break;
    case 'notifications':
        handleNotifications($method, $request);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

function handleAlat($method, $request) {
    global $pdo;

    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // Get single alat
                $stmt = $pdo->prepare("SELECT * FROM alat WHERE id = ?");
                $stmt->execute([$request[1]]);
                $alat = $stmt->fetch();
                echo json_encode($alat ?: ['error' => 'Alat not found']);
            } else {
                // Get all alat with pagination
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $offset = ($page - 1) * $limit;

                $stmt = $pdo->prepare("SELECT * FROM alat ORDER BY id DESC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                $alat = $stmt->fetchAll();

                // Get total count for pagination
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM alat");
                $total = $stmt->fetch()['total'];

                echo json_encode([
                    'data' => $alat,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;

        case 'POST':
            $nama = $_POST['nama'] ?? '';
            $deskripsi = $_POST['deskripsi'] ?? '';
            $kategori = $_POST['kategori'] ?? '';
            $stock = (int)($_POST['stock'] ?? 0);
            $status = $_POST['status'] ?? 'Tersedia';

            $fotoPath = '';
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = uniqid() . '_' . basename($_FILES['foto']['name']);
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                    $fotoPath = $targetFile;
                }
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO alat (nama, deskripsi, kategori, stock, status, foto) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $nama,
                    $deskripsi,
                    $kategori,
                    $stock,
                    $status,
                    $fotoPath
                ]);
                $newId = $pdo->lastInsertId();
                if ($newId) {
                    // Clear dashboard cache when new alat is added
                    $cache = new SimpleCache();
                    $cache->delete('dashboard_stats');
                    echo json_encode(['id' => $newId, 'message' => 'Alat created successfully']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to create alat: No ID returned']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!isset($request[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Alat ID required']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE alat SET nama=?, deskripsi=?, kategori=?, stock=?, status=?, foto=? WHERE id=?");
            $stmt->execute([
                $data['nama'],
                $data['deskripsi'],
                $data['kategori'],
                $data['stock'],
                $data['status'] ?? 'Tersedia',
                $data['foto'] ?? '',
                $request[1]
            ]);
            echo json_encode(['message' => 'Alat updated successfully']);
            break;

        case 'DELETE':
            if (!isset($request[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Alat ID required']);
                return;
            }

            // Check if alat is currently borrowed
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM peminjaman WHERE alat_id = ? AND status = 'Aktif'");
            $stmt->execute([$request[1]]);
            $activeBorrow = $stmt->fetch();

            if ($activeBorrow['count'] > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Tidak dapat menghapus alat yang sedang dipinjam']);
                return;
            }

            // Delete related peminjaman records first
            $stmt = $pdo->prepare("DELETE FROM peminjaman WHERE alat_id = ?");
            $stmt->execute([$request[1]]);

            // Then delete the alat
            $stmt = $pdo->prepare("DELETE FROM alat WHERE id = ?");
            $stmt->execute([$request[1]]);

            echo json_encode(['message' => 'Alat dan data peminjaman terkait berhasil dihapus']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleAnggota($method, $request) {
    global $pdo;

    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // Get single anggota
                $stmt = $pdo->prepare("SELECT * FROM anggota WHERE id = ?");
                $stmt->execute([$request[1]]);
                $anggota = $stmt->fetch();
                echo json_encode($anggota ?: ['error' => 'Anggota not found']);
            } else {
                // Get all anggota with pagination
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $offset = ($page - 1) * $limit;

                $stmt = $pdo->prepare("SELECT * FROM anggota ORDER BY id DESC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                $anggota = $stmt->fetchAll();

                // Get total count for pagination
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM anggota");
                $total = $stmt->fetch()['total'];

                echo json_encode([
                    'data' => $anggota,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO anggota (nama, kelas, kontak, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['nama'],
                $data['kelas'],
                $data['kontak'],
                $data['status'] ?? 'Aktif'
            ]);
            echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Anggota created successfully']);
            break;

        case 'PUT':
            if (!isset($request[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Anggota ID required']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE anggota SET nama=?, kelas=?, kontak=?, status=? WHERE id=?");
            $stmt->execute([
                $data['nama'],
                $data['kelas'],
                $data['kontak'],
                $data['status'] ?? 'Aktif',
                $request[1]
            ]);
            echo json_encode(['message' => 'Anggota updated successfully']);
            break;

        case 'DELETE':
            if (!isset($request[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Anggota ID required']);
                return;
            }

            // Check if anggota has active peminjaman
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM peminjaman WHERE peminjam = (SELECT nama FROM anggota WHERE id = ?) AND status = 'Aktif'");
            $stmt->execute([$request[1]]);
            $activeBorrow = $stmt->fetch();

            if ($activeBorrow['count'] > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Tidak dapat menghapus anggota yang memiliki peminjaman aktif']);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM anggota WHERE id=?");
            $stmt->execute([$request[1]]);
            echo json_encode(['message' => 'Anggota deleted successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handlePeminjaman($method, $request) {
    global $pdo;

    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // Get single peminjaman
                $stmt = $pdo->prepare("SELECT * FROM peminjaman WHERE id = ?");
                $stmt->execute([$request[1]]);
                $peminjaman = $stmt->fetch();
                echo json_encode($peminjaman ?: ['error' => 'Peminjaman not found']);
            } else {
                // Get all peminjaman with pagination
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $offset = ($page - 1) * $limit;

                $stmt = $pdo->prepare("SELECT * FROM peminjaman ORDER BY id DESC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                $peminjaman = $stmt->fetchAll();

                // Get total count for pagination
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM peminjaman");
                $total = $stmt->fetch()['total'];

                echo json_encode([
                    'data' => $peminjaman,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            // Check if peminjam exists and is active
            $stmt = $pdo->prepare("SELECT id FROM anggota WHERE nama = ? AND status = 'Aktif'");
            $stmt->execute([$data['peminjam']]);
            $anggota = $stmt->fetch();

            if (!$anggota) {
                http_response_code(400);
                echo json_encode(['error' => 'Nama peminjam tidak terdaftar atau tidak aktif']);
                return;
            }

            // Check if alat is available
            $stmt = $pdo->prepare("SELECT stock FROM alat WHERE id = ?");
            $stmt->execute([$data['alat_id']]);
            $alat = $stmt->fetch();

            if (!$alat || $alat['stock'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Alat tidak tersedia']);
                return;
            }

            // Insert peminjaman
            $stmt = $pdo->prepare("INSERT INTO peminjaman (alat_id, nama_alat, peminjam, tanggal_mulai, tanggal_selesai, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['alat_id'],
                $data['nama_alat'],
                $data['peminjam'],
                $data['tanggal_mulai'],
                $data['tanggal_selesai'],
                $data['status'] ?? 'Aktif',
                $data['keterangan'] ?? ''
            ]);

            // Decrease stock
            $stmt = $pdo->prepare("UPDATE alat SET stock = stock - 1 WHERE id = ?");
            $stmt->execute([$data['alat_id']]);

            // Clear dashboard cache when new peminjaman is added
            $cache = new SimpleCache();
            $cache->delete('dashboard_stats');

            echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Peminjaman created successfully']);
            break;

        case 'PUT':
            if (!isset($request[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Peminjaman ID required']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['action']) && $data['action'] === 'return') {
                // Return alat
                $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'Selesai' WHERE id = ?");
                $stmt->execute([$request[1]]);

                // Get alat_id to increase stock
                $stmt = $pdo->prepare("SELECT alat_id FROM peminjaman WHERE id = ?");
                $stmt->execute([$request[1]]);
                $peminjaman = $stmt->fetch();

                if ($peminjaman) {
                    $stmt = $pdo->prepare("UPDATE alat SET stock = stock + 1 WHERE id = ?");
                    $stmt->execute([$peminjaman['alat_id']]);
                }

                // Clear dashboard cache when alat is returned
                $cache = new SimpleCache();
                $cache->delete('dashboard_stats');

                echo json_encode(['message' => 'Alat returned successfully']);
            } else {
                // Update peminjaman
                $stmt = $pdo->prepare("UPDATE peminjaman SET alat_id=?, nama_alat=?, peminjam=?, tanggal_mulai=?, tanggal_selesai=?, status=?, keterangan=? WHERE id=?");
                $stmt->execute([
                    $data['alat_id'],
                    $data['nama_alat'],
                    $data['peminjam'],
                    $data['tanggal_mulai'],
                    $data['tanggal_selesai'],
                    $data['status'] ?? 'Aktif',
                    $data['keterangan'] ?? '',
                    $request[1]
                ]);
                echo json_encode(['message' => 'Peminjaman updated successfully']);
            }
            break;

        case 'DELETE':
            if (!isset($request[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Peminjaman ID required']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM peminjaman WHERE id=?");
            $stmt->execute([$request[1]]);
            echo json_encode(['message' => 'Peminjaman deleted successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleDashboard($method) {
    global $pdo;

    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $cache = new SimpleCache();
    $cacheKey = 'dashboard_stats';

    // Try to get from cache first
    $stats = $cache->get($cacheKey);
    if ($stats !== false) {
        echo json_encode($stats);
        return;
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

    echo json_encode($stats);
}

function handleNotifications($method, $request) {
    global $pdo;

    switch ($method) {
        case 'GET':
            if (isset($request[1])) {
                // Get single notification
                $stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = ?");
                $stmt->execute([$request[1]]);
                $notification = $stmt->fetch();
                echo json_encode($notification ?: ['error' => 'Notification not found']);
            } else {
                // Get all notifications
                $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
                $notifications = $stmt->fetchAll();
                echo json_encode($notifications);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("INSERT INTO notifications (type, title, message, time, is_read) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['type'] ?? 'info',
                $data['title'],
                $data['message'],
                $data['time'] ?? date('H:i'),
                $data['is_read'] ?? false
            ]);
            echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Notification created successfully']);
            break;

        case 'PUT':
            if (!isset($request[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Notification ID required']);
                return;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE notifications SET type=?, title=?, message=?, time=?, is_read=? WHERE id=?");
            $stmt->execute([
                $data['type'] ?? 'info',
                $data['title'],
                $data['message'],
                $data['time'] ?? date('H:i'),
                $data['is_read'] ?? false,
                $request[1]
            ]);
            echo json_encode(['message' => 'Notification updated successfully']);
            break;

        case 'DELETE':
            if (!isset($request[1])) {
                http_response_code(400);
                echo json_encode(['error' => 'Notification ID required']);
                return;
            }
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id=?");
            $stmt->execute([$request[1]]);
            echo json_encode(['message' => 'Notification deleted successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}
?>
