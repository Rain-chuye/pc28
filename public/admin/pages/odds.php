<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>赔率管理 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <h1>赔率配置中心</h1>
        <div class="w-6"></div>
    </header>

    <main class="space-y-4">
        <div class="card">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">全玩法赔率设定</h3>
            <div id="odds-list" class="grid grid-cols-2 gap-4">
                <div class="p-10 text-center col-span-2 text-slate-300 font-bold text-xs">正在载入赔率数据...</div>
            </div>
        </div>
    </main>

    <script>
        async function loadOdds() {
            const res = await fetch('/api/lottery.php').then(r => r.json());
            const list = document.getElementById('odds-list');
            if(res.success) {
                list.innerHTML = Object.keys(res.odds).map(k => `
                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-100">
                        <p class="text-[10px] font-black text-indigo-600 uppercase mb-4 tracking-widest">${k}</p>
                        <div class="space-y-4">
                            <div class="input-group mb-0">
                                <label>低倍房 (2.0)</label>
                                <input type="number" id="low-${k}" value="${res.odds[k].low}" class="form-input">
                            </div>
                            <div class="input-group mb-0">
                                <label>高倍房 (2.8)</label>
                                <input type="number" id="high-${k}" value="${res.odds[k].high}" class="form-input">
                            </div>
                            <button onclick="updateOdds('${k}')" class="w-full py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black hover:bg-indigo-600 hover:text-white transition-all">保存</button>
                        </div>
                    </div>
                `).join('');
            }
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
            if(res.success) alert(key + ' 已更新');
        }

        loadOdds();
    </script>
</body>
</html>
