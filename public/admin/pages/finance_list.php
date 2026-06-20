<?php require_once __DIR__ . '/../check_auth.php'; ?>
<?php
require_once __DIR__ . '/../../../src/Utils/DB.php';
$db = \App\Utils\DB::getInstance()->getConnection();
$stmt = $db->query("SELECT f.*, u.username FROM finance_requests f JOIN users u ON f.user_id = u.id ORDER BY f.id DESC LIMIT 50");
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>财务审核 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .type-deposit { color: #10b981; }
        .type-withdraw { color: #f43f5e; }
        .status-pending { background: #eef2ff; color: #4f46e5; }
        .status-approved { background: #f0fdf4; color: #10b981; }
        .status-rejected { background: #fef2f2; color: #f43f5e; }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>财务充提审批</h1>
        <div class="w-6"></div>
    </header>

    <main class="space-y-4 pt-2">
        <?php foreach ($requests as $req): ?>
        <div class="card p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase status-<?= $req['status'] ?>">
                        <?= $req['status'] === 'pending' ? '待审核' : ($req['status'] === 'approved' ? '已通过' : '已拒绝') ?>
                    </span>
                    <h3 class="text-sm font-black text-slate-800 mt-2"><?= htmlspecialchars($req['username']) ?></h3>
                    <p class="text-[9px] text-slate-400 font-bold mt-1"><?= $req['created_at'] ?></p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-black <?= $req['type'] === 'deposit' ? 'type-deposit' : 'type-withdraw' ?>">
                        <?= $req['type'] === 'deposit' ? '+' : '-' ?> ¥ <?= number_format($req['amount'], 2) ?>
                    </p>
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest"><?= $req['type'] === 'deposit' ? '充值上分' : '提现回分' ?></p>
                </div>
            </div>

            <?php if($req['status'] === 'pending'): ?>
            <div class="flex gap-3 pt-4 border-t border-slate-50">
                <button onclick="review(<?= $req['id'] ?>, 'approved')" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-black text-[10px] shadow-lg shadow-indigo-100">通过</button>
                <button onclick="openRefusal(<?= $req['id'] ?>)" class="flex-1 py-3 bg-white border border-slate-200 text-rose-500 rounded-xl font-black text-[10px]">拒绝</button>
            </div>
            <?php elseif($req['status'] === 'rejected' && !empty($req['refusal_reason'])): ?>
            <div class="mt-3 p-3 bg-rose-50 rounded-xl border border-rose-100">
                <p class="text-[9px] text-rose-400 font-bold">拒绝理由: <?= htmlspecialchars($req['refusal_reason']) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </main>

    <!-- Refusal Modal -->
    <div id="refusalModal" class="fixed inset-0 z-[2000] bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-6">
        <div class="bg-white rounded-[2rem] p-8 w-full max-w-sm shadow-2xl">
            <h3 class="text-lg font-black text-slate-800 mb-4">拒绝申请</h3>
            <textarea id="refusal-reason" class="form-input h-24 mb-6" placeholder="请输入拒绝理由..."></textarea>
            <div class="flex gap-4">
                <button onclick="closeRefusal()" class="flex-1 py-4 bg-slate-100 text-slate-400 rounded-xl font-black text-xs">取消</button>
                <button onclick="confirmRefusal()" class="flex-1 py-4 bg-rose-600 text-white rounded-xl font-black text-xs shadow-lg">确认拒绝</button>
            </div>
        </div>
    </div>

    <script>
        let currentId = null;
        function openRefusal(id) { currentId = id; document.getElementById('refusalModal').classList.remove('hidden'); }
        function closeRefusal() { document.getElementById('refusalModal').classList.add('hidden'); }

        async function review(id, status, reason = '') {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', status);
            fd.append('reason', reason);
            const res = await fetch('/admin/api/finance_review.php', { method: 'POST', body: fd }).then(r => r.json());
            if(res.success) location.reload(); else alert(res.message);
        }

        function confirmRefusal() {
            const r = document.getElementById('refusal-reason').value;
            if(!r) return alert('请输入理由');
            review(currentId, 'rejected', r);
        }
    </script>
</body>
</html>
