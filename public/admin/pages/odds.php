<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>赔率管理 - PC28 PRO</title>
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
            <a href="/admin/pages/settings.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-cog w-5 text-slate-400"></i> 系统全局配置</a>
            <a href="/admin/pages/odds.php" class="bg-indigo-600 flex items-center gap-3 px-4 py-3 rounded-xl font-bold"><i class="fas fa-percentage w-5"></i> 赔率规则管理</a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5 text-slate-400"></i> 会员管理</a>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 在线客服中心</a>
        </nav>
    </div>

    <div class="admin-main p-10">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">赔率配置中心</h2>
            <p class="text-slate-400 text-sm">支持修改所有特码、组合、大小单双、豹子对子顺子倍率</p>
        </header>

        <div class="grid grid-cols-1 gap-8">
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm">
                <div class="grid grid-cols-4 gap-4" id="odds-list">
                    <div class="p-10 text-center col-span-4 text-slate-300 font-black uppercase">加载数据中...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadOdds() {
            const res = await fetch('/api/lottery.php').then(r => r.json());
            const list = document.getElementById('odds-list');
            list.innerHTML = Object.keys(res.odds).map(k => `
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-3">${k}</p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[9px] font-bold text-slate-400">2.0房</span>
                            <input type="number" id="low-${k}" value="${res.odds[k].low}" class="w-16 bg-white border border-slate-200 rounded-lg px-2 py-1 text-xs font-black text-indigo-600">
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[9px] font-bold text-slate-400">2.8房</span>
                            <input type="number" id="high-${k}" value="${res.odds[k].high}" class="w-16 bg-white border border-slate-200 rounded-lg px-2 py-1 text-xs font-black text-indigo-600">
                        </div>
                        <button onclick="updateOdds('${k}')" class="w-full mt-2 py-1.5 bg-white border border-slate-200 rounded-lg text-[9px] font-black hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">确认</button>
                    </div>
                </div>
            `).join('');
        }

        async function updateOdds(key) {
            const payload = {
                play_type: key,
                odds_low: document.getElementById('low-'+key).value,
                odds_high: document.getElementById('high-'+key).value
            };
            const res = await fetch('/admin/api/odds_update.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json());
            if(res.success) console.log(key + ' Updated');
        }

        loadOdds();
    </script>
</body>
</html>
