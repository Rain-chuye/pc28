<?php
require_once __DIR__ . '/../../src/Model/Lottery.php';

header('Content-Type: application/json');

$latest = \App\Model\Lottery::getLatest();
$history = \App\Model\Lottery::getHistory(10);

echo json_encode([
    'latest' => $latest,
    'history' => $history
]);
