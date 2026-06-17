<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/User.php';

header('Content-Type: application/json');

$users = \App\Model\User::getAll();
echo json_encode(['success' => true, 'data' => $users]);
