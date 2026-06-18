<?php
require_once __DIR__ . '/../../../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();

$bets = $db->query("SELECT b.*, u.username FROM bets b JOIN users u ON b.user_id = u.id ORDER BY b.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">投注历史</h1>
</div>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>用户</th>
                    <th>期号</th>
                    <th>玩法</th>
                    <th>金额</th>
                    <th>赔率</th>
                    <th>奖金</th>
                    <th>状态</th>
                    <th>时间</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bets as $bet): ?>
                <tr>
                    <td><?php echo $bet['id']; ?></td>
                    <td><?php echo htmlspecialchars($bet['username']); ?></td>
                    <td><?php echo htmlspecialchars($bet['issue_no']); ?></td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($bet['play_type']); ?></span></td>
                    <td class="font-monospace">¥ <?php echo number_format($bet['bet_amount'], 2); ?></td>
                    <td><?php echo $bet['odds']; ?></td>
                    <td class="text-success">¥ <?php echo number_format($bet['win_amount'], 2); ?></td>
                    <td>
                        <?php if ($bet['status'] == 0): ?>
                            <span class="badge bg-warning">待开奖</span>
                        <?php elseif ($bet['status'] == 1): ?>
                            <span class="badge bg-success">中奖</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">未中</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?php echo $bet['created_at']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bets)): ?>
                <tr><td colspan="9" class="text-center text-muted">暂无数据</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
