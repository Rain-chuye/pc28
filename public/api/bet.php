<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Bet.php';
require_once __DIR__ . '/../../src/Model/Lottery.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '请先登录']);
    die;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $bets = isset($input['bets']) ? $input['bets'] : [$input];
    $issueNo = $input['issue_no'] ?? '';
    $oddsType = $input['odds_type'] ?? 'low';

    if (!$issueNo) {
        echo json_encode(['success' => false, 'message' => '期号异常']);
        die;
    }

    // 封盘逻辑校验 (15秒封盘)
    $latest = \App\Model\Lottery::getLatest();
    if ($latest) {
        $now = time();
        $nextDrawTs = strtotime($latest['next_draw_at']);
        $countdown = $nextDrawTs - $now;
        if ($countdown <= 15) {
            echo json_encode(['success' => false, 'message' => '当前期号已封盘，停止下单']);
            die;
        }
    }

    $db = \App\Utils\DB::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];

    $db->beginTransaction();
    try {
        foreach ($bets as $b) {
            $playType = $b['play_type'];
            $amount = (float)$b['amount'];

            if ($amount < 2) throw new Exception("单注最低 2 积分 ($playType)");

            // 修复：特殊玩法逻辑名对应 (triple, straight, pair, banker, player, tie)
            // 确保 odds_config 表中有这些 Key
            if (!\App\Model\Bet::place($userId, $issueNo, $playType, $amount, $oddsType, true)) {
                throw new Exception("下注失败: $playType");
            }
        }
        $db->commit();
        echo json_encode(['success' => true, 'message' => '下单成功']);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
