<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Lottery.php';

header('Content-Type: application/json');

$latest = \App\Model\Lottery::getLatest();
if (!$latest) {
    // Return dummy data if DB is empty
    $latest = [
        'issue_no' => '20240616-088',
        'numbers' => '1,2,3',
        'total_sum' => 6
    ];
}
echo json_encode(['success' => true, 'latest' => $latest]);
