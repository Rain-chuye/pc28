<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

$db = \App\Utils\DB::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['id'];
    $status = $_POST['status'];

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT * FROM finance_requests WHERE id = ? FOR UPDATE");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch();

        if ($req && $req['status'] === 'pending') {
            $stmt = $db->prepare("UPDATE finance_requests SET status = ? WHERE id = ?");
            $stmt->execute([$status, $requestId]);

            if ($status === 'approved') {
                if ($req['type'] === 'deposit') {
                    // Update user balance and total_deposit
                    $stmt = $db->prepare("UPDATE users SET balance = balance + ?, total_deposit = total_deposit + ? WHERE id = ?");
                    $stmt->execute([$req['amount'], $req['amount'], $req['user_id']]);

                    // New user bonus check (assuming "Deposit 21 Get 20" from previous context or generic bonus)
                    // If this is the first deposit, give 20 bonus
                    $stmt = $db->prepare("SELECT COUNT(*) FROM finance_requests WHERE user_id = ? AND status = 'approved' AND type = 'deposit'");
                    $stmt->execute([$req['user_id']]);
                    if ($stmt->fetchColumn() == 1) { // Current one is already counted as approved
                        $bonus = 20;
                        $db->prepare("UPDATE users SET balance = balance + ?, total_bonus = total_bonus + ? WHERE id = ?")
                           ->execute([$bonus, $bonus, $req['user_id']]);
                    }

                    if ($req['amount'] >= 21) {
                        checkInvitationBonus($db, $req['user_id']);
                    }
                }
            }
        }
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function checkInvitationBonus($db, $userId) {
    $stmt = $db->prepare("SELECT inviter_id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $inviterId = $stmt->fetchColumn();

    if ($inviterId) {
        $checkStmt = $db->prepare("SELECT id FROM rebates WHERE sub_id = ? AND user_id = ? AND type = 'invitation'");
        $checkStmt->execute([$userId, $inviterId]);
        if (!$checkStmt->fetch()) {
            $db->prepare("UPDATE users SET balance = balance + 15 WHERE id = ?")->execute([$inviterId]);
            $db->prepare("INSERT INTO rebates (user_id, sub_id, type, amount) VALUES (?, ?, 'invitation', 15)")->execute([$inviterId, $userId]);
        }
    }
}
