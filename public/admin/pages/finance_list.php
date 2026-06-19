<?php
session_start();
require_once __DIR__ . '/../../../src/Utils/DB.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.html');
    die;
}

$db = \App\Utils\DB::getInstance()->getConnection();

$stmt = $db->query("SELECT f.*, u.username FROM finance_requests f JOIN users u ON f.user_id = u.id ORDER BY f.id DESC LIMIT 50");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>财务审核 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar { height: 100vh; position: fixed; left: 0; top: 0; width: 260px; background: #0f172a; color: white; }
        .admin-main { margin-left: 260px; min-height: 100vh; background: #f8fafc; }
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
    </style>
</head>
<body>
    <div class="admin-sidebar p-6 flex flex-col">
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black italic">28</div>
            <h1 class="text-xl font-black">PC28 <span class="text-indigo-400">PRO</span></h1>
        </div>
        <nav class="flex-1 space-y-2">
            <a href="/admin/index.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-home w-5 text-slate-400"></i> 控制台大盘</a>
            <a href="/admin/pages/settings.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-cog w-5 text-slate-400"></i> 系统全局配置</a>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 客服与机器人</a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5 text-slate-400"></i> 会员管理</a>
            <a href="/admin/pages/finance_list.php" class="bg-indigo-600 px-4 py-3 rounded-xl font-bold flex items-center gap-3"><i class="fas fa-wallet w-5"></i> 财务充提审批</a>
        </nav>
    </div>

    <div class="admin-main p-10">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">财务审核</h2>
            <p class="text-slate-400 text-sm">处理用户充值及提现申请</p>
        </header>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-widest font-black">
                        <th class="px-8 py-5">类型</th>
                        <th class="px-8 py-5">用户</th>
                        <th class="px-8 py-5">金额</th>
                        <th class="px-8 py-5">时间</th>
                        <th class="px-8 py-5">状态 / 理由</th>
                        <th class="px-8 py-5 text-right">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($requests as $req): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5">
                            <?php if($req['type'] === 'deposit'): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-[10px] font-black">充值</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-rose-100 text-rose-600 rounded-full text-[10px] font-black">提现</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-5 font-black text-slate-900"><?= htmlspecialchars($req['username']) ?></td>
                        <td class="px-8 py-5 font-black <?= $req['type'] === 'deposit' ? 'text-green-600' : 'text-rose-600' ?>">¥ <?= number_format($req['amount'], 2) ?></td>
                        <td class="px-8 py-5 text-slate-400 font-bold"><?= $req['created_at'] ?></td>
                        <td class="px-8 py-5">
                            <?php if($req['status'] === 'pending'): ?>
                                <span class="text-indigo-600 animate-pulse font-black text-[10px]">待审核...</span>
                            <?php elseif($req['status'] === 'approved'): ?>
                                <span class="text-slate-400 font-black text-[10px]">已通过</span>
                            <?php else: ?>
                                <span class="text-red-400 font-black text-[10px]">已拒绝</span>
                                <?php if(!empty($req['refusal_reason'])): ?>
                                    <p class="text-[9px] text-red-300 italic mt-1 font-bold">理由: <?= htmlspecialchars($req['refusal_reason']) ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <?php if($req['status'] === 'pending'): ?>
                            <button onclick="review(<?= $req['id'] ?>, 'approved')" class="text-indigo-600 font-black text-[10px] hover:underline">直接通过</button>
                            <span class="mx-2 text-slate-200">|</span>
                            <button onclick="openRefusalModal(<?= $req['id'] ?>)" class="text-rose-400 font-black text-[10px] hover:underline">拒绝申请</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Refusal Modal -->
    <div id="refusalModal" class="modal">
        <div class="bg-white rounded-[2rem] p-8 max-w-sm w-full shadow-2xl">
            <h3 class="text-lg font-black text-slate-800 mb-4">拒绝提现申请</h3>
            <textarea id="refusalReason" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs font-bold outline-none mb-6 h-24" placeholder="请输入拒绝理由..."></textarea>
            <div class="flex gap-4">
                <button onclick="closeRefusalModal()" class="flex-1 py-4 bg-slate-100 text-slate-400 rounded-xl font-black text-xs">取消</button>
                <button onclick="confirmRefusal()" class="flex-1 py-4 bg-rose-600 text-white rounded-xl font-black text-xs shadow-lg shadow-rose-100">确认拒绝</button>
            </div>
        </div>
    </div>

    <script>
        let currentRequestId = null;

        function openRefusalModal(id) {
            currentRequestId = id;
            document.getElementById('refusalModal').classList.add('active');
        }

        function closeRefusalModal() {
            document.getElementById('refusalModal').classList.remove('active');
            document.getElementById('refusalReason').value = '';
        }

        async function review(id, status, reason = '') {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', status);
            fd.append('reason', reason);
            const res = await fetch('/admin/api/finance_review.php', { method: 'POST', body: fd }).then(r => r.json());
            if(res.success) location.reload();
            else alert(res.message);
        }

        function confirmRefusal() {
            const reason = document.getElementById('refusalReason').value;
            if(!reason) return alert('请填写拒绝理由');
            review(currentRequestId, 'rejected', reason);
        }
    </script>
</body>
</html>
