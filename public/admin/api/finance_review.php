<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';
require_once __DIR__ . '/../../../src/Model/User.php';

header('Content-Type: application/json');
$db = \App\Utils\DB::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if (!$requestId || !$status) {
        echo json_encode(['success' => false, 'message' => 'Missing ID or Status']);
        die;
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT * FROM finance_requests WHERE id = ? FOR UPDATE");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch();

        if ($req && $req['status'] === 'pending') {
            $stmt = $db->prepare("UPDATE finance_requests SET status = ?, refusal_reason = ? WHERE id = ?");
            $stmt->execute([$status, $reason, $requestId]);

            if ($status === 'approved') {
                if ($req['type'] === 'deposit') {
                    $userId = $req['user_id'];
                    $amount = (float)$req['amount'];

                    \App\Model\User::addDeposit($userId, $amount, $db);

                    // Robust column check
                    $user = $db->query("SELECT * FROM users WHERE id = $userId")->fetch();
                    $hasDailyCol = array_key_exists('last_daily_bonus_at', $user);

                    $today = date('Y-m-d');
                    $bonus = 0;

                    if (!$user['first_recharge_done'] && $amount >= 20) {
                        $bonus = 21;
                        $db->prepare("UPDATE users SET first_recharge_done = 1 WHERE id = ?")->execute([$userId]);
                    } elseif ($hasDailyCol && $user['last_daily_bonus_at'] !== $today) {
                        if ($amount >= 100) $bonus = 60;
                        elseif ($amount >= 50) $bonus = 20;
                        elseif ($amount >= 40) $bonus = 12;
                        elseif ($amount >= 30) $bonus = 10;
                        elseif ($amount >= 20) $bonus = 4;
                        elseif ($amount >= 10) $bonus = 1;

                        if($bonus > 0) {
                            $db->prepare("UPDATE users SET last_daily_bonus_at = ? WHERE id = ?")->execute([$today, $userId]);
                        }
                    }

                    if ($bonus > 0) {
                        \App\Model\User::addBonus($userId, $bonus, $db);
                    }
                }
            } else if ($status === 'rejected') {
                if ($req['type'] === 'withdraw') {
                    $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$req['amount'], $req['user_id']]);
                }
            }
        }
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if($db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Review Failed: ' . $e->getMessage()]);
    }
}
