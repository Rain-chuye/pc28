<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';

header('Content-Type: application/json');

// Admin check
// if ($_SESSION['role'] !== 'admin') die;

$db = \App\Utils\DB::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['id'];
    $status = $_POST['status']; // 'approved' or 'rejected'

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
                    $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$req['amount'], $req['user_id']]);

                    // Trigger Agent Invitation Bonus (15 points for first deposit >= 21)
                    if ($req['amount'] >= 21) {
                        checkInvitationBonus($db, $req['user_id']);
                    }
                } else if ($req['type'] === 'withdraw') {
                    // Balance should have been deducted at request time or handle here
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
        // Check if bonus already given
        $checkStmt = $db->prepare("SELECT id FROM rebates WHERE sub_id = ? AND user_id = ? AND type = 'invitation'");
        $checkStmt->execute([$userId, $inviterId]);
        if (!$checkStmt->fetch()) {
            // Give 15 point reward to inviter
            $db->prepare("UPDATE users SET balance = balance + 15 WHERE id = ?")->execute([$inviterId]);
            $db->prepare("INSERT INTO rebates (user_id, sub_id, type, amount) VALUES (?, ?, 'invitation', 15)")->execute([$inviterId, $userId]);
        }
    }
}
