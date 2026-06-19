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

    $userId = $_SESSION['user_id'];
    $db = \App\Utils\DB::getInstance()->getConnection();

    $db->beginTransaction();
    try {
        $totalThisBatch = 0;
        foreach ($betsInput as $b) {
            $playType = $b['play_type'];
            // Convert Chinese names to English keys if applicable
            if(isset($playTypeMap[$playType])) $playType = $playTypeMap[$playType];

            $amount = (float)$b['amount'];
            if ($amount < 2) throw new Exception("最低 2 积分");
            $totalThisBatch += $amount;

            if (!\App\Model\Bet::place($userId, $issueNo, $playType, $amount, $oddsType, true)) {
                throw new Exception("下注失败: $playType");
            }
        }

        // Final sanity check on issue total
        $stmt = $db->prepare("SELECT SUM(bet_amount) FROM bets WHERE user_id = ? AND issue_no = ?");
        $stmt->execute([$userId, $issueNo]);
        if($stmt->fetchColumn() > 20000) throw new Exception("单期投注总额超过 20000 限制");

        $db->commit();
        echo json_encode(['success' => true, 'message' => '下单成功']);
    } catch (Exception $e) {
        if($db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
