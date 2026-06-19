<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>系统设置 - PC28 PRO</title>
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
            <a href="/admin/index.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-home w-5"></i> 控制台大盘</a>
            <a href="/admin/pages/settings.php" class="bg-indigo-600 px-4 py-3 rounded-xl font-bold" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-cog w-5"></i> 系统全局配置</a>
            <a href="/admin/pages/chat.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5"></i> 在线客服中心</a>
            <a href="/admin/pages/users.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5"></i> 会员管理</a>
            <a href="/admin/pages/finance_list.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-wallet w-5"></i> 财务充提审批</a>
            <a href="/admin/pages/profile.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-user-shield w-5"></i> 修改管理账号</a>
        </nav>
    </div></div>

    <div class="admin-main p-10">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">系统全局配置</h2>
            <p class="text-slate-400 text-sm">管理公告、代理链接等远程参数</p>
        </header>

        <div class="max-w-2xl bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100">
            <div class="space-y-8">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-3">首页滚动公告</label>
                    <textarea id="announcement" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 outline-none focus:border-indigo-500 font-bold text-sm h-32"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-3">代理注册链接前缀</label>
                    <input type="text" id="agent-link" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 outline-none focus:border-indigo-500 font-bold text-sm">
                </div>
                <button onclick="saveSettings()" class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black shadow-lg shadow-indigo-100 active:scale-95 transition-all">确认保存配置</button>
            </div>
        </div>
    </div>

    <script>
        async function loadSettings() {
            const res = await fetch('/api/system_info.php?action=get_settings').then(r => r.json());
            if(res.success) {
                document.getElementById('announcement').value = res.data.announcement;
                document.getElementById('agent-link').value = res.data.agent_link_prefix;
            }
        }
        async function saveSettings() {
            const ann = document.getElementById('announcement').value;
            const link = document.getElementById('agent-link').value;
            const res = await fetch('/admin/api/update_settings.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({announcement: ann, agent_link_prefix: link})
            }).then(r => r.json());
            alert(res.message);
        }
        loadSettings();
    </script>
</body>
</html>
