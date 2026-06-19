<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';
require_once __DIR__ . '/../../../src/Model/User.php';

header('Content-Type: application/json');
$db = \App\Utils\DB::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['id'];
    $status = $_POST['status'];
    $reason = $_POST['reason'] ?? '';

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
                    $amount = $req['amount'];

                    \App\Model\User::addDeposit($userId, $amount, $db);

                    // Tiered Recharge Bonus Logic
                    $stmt = $db->prepare("SELECT first_recharge_done FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $isFirst = !$stmt->fetchColumn();

                    $bonus = 0;
                    if ($isFirst && $amount >= 20) {
                        $bonus = 21;
                        $db->prepare("UPDATE users SET first_recharge_done = 1 WHERE id = ?")->execute([$userId]);
                    } else {
                        if ($amount >= 100) $bonus = 60;
                        else if ($amount >= 50) $bonus = 20;
                        else if ($amount >= 40) $bonus = 12;
                        else if ($amount >= 30) $bonus = 10;
                        else if ($amount >= 20) $bonus = 4;
                        else if ($amount >= 10) $bonus = 1;
                    }

                    if ($bonus > 0) {
                        \App\Model\User::addBonus($userId, $bonus, $db);
                    }
                }
            } else if ($status === 'rejected') {
                // If it was a withdrawal, refund the balance
                if ($req['type'] === 'withdraw') {
                    $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
                       ->execute([$req['amount'], $req['user_id']]);
                }
            }
        }
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if(isset($db) && $db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
