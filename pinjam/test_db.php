<?php
require_once 'config.php';

try {
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables in database: " . implode(', ', $tables) . "\n";

    // Check alat table content
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM alat");
    $count = $stmt->fetch()['count'];
    echo "Total alat records: $count\n";

    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, nama, stock FROM alat ORDER BY id DESC LIMIT 5");
        $alat = $stmt->fetchAll();
        echo "Recent alat:\n";
        foreach ($alat as $item) {
            echo "- ID: {$item['id']}, Nama: {$item['nama']}, Stock: {$item['stock']}\n";
        }
    }

    // Check anggota table content
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM anggota");
    $count = $stmt->fetch()['count'];
    echo "Total anggota records: $count\n";

    // Check peminjaman table content
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM peminjaman");
    $count = $stmt->fetch()['count'];
    echo "Total peminjaman records: $count\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
