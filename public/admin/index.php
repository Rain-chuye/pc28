<?php
require_once __DIR__ . '/check_auth.php';
require_once __DIR__ . '/../../src/Utils/DB.php';

$db = \App\Utils\DB::getInstance()->getConnection();

// Global Stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE is_robot = 0")->fetchColumn();
$totalDeposit = $db->query("SELECT SUM(amount) FROM finance_requests WHERE type='deposit' AND status='approved'")->fetchColumn() ?: 0;
$totalWithdraw = $db->query("SELECT SUM(amount) FROM finance_requests WHERE type='withdraw' AND status='approved'")->fetchColumn() ?: 0;
$totalBets = $db->query("SELECT SUM(bet_amount) FROM bets")->fetchColumn() ?: 0;
$totalWins = $db->query("SELECT SUM(win_amount) FROM bets")->fetchColumn() ?: 0;
$netProfit = $totalBets - $totalWins;

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>PC28 商业版 - 管理中心</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar { height: 100vh; position: fixed; left: 0; top: 0; width: 260px; background: #0f172a; color: white; }
        .admin-main { margin-left: 260px; min-height: 100vh; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="admin-sidebar p-6 flex flex-col">
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black">P</div>
            <h1 class="text-xl font-black">PC28 <span class="text-indigo-400">PRO</span></h1>
        </div>

        <nav class="flex-1 space-y-2">
            <a href="/admin/index.php" class="flex items-center gap-3 bg-indigo-600 px-4 py-3 rounded-xl font-bold">
                <i class="fas fa-home w-5"></i> 仪表盘
            </a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-users w-5 text-slate-400"></i> 用户管理
            </a>
            <a href="/admin/pages/finance_list.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-wallet w-5 text-slate-400"></i> 财务审核
            </a>
            <a href="/admin/pages/bets.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-history w-5 text-slate-400"></i> 注单监控
            </a>
            <a href="/admin/pages/odds.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-cog w-5 text-slate-400"></i> 赔率设置
            </a>
        </nav>

        <div class="pt-6 border-t border-white/5">
            <a href="/api/logout.php" class="text-slate-500 text-sm hover:text-white transition-colors">退出登录</a>
        </div>
    </div>

    <div class="admin-main p-10">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-2xl font-black text-slate-900">数据概览</h2>
                <p class="text-slate-400 text-sm">实时监控平台运行状况</p>
            </div>
            <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[10px] text-slate-400 font-bold uppercase">当前状态</p>
                    <p class="text-xs font-black text-green-500">正在运行</p>
                </div>
                <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center text-green-500"><i class="fas fa-check"></i></div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <p class="text-[10px] text-slate-400 font-black uppercase mb-2">平台盈亏</p>
                <h3 class="text-3xl font-black text-slate-900">¥ <?= number_format($netProfit, 2) ?></h3>
                <p class="text-xs text-indigo-500 mt-2 font-bold">净收益 (注额-中奖)</p>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <p class="text-[10px] text-slate-400 font-black uppercase mb-2">累计充值</p>
                <h3 class="text-3xl font-black text-slate-900">¥ <?= number_format($totalDeposit, 2) ?></h3>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <p class="text-[10px] text-slate-400 font-black uppercase mb-2">累计提现</p>
                <h3 class="text-3xl font-black text-slate-900">¥ <?= number_format($totalWithdraw, 2) ?></h3>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <p class="text-[10px] text-slate-400 font-black uppercase mb-2">活跃用户</p>
                <h3 class="text-3xl font-black text-slate-900"><?= $totalUsers ?></h3>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-10">
            <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">玩法注额分布</h4>
                <div class="space-y-4">
                    <?php
                    $stmt = $db->query("SELECT play_type, SUM(bet_amount) as total FROM bets GROUP BY play_type ORDER BY total DESC LIMIT 5");
                    while($row = $stmt->fetch()) {
                        $p = $totalBets > 0 ? ($row['total'] / $totalBets) * 100 : 0;
                    ?>
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold">
                            <span><?= $row['play_type'] ?></span>
                            <span><?= number_format($p, 1) ?>%</span>
                        </div>
                        <div class="w-full bg-slate-50 h-2 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-full" style="width: <?= $p ?>%"></div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">最新注单</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-slate-400">
                                <th class="pb-4">用户</th>
                                <th class="pb-4">玩法</th>
                                <th class="pb-4">金额</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("SELECT b.*, u.username FROM bets b JOIN users u ON b.user_id = u.id ORDER BY b.id DESC LIMIT 5");
                            while($row = $stmt->fetch()) {
                            ?>
                            <tr>
                                <td class="py-3 font-bold"><?= $row['username'] ?></td>
                                <td class="py-3 uppercase text-xs"><?= $row['play_type'] ?></td>
                                <td class="py-3 font-black">¥ <?= $row['bet_amount'] ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
