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
    $data = json_decode(file_get_contents('php://input'), true);
    $playType = isset($data['play_type']) ? $data['play_type'] : '';
    $amount = isset($data['amount']) ? (float)$data['amount'] : 0;
    $issueNo = isset($data['issue_no']) ? $data['issue_no'] : '';
    $oddsType = isset($data['odds_type']) ? $data['odds_type'] : 'low';

    if ($amount < 2) {
        echo json_encode(array('success' => false, 'message' => '最低起投 2 元'));
        die();
    }

    if (empty($issueNo)) {
        echo json_encode(array('success' => false, 'message' => '期号缺失'));
        die();
    }

    $db = \App\Utils\DB::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];

    // --- Validation Logic ---

    // 1. Fetch current bets for this issue
    $stmt = $db->prepare("SELECT play_type FROM bets WHERE user_id = ? AND issue_no = ? AND status = 0");
    $stmt->execute([$userId, $issueNo]);
    $currentBets = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Prohibit Single and Double simultaneously
    if ($playType === 'single' && in_array('double', $currentBets)) {
        echo json_encode(array('success' => false, 'message' => '禁止同时下注单和双'));
        die();
    }
    if ($playType === 'double' && in_array('single', $currentBets)) {
        echo json_encode(array('success' => false, 'message' => '禁止同时下注单和双'));
        die();
    }

    // 3. Prohibit Big and Small simultaneously
    if ($playType === 'big' && in_array('small', $currentBets)) {
        echo json_encode(array('success' => false, 'message' => '禁止同时下注大和小'));
        die();
    }
    if ($playType === 'small' && in_array('big', $currentBets)) {
        echo json_encode(array('success' => false, 'message' => '禁止同时下注大和小'));
        die();
    }

    // 4. Prohibit 4-gate coverage (BigSingle, SmallSingle, BigDouble, SmallDouble)
    $fourGates = ['big_single', 'small_single', 'big_double', 'small_double'];
    if (in_array($playType, $fourGates)) {
        $existingGates = array_intersect($currentBets, $fourGates);
        if (count($existingGates) >= 3 && !in_array($playType, $existingGates)) {
            echo json_encode(array('success' => false, 'message' => '禁止对“大单、小单、大双、小双”进行四门全包'));
            die();
        }
    }

    // 5. Limit specific number (0-27) bets to 4 per issue
    if (is_numeric($playType)) {
        $numericBets = array_filter($currentBets, 'is_numeric');
        if (count(array_unique($numericBets)) >= 4 && !in_array($playType, $numericBets)) {
            echo json_encode(array('success' => false, 'message' => '特码数字每期最多只能选4个'));
            die();
        }
    }

    // 6. Anti-Double Betting Logic (Keep existing 1.5x constraint for safety)
    $stmt = $db->prepare("SELECT bet_amount FROM bets WHERE user_id = ? AND play_type = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId, $playType]);
    $lastBetAmount = $stmt->fetchColumn();

    if ($lastBetAmount && $amount > ($lastBetAmount * 1.5)) {
        echo json_encode(array('success' => false, 'message' => '为了风控安全，本次投注金额不能超过上次同玩法金额的1.5倍（禁止倍投）'));
        die();
    }

    try {
        if (\App\Model\Bet::place($userId, $issueNo, $playType, $amount, $oddsType)) {
            echo json_encode(array('success' => true, 'message' => '下注成功'));
        }
    } catch (\Exception $e) {
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
}
