<?php
session_start();
require_once __DIR__ . '/../../src/Utils/DB.php';
require_once __DIR__ . '/../../src/Model/Bet.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'message' => '请先登录'));
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Support both single bet and array of bets
    $bets = isset($input['bets']) ? $input['bets'] : [$input];
    $issueNo = isset($input['issue_no']) ? $input['issue_no'] : '';
    $oddsType = isset($input['odds_type']) ? $input['odds_type'] : 'low';

    if (empty($issueNo)) {
        echo json_encode(array('success' => false, 'message' => '期号缺失'));
        die();
    }

    $db = \App\Utils\DB::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];

    // 1. Fetch existing bets for this issue to check conflicts
    $stmt = $db->prepare("SELECT play_type FROM bets WHERE user_id = ? AND issue_no = ? AND status = 0");
    $stmt->execute([$userId, $issueNo]);
    $allTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Validate each bet in the batch
    foreach ($bets as $b) {
        $playType = $b['play_type'];
        $amount = (float)$b['amount'];

        if ($amount < 2) {
            echo json_encode(array('success' => false, 'message' => "下注金额 [$playType] 低于最低限制 2 元"));
            die();
        }

        // Prohibit Single and Double simultaneously
        if (($playType === 'single' && in_array('double', $allTypes)) ||
            ($playType === 'double' && in_array('single', $allTypes))) {
            echo json_encode(array('success' => false, 'message' => '禁止同时下注单和双'));
            die();
        }

        // Prohibit Big and Small simultaneously
        if (($playType === 'big' && in_array('small', $allTypes)) ||
            ($playType === 'small' && in_array('big', $allTypes))) {
            echo json_encode(array('success' => false, 'message' => '禁止同时下注大和小'));
            die();
        }

        // Prohibit 4-gate coverage
        $fourGates = ['big_single', 'small_single', 'big_double', 'small_double'];
        if (in_array($playType, $fourGates)) {
            $currentGates = array_intersect($allTypes, $fourGates);
            if (count($currentGates) >= 3 && !in_array($playType, $currentGates)) {
                echo json_encode(array('success' => false, 'message' => '禁止对“大单、小单、大双、小双”进行四门全包'));
                die();
            }
        }

        // Limit specific number (0-27) bets to 4 per issue
        if (is_numeric($playType)) {
            $numericBets = array_filter($allTypes, 'is_numeric');
            if (count(array_unique($numericBets)) >= 4 && !in_array($playType, $numericBets)) {
                echo json_encode(array('success' => false, 'message' => '特码数字每期最多只能选4个'));
                die();
            }
        }

        $allTypes[] = $playType;
    }

    // Process all bets in a transaction
    $db->beginTransaction();
    try {
        foreach ($bets as $b) {
            if (!\App\Model\Bet::place($userId, $issueNo, $b['play_type'], $b['amount'], $oddsType, true)) {
                throw new Exception("下注失败");
            }
        }
        $db->commit();
        echo json_encode(array('success' => true, 'message' => '下注成功'));
    } catch (\Exception $e) {
        $db->rollBack();
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
}
