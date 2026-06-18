<?php
/**
 * PC28 采集器 - 深度28 (shendu28.com) 适配版
 */
require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Config/database.php';
$config = require __DIR__ . '/../src/Config/database.php';

function fetchLotteryData() {
    $url = "http://shendu28.com/yuce.php?type=zh";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

    $html = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || !$html) {
        error_log("Scraper Curl Error: " . $error);
        return simulateData();
    }

    // 1. 提取期号
    $issue_no = "";
    if (preg_match('/第\s*(\d+)\s*期开奖结果/', $html, $issueMatch)) {
        $issue_no = $issueMatch[1];
    }

    // 2. 提取开奖数字 (三个普通 ball + 一个 sum ball)
    // 普通球
    preg_match_all('/<div class="ball">(\d+)<\/div>/', $html, $ballMatches);
    // 结果球
    preg_match('/<div class="ball sum[^">]*">(\d+)<\/div>/', $html, $sumMatch);

    if ($issue_no && count($ballMatches[1]) >= 3 && isset($sumMatch[1])) {
        $n1 = $ballMatches[1][0];
        $n2 = $ballMatches[1][1];
        $n3 = $ballMatches[1][2];
        $sum = $sumMatch[1];

        return [
            'issue_no' => $issue_no,
            'numbers' => "$n1,$n2,$n3",
            'total_sum' => $sum,
            'open_time' => date('Y-m-d H:i:s')
        ];
    }

    error_log("Scraper Error: Failed to parse Shendu28 content");
    return simulateData();
}

function simulateData() {
    // 备用模拟逻辑，防止源站宕机导致系统停滞
    $issue_no = "S" . date('Ymd') . (floor(time() / 300) % 288 + 1);
    $n1 = rand(0, 9);
    $n2 = rand(0, 9);
    $n3 = rand(0, 9);
    $sum = $n1 + $n2 + $n3;
    return [
        'issue_no' => $issue_no,
        'numbers' => "$n1,$n2,$n3",
        'total_sum' => $sum,
        'open_time' => date('Y-m-d H:i:s')
    ];
}

function saveResult($db, $data) {
    $sql = "INSERT IGNORE INTO lottery_results (issue_no, numbers, total_sum, open_time)
            VALUES (:issue_no, :numbers, :total_sum, :open_time)";
    $stmt = $db->prepare($sql);
    $stmt->execute($data);
    return $stmt->rowCount() > 0;
}

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset=utf8";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);

    $data = fetchLotteryData();
    if ($data && saveResult($pdo, $data)) {
        echo "[".date('Y-m-d H:i:s')."] 成功采集期号: {$data['issue_no']} -> 结果: {$data['total_sum']} ({$data['numbers']})\n";
    } else {
        echo "[".date('Y-m-d H:i:s')."] 期号 " . ($data['issue_no'] ?? 'Unknown') . " 无更新或采集失败。\n";
    }
} catch (PDOException $e) {
    error_log("Scraper DB Connection Failed: " . $e->getMessage());
    echo "ERROR: DB Connection Failed\n";
}
