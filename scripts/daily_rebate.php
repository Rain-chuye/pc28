<?php
/**
 * 东爷国际 凌晨自动返利脚本 (2:00 AM)
 * 逻辑：下级流水满 100 返上级 1 块，满 200 返 2 块
 */
require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Model/User.php';

$db = \App\Utils\DB::getInstance()->getConnection();

try {
    echo "[" . date('Y-m-d H:i:s') . "] Starting Daily Rebate Task...\n";

    // 1. Get all users who have an inviter and daily turnover > 0
    $stmt = $db->query("SELECT id, username, inviter_id, daily_turnover FROM users WHERE inviter_id IS NOT NULL AND daily_turnover > 0");
    $users = $stmt->fetchAll();

    $processedCount = 0;
    $totalRebate = 0;

    foreach ($users as $user) {
        $turnover = (float)$user['daily_turnover'];
        if ($turnover < 100) continue;

        // Calculate rebate: 1 per 100
        $rebateAmount = floor($turnover / 100);
        if ($rebateAmount <= 0) continue;

        $db->beginTransaction();
        try {
            // Give rebate to inviter
            $inviterId = $user['inviter_id'];

            // Log balance for inviter
            \App\Model\User::updateBalance($inviterId, $rebateAmount, 'rebate', "下级 [{$user['username']}] 流水返利 (流水: $turnover)", $db);

            // Log into rebates table
            $st = $db->prepare("INSERT INTO rebates (user_id, sub_id, type, amount) VALUES (?, ?, 'turnover', ?)");
            $st->execute([$inviterId, $user['id'], $rebateAmount]);

            $db->commit();
            $processedCount++;
            $totalRebate += $rebateAmount;
            echo "Rebated $rebateAmount to Inviter ID $inviterId from User ID {$user['id']} (Turnover: $turnover)\n";
        } catch (Exception $e) {
            $db->rollBack();
            echo "Error processing rebate for User ID {$user['id']}: " . $e->getMessage() . "\n";
        }
    }

    // 2. Reset DAILY turnover for EVERYONE
    $db->exec("UPDATE users SET daily_turnover = 0");

    echo "[" . date('Y-m-d H:i:s') . "] Task Completed. Processed: $processedCount, Total Rebates: ¥ $totalRebate\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
