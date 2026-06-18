<?php
session_start();
require_once __DIR__ . '/../../src/Model/User.php';
require_once __DIR__ . '/../../src/Utils/DB.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.html');
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();

if (isset($_POST['action'])) {
    $uid = $_POST['user_id'];
    if ($_POST['action'] === 'bonus') {
        \App\Model\User::addBonus($uid, 20, $db);
        $msg = "已为用户 ID: $uid 发送新人福利 (20元)";
    } elseif ($_POST['action'] === 'toggle') {
        $stmt = $db->prepare("UPDATE users SET status = 1 - status WHERE id = :id");
        $stmt->execute(array('id' => $uid));
    }
}

$users = \App\Model\User::getAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户管理 - PC28 PRO</title>
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
            <a href="/admin/index.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-home w-5 text-slate-400"></i> 仪表盘
            </a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 bg-indigo-600 px-4 py-3 rounded-xl font-bold">
                <i class="fas fa-users w-5"></i> 用户管理
            </a>
            <a href="/admin/pages/finance_list.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-wallet w-5 text-slate-400"></i> 财务审核
            </a>
            <a href="/admin/pages/bets.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors">
                <i class="fas fa-history w-5 text-slate-400"></i> 注单监控
            </a>
        </nav>
    </div>

    <div class="admin-main p-10">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">用户管理</h2>
            <p class="text-slate-400 text-sm">管理平台注册用户及状态</p>
        </header>

        <?php if(isset($msg)): ?>
        <div class="bg-green-500 text-white p-4 rounded-xl mb-6 font-bold text-sm shadow-lg animate-bounce">
            <i class="fas fa-check-circle mr-2"></i> <?= $msg ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-widest font-black">
                        <th class="px-8 py-5">ID</th>
                        <th class="px-8 py-5">用户名</th>
                        <th class="px-8 py-5">余额</th>
                        <th class="px-8 py-5">累计流水</th>
                        <th class="px-8 py-5">状态</th>
                        <th class="px-8 py-5">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5 font-bold text-slate-400"><?= $user['id'] ?></td>
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold uppercase"><?= $user['username'][0] ?></div>
                                <span class="font-black text-slate-900"><?= htmlspecialchars($user['username']) ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-5 font-black text-indigo-600">¥ <?= number_format($user['balance'], 2) ?></td>
                        <td class="px-8 py-5 font-bold text-slate-500">¥ <?= number_format($user['total_turnover'], 2) ?></td>
                        <td class="px-8 py-5">
                            <?php if ($user['status'] == 1): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-[10px] font-black">ACTIVE</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-[10px] font-black">LOCKED</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-5">
                            <form method="POST" class="flex gap-2">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button name="action" value="toggle" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[10px] font-black transition-colors">切换状态</button>
                                <button name="action" value="bonus" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-[10px] font-black transition-colors">送福利</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
