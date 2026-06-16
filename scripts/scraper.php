<?php
require_once __DIR__ . '/../src/Config/database.php';
$config = require __DIR__ . '/../src/Config/database.php';

function fetchLotteryData() {
    $issue_no = date('Ymd') . str_pad(rand(1, 1000), 4, '0', STR_PAD_LEFT);
    $n1 = rand(0, 9);
    $n2 = rand(0, 9);
    $n3 = rand(0, 9);
    $sum = $n1 + $n2 + $n3;
    $numbers = "$n1,$n2,$n3";
    $open_time = date('Y-m-d H:i:s');

    return [
        'issue_no' => $issue_no,
        'numbers' => $numbers,
        'total_sum' => $sum,
        'open_time' => $open_time
    ];
}

function saveResult($db, $data) {
    // MySQL specific INSERT IGNORE
    $sql = "INSERT IGNORE INTO lottery_results (issue_no, numbers, total_sum, open_time)
            VALUES (:issue_no, :numbers, :total_sum, :open_time)";
    $stmt = $db->prepare($sql);
    $stmt->execute($data);
    return $stmt->rowCount() > 0;
}

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset=utf8";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $data = fetchLotteryData();
    if (saveResult($pdo, $data)) {
        echo "New result saved: Issue {$data['issue_no']} - Result: {$data['total_sum']}\n";
    } else {
        echo "Issue {$data['issue_no']} already exists or skip.\n";
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
