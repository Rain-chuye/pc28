<?php
require_once __DIR__ . '/../check_auth.php';
require_once __DIR__ . '/../../../src/Utils/DB.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员设置 - PC28 PRO</title>
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
            <a href="/admin/pages/settings.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-cog w-5"></i> 系统全局配置</a>
            <a href="/admin/pages/chat.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5"></i> 在线客服中心</a>
            <a href="/admin/pages/users.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5"></i> 会员管理</a>
            <a href="/admin/pages/finance_list.php" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-wallet w-5"></i> 财务充提审批</a>
            <a href="/admin/pages/profile.php" class="bg-indigo-600 px-4 py-3 rounded-xl font-bold" class="sidebar-link flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-user-shield w-5"></i> 修改管理账号</a>
        </nav>
    </div></div>

    <div class="admin-main p-10">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">账号设置</h2>
            <p class="text-slate-400 text-sm">修改管理员登录凭据</p>
        </header>

        <div class="max-w-md bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">管理账号</label>
                    <input type="text" id="admin-user" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold" value="<?= $_SESSION['username'] ?>">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">重置密码 (不改留空)</label>
                    <input type="password" id="admin-pass" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-indigo-500 font-bold" placeholder="输入新密码">
                </div>
                <button onclick="updateProfile()" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-black shadow-lg shadow-indigo-100 active:scale-95 transition-all">保存更改</button>
            </div>
        </div>
    </div>

    <script>
        async function updateProfile() {
            const u = document.getElementById('admin-user').value;
            const p = document.getElementById('admin-pass').value;
            const res = await fetch('/admin/api/update_profile.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({username: u, password: p})
            }).then(r => r.json());
            alert(res.message);
            if(res.success) location.reload();
        }
    </script>
</body>
</html>
