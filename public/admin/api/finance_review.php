<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';
require_once __DIR__ . '/../../../src/Model/User.php';

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
                    \App\Model\User::addDeposit($req['user_id'], $req['amount'], $db);

                    // First deposit bonus
                    $stmt = $db->prepare("SELECT COUNT(*) FROM finance_requests WHERE user_id = ? AND status = 'approved' AND type = 'deposit'");
                    $stmt->execute([$req['user_id']]);
                    if ($stmt->fetchColumn() == 1) {
                        \App\Model\User::addBonus($req['user_id'], 20, $db);
                    }

                    if ($req['amount'] >= 21) {
                        checkInvitationBonus($db, $req['user_id']);
                    }
                } else if ($req['type'] === 'withdraw') {
                    // Balance already deducted on request (assuming typical flow)
                    // If not deducted on request, deduct here.
                    // Currently, let's assume it was deducted on request.
                    // Let's check withdraw.php logic.
                }
            } else if ($status === 'rejected' && $req['type'] === 'withdraw') {
                // Return funds to user
                \App\Model\User::updateBalance($req['user_id'], $req['amount'], 'withdraw', '提现驳回退款', $db);
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
            \App\Model\User::updateBalance($inviterId, 15, 'rebate', '好友首充奖励', $db);
            $db->prepare("INSERT INTO rebates (user_id, sub_id, type, amount) VALUES (?, ?, 'invitation', 15)")->execute([$inviterId, $userId]);
        }
    }
}
