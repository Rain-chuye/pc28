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
$totalRebates = $db->query("SELECT SUM(amount) FROM rebates")->fetchColumn() ?: 0;
$netProfit = $totalBets - $totalWins - $totalRebates;

$todayProfit = $db->query("SELECT SUM(bet_amount - win_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
$todayRebates = $db->query("SELECT SUM(amount) FROM rebates WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
$todayNet = $todayProfit - $todayRebates;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理大盘 - 东爷国际 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <h1>东爷国际 管理大盘</h1>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded">PRO v8</span>
        </div>
    </header>

    <main>
        <!-- Highlights -->
        <div class="card bg-indigo-600 text-white border-none shadow-indigo-200">
            <p class="text-[10px] font-black uppercase opacity-60 mb-2">今日总净收</p>
            <h2 class="text-4xl font-black">¥ <?= number_format($todayNet, 2) ?></h2>
            <div class="mt-4 flex gap-4 text-[9px] font-bold opacity-80 uppercase tracking-widest">
                <span>下注: <?= number_format($db->query("SELECT SUM(bet_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0, 0) ?></span>
                <span>派奖: <?= number_format($db->query("SELECT SUM(win_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0, 0) ?></span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-0">
            <div class="card p-5">
                <p class="text-[9px] text-slate-400 font-black uppercase mb-1">累计净收</p>
                <h3 class="text-xl font-black text-slate-800">¥ <?= number_format($netProfit, 2) ?></h3>
            </div>
            <div class="card p-5">
                <p class="text-[9px] text-slate-400 font-black uppercase mb-1">活跃会员</p>
                <h3 class="text-xl font-black text-slate-800"><?= $totalUsers ?></h3>
            </div>
        </div>

        <div class="card">
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">财务盈余分析</h4>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">累计充值</span>
                    <span class="text-xs font-black text-green-600">¥ <?= number_format($totalDeposit, 2) ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-600">累计提现</span>
                    <span class="text-xs font-black text-rose-500">¥ <?= number_format($totalWithdraw, 2) ?></span>
                </div>
                <div class="pt-3 border-t flex justify-between items-center">
                    <span class="text-xs font-black text-slate-800">实存盈余</span>
                    <span class="text-sm font-black text-blue-600">¥ <?= number_format($totalDeposit - $totalWithdraw, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="px-4 pb-4">
            <button onclick="location.href='/api/logout.php'" class="w-full py-4 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-rose-500 transition-colors">安全退出管理系统</button>
        </div>
    </main>
</body>
</html>
