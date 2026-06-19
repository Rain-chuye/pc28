<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>红包/财务管理 - PC28 PRO</title>
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
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black italic">28</div>
            <h1 class="text-xl font-black">PC28 <span class="text-indigo-400">PRO</span></h1>
        </div>
        <nav class="flex-1 space-y-2">
            <a href="/admin/index.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-home w-5 text-slate-400"></i> 控制台大盘</a>
            <a href="/admin/pages/finance.php" class="bg-indigo-600 flex items-center gap-3 px-4 py-3 rounded-xl font-bold"><i class="fas fa-gift w-5"></i> 红包/财务中心</a>
            <a href="/admin/pages/odds.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-percentage w-5 text-slate-400"></i> 赔率规则管理</a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5 text-slate-400"></i> 会员管理</a>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 在线客服中心</a>
        </nav>
    </div>

    <div class="admin-main p-10">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">红包与福利中心</h2>
        </header>

        <div class="bg-white rounded-[2.5rem] p-10 border border-slate-100 shadow-sm max-w-2xl">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8">手动发放全服红包</h3>
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase">总金额 (¥)</p>
                        <input type="number" id="rp-amount" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 outline-none font-black text-slate-700" value="100.00">
                    </div>
                    <div class="space-y-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase">红包个数</p>
                        <input type="number" id="rp-count" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 outline-none font-black text-slate-700" value="10">
                    </div>
                </div>
                <div class="space-y-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase">最低流水要求 (¥)</p>
                    <input type="number" id="rp-turnover" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 outline-none font-black text-slate-700" value="0.00">
                </div>
                <div class="space-y-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase">附言内容</p>
                    <input type="text" id="rp-msg" class="w-full bg-slate-50 border-none rounded-2xl px-6 py-4 outline-none font-black text-slate-700" value="恭喜发财，大吉大利！">
                </div>
                <button onclick="sendRP()" class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black shadow-xl shadow-indigo-100 active:scale-95 transition-all">立即发放红包</button>
            </div>
        </div>
    </div>

    <script>
        async function sendRP() {
            const payload = {
                amount: document.getElementById('rp-amount').value,
                count: document.getElementById('rp-count').value,
                turnover: document.getElementById('rp-turnover').value,
                message: document.getElementById('rp-msg').value
            };
            const res = await fetch('/admin/api/send_red_packet.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json());
            if(res.success) alert('红包已发放！');
            else alert(res.message);
        }
    </script>
</body>
</html>
