<?php
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Bet.php';

header('Content-Type: application/json');

$bets = \App\Model\Bet::getLiveBets(20);
echo json_encode(['success' => true, 'data' => $bets]);
