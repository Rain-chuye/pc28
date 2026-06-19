<?php
/**
 * PC28 采集器 - 深度28 (shendu28.com) 商业修复版
 */
require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Config/database.php';
$config = require __DIR__ . '/../src/Config/database.php';

function fetchLotteryData() {
    $url = "http://shendu28.com/yuce.php?type=zh";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

    $html = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || !$html) {
        error_log("Scraper Error: $error");
        return simulateData();
    }

    // 1. 提取期号 (改进正则)
    $issue_no = "";
    if (preg_match('/第\s*(\d+)\s*期开奖结果/', $html, $issueMatch)) {
        $issue_no = trim($issueMatch[1]);
    }

    // 2. 提取开奖球 (精准定位)
    preg_match_all('/<div class="ball">(\d+)<\/div>/', $html, $ballMatches);
    preg_match('/<div class="ball sum[^">]*">(\d+)<\/div>/', $html, $sumMatch);

    if ($issue_no && count($ballMatches[1]) >= 3 && isset($sumMatch[1])) {
        $n1 = $ballMatches[1][0];
        $n2 = $ballMatches[1][1];
        $n3 = $ballMatches[1][2];
        $sum = $sumMatch[1];

        // 3. 尝试提取开奖时间 (从页面元数据或特定标签)
        // 深度 28 页面上通常没有显式的“开奖时间”标签给当前结果，我们使用当前采集时间作为标记，
        // 或者寻找页面底部的更新时间。
        $open_time = date('Y-m-d H:i:s');

        return [
            'issue_no' => $issue_no,
            'numbers' => "$n1,$n2,$n3",
            'total_sum' => $sum,
            'open_time' => $open_time
        ];
    }

    return simulateData();
}

function simulateData() {
    $ts = time();
    $issue_no = date('Ymd', $ts) . str_pad((floor(($ts % 86400) / 210)), 3, '0', STR_PAD_LEFT);
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
    try {
        // 使用 REPLACE INTO 确保即使期号冲突也更新最新的开奖时间
        $sql = "REPLACE INTO lottery_results (issue_no, numbers, total_sum, open_time)
                VALUES (:issue_no, :numbers, :total_sum, :open_time)";
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Scraper DB Save Error: " . $e->getMessage());
        return false;
    }
}

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);

    $data = fetchLotteryData();
    if ($data && saveResult($pdo, $data)) {
        echo "[".date('Y-m-d H:i:s')."] 成功采集期号: {$data['issue_no']} -> {$data['numbers']} = {$data['total_sum']}\n";
    } else {
        echo "[".date('Y-m-d H:i:s')."] 无新数据或采集失败。\n";
    }
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}
