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

$playTypeMap = [
    '大' => 'big', '小' => 'small', '单' => 'single', '双' => 'double',
    '大单' => 'big_single', '大双' => 'big_double', '小单' => 'small_single', '小双' => 'small_double',
    '极大' => 'extreme_big', '极小' => 'extreme_small', '对子' => 'pair', '顺子' => 'straight', '豹子' => 'triple'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $betsInput = isset($input['bets']) ? $input['bets'] : [$input];
    $issueNo = $input['issue_no'] ?? '';
    $oddsType = $input['odds_type'] ?? 'low';

    if (!$issueNo) {
        echo json_encode(['success' => false, 'message' => '期号异常']);
        die;
    }

    $userId = $_SESSION['user_id'];
    $db = \App\Utils\DB::getInstance()->getConnection();

    // 1. Fetch existing bets for this issue to enforce rules
    $stmt = $db->prepare("SELECT play_type, odds_type FROM bets WHERE user_id = ? AND issue_no = ?");
    $stmt->execute([$userId, $issueNo]);
    $existingBets = $stmt->fetchAll();

    // Cross-room check
    foreach ($existingBets as $eb) {
        if ($eb['odds_type'] !== $oddsType) {
            echo json_encode(['success' => false, 'message' => "本期已在 " . ($eb['odds_type'] == 'high' ? '高倍房' : '低倍房') . " 下注，不可跨房投注"]);
            die;
        }
    }

    // 2. Aggregate all play types (existing + new)
    $allPlayTypes = [];
    foreach ($existingBets as $eb) $allPlayTypes[] = $eb['play_type'];

    $newInternalBets = [];
    foreach ($betsInput as $b) {
        $pt = $b['play_type'];
        if (isset($playTypeMap[$pt])) $pt = $playTypeMap[$pt];
        $allPlayTypes[] = $pt;
        $newInternalBets[] = ['type' => $pt, 'amount' => (float)$b['amount']];
    }

    // 3. Rule Enforcement Logic
    $uniqueTypes = array_unique($allPlayTypes);

    // Rule: Big/Small, Single/Double exclusivity
    if (in_array('big', $uniqueTypes) && in_array('small', $uniqueTypes)) {
        echo json_encode(['success' => false, 'message' => '不可同时下注大和小']); die;
    }
    if (in_array('single', $uniqueTypes) && in_array('double', $uniqueTypes)) {
        echo json_encode(['success' => false, 'message' => '不可同时下注单和双']); die;
    }

    // Rule: Combo limit (max 3 of the 4 combos)
    $combos = ['big_single', 'big_double', 'small_single', 'small_double'];
    $activeCombos = array_intersect($combos, $uniqueTypes);
    if (count($activeCombos) >= 4) {
        echo json_encode(['success' => false, 'message' => '不可同时下注四门组合']); die;
    }

    // Rule: Specific number limit (max 4)
    $numberBets = 0;
    foreach ($uniqueTypes as $ut) {
        if (is_numeric($ut)) $numberBets++;
    }
    if ($numberBets > 4) {
        echo json_encode(['success' => false, 'message' => '每期最多下注 4 个特码']); die;
    }

    // 4. Execution
    // 封盘逻辑校验
    $latest = \App\Model\Lottery::getLatest();
    if ($latest) {
        $now = time();
        $nextDrawTs = strtotime($latest['next_draw_at']);
        if (($nextDrawTs - $now) <= 15) {
            echo json_encode(['success' => false, 'message' => '已封盘，停止下单']);
            die;
        }
    }

    $db->beginTransaction();
    try {
        foreach ($newInternalBets as $nb) {
            if ($nb['amount'] < 2) throw new Exception("最低 2 积分");
            if (!\App\Model\Bet::place($userId, $issueNo, $nb['type'], $nb['amount'], $oddsType, true)) {
                throw new Exception("下注失败: " . $nb['type']);
            }
        }

        // Final sanity check on issue total
        $stmt = $db->prepare("SELECT SUM(bet_amount) FROM bets WHERE user_id = ? AND issue_no = ?");
        $stmt->execute([$userId, $issueNo]);
        if ($stmt->fetchColumn() > 20000) throw new Exception("单期投注总额超过 20000 限制");

        $db->commit();
        echo json_encode(['success' => true, 'message' => '下单成功']);
    } catch (Exception $e) {
        if($db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
