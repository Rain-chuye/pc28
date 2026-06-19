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

// Today Stats
$todayProfit = $db->query("SELECT SUM(bet_amount - win_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
$todayRebates = $db->query("SELECT SUM(amount) FROM rebates WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
$todayNet = $todayProfit - $todayRebates;

// Pending Requests
$pendingDeposit = $db->query("SELECT COUNT(*) FROM finance_requests WHERE type='deposit' AND status='pending'")->fetchColumn();
$pendingWithdraw = $db->query("SELECT COUNT(*) FROM finance_requests WHERE type='withdraw' AND status='pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>PC28 商业版 - 管理中心</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: -apple-system, sans-serif; }
        .admin-sidebar { height: 100vh; position: fixed; left: 0; top: 0; width: 260px; background: #0f172a; color: white; z-index: 100; }
        .admin-main { margin-left: 260px; min-height: 100vh; }
        .stat-card { background: white; padding: 2rem; border-radius: 2rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
    </style>
</head>
<body>
    <div class="admin-sidebar p-6 flex flex-col">
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black italic">28</div>
            <h1 class="text-xl font-black">PC28 <span class="text-indigo-400">PRO</span></h1>
        </div>

        <nav class="flex-1 space-y-2">
            <a href="/admin/index.php" class="flex items-center gap-3 bg-indigo-600 px-4 py-3 rounded-xl font-bold">
                <i class="fas fa-home w-5"></i> 控制台大盘
            </a>
            <a href="/admin/pages/finance.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-gift w-5 text-slate-400"></i> 红包/财务管理
            </a>
            <a href="/admin/pages/odds.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-percentage w-5 text-slate-400"></i> 赔率规则管理
            </a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-users w-5 text-slate-400"></i> 会员管理
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 在线客服中心</a>
            </a>
            <a href="/admin/pages/finance_list.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-wallet w-5 text-slate-400"></i> 财务充提审批
            </a>
            <a href="/admin/pages/profile.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-user-shield w-5 text-slate-400"></i> 修改管理账号
            </a>
        </nav>

        <div class="pt-6 border-t border-white/5">
            <a href="/api/logout.php" class="text-slate-500 text-sm hover:text-white transition-colors">退出管理系统</a>
        </div>
    </div>

    <div class="admin-main p-10">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-2xl font-black text-slate-900">控制台大盘</h2>
                <p class="text-slate-400 text-sm">实时监控平台盈亏及核心业务指标</p>
            </div>
            <div class="flex gap-4">
                <div class="bg-indigo-600 text-white px-6 py-3 rounded-2xl shadow-lg shadow-indigo-100 flex items-center gap-3">
                    <span class="text-[10px] font-black uppercase tracking-widest opacity-60">Today Net</span>
                    <span class="text-lg font-black">¥ <?= number_format($todayNet, 2) ?></span>
                </div>
            </div>
        </header>

        <!-- Quick Alerts -->
        <?php if($pendingDeposit > 0 || $pendingWithdraw > 0): ?>
        <div class="mb-10 flex gap-4">
            <?php if($pendingDeposit > 0): ?>
            <div onclick="location.href='/admin/pages/finance_list.php'" class="flex-1 bg-amber-50 border border-amber-100 p-4 rounded-2xl flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center text-white"><i class="fas fa-arrow-down"></i></div>
                    <span class="text-sm font-black text-amber-800">有 <?= $pendingDeposit ?> 笔充值待处理</span>
                </div>
                <i class="fas fa-chevron-right text-amber-300"></i>
            </div>
            <?php endif; ?>
            <?php if($pendingWithdraw > 0): ?>
            <div onclick="location.href='/admin/pages/finance_list.php'" class="flex-1 bg-rose-50 border border-rose-100 p-4 rounded-2xl flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-rose-500 rounded-full flex items-center justify-center text-white"><i class="fas fa-arrow-up"></i></div>
                    <span class="text-sm font-black text-rose-800">有 <?= $pendingWithdraw ?> 笔提现待处理</span>
                </div>
                <i class="fas fa-chevron-right text-rose-300"></i>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="grid grid-cols-4 gap-6 mb-10">
            <div class="stat-card border-indigo-100">
                <p class="text-[10px] text-slate-400 font-black uppercase mb-2">平台总净收</p>
                <h3 class="text-3xl font-black text-indigo-600">¥ <?= number_format($netProfit, 2) ?></h3>
                <p class="text-[9px] text-slate-400 mt-2 font-bold">(下注 - 派奖 - 代理佣金)</p>
            </div>
            <div class="stat-card">
                <p class="text-[10px] text-slate-400 font-black uppercase mb-2">累计代理支出</p>
                <h3 class="text-3xl font-black text-slate-900">¥ <?= number_format($totalRebates, 2) ?></h3>
            </div>
            <div class="stat-card">
                <p class="text-[10px] text-slate-400 font-black uppercase mb-2">充提差 (盈余)</p>
                <h3 class="text-3xl font-black text-green-600">¥ <?= number_format($totalDeposit - $totalWithdraw, 2) ?></h3>
            </div>
            <div class="stat-card">
                <p class="text-[10px] text-slate-400 font-black uppercase mb-2">活跃玩家</p>
                <h3 class="text-3xl font-black text-slate-900"><?= $totalUsers ?></h3>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="bg-white p-8 rounded-[2rem] border border-slate-100">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">今日流水数据</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                        <span class="text-sm font-bold text-slate-600">今日总下注</span>
                        <span class="text-sm font-black text-slate-900">¥ <?= number_format($db->query("SELECT SUM(bet_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                        <span class="text-sm font-bold text-slate-600">今日派奖额</span>
                        <span class="text-sm font-black text-rose-500">¥ <?= number_format($db->query("SELECT SUM(win_amount) FROM bets WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center py-3">
                        <span class="text-sm font-bold text-slate-600">今日代理返佣</span>
                        <span class="text-sm font-black text-amber-500">¥ <?= number_format($todayRebates, 2) ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] border border-slate-100">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">快捷操作</h4>
                <div class="grid grid-cols-2 gap-4">
                    <button onclick="location.href='/admin/pages/users.php'" class="p-4 bg-slate-50 rounded-2xl text-left hover:bg-indigo-50 transition-colors">
                        <i class="fas fa-user-plus text-indigo-500 mb-2"></i>
                        <p class="text-xs font-black text-slate-700">会员管理</p>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 在线客服中心</a>
                    </button>
                    <button onclick="location.href='/admin/pages/finance_list.php'" class="p-4 bg-slate-50 rounded-2xl text-left hover:bg-amber-50 transition-colors">
                        <i class="fas fa-file-invoice-dollar text-amber-500 mb-2"></i>
                        <p class="text-xs font-black text-slate-700">充值审批</p>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
