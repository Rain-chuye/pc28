<?php require_once __DIR__ . '/../check_auth.php'; ?>
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
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black italic">28</div>
            <h1 class="text-xl font-black">PC28 <span class="text-indigo-400">PRO</span></h1>
        </div>
        <nav class="flex-1 space-y-2">
            <a href="/admin/index.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-home w-5 text-slate-400"></i> 控制台大盘</a>
            <a href="/admin/pages/settings.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-cog w-5 text-slate-400"></i> 系统全局配置</a>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 在线客服中心</a>
            <a href="/admin/pages/users.php" class="bg-indigo-600 flex items-center gap-3 px-4 py-3 rounded-xl font-bold"><i class="fas fa-users w-5"></i> 会员管理</a>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 在线客服中心</a>
            <a href="/admin/pages/finance_list.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-wallet w-5 text-slate-400"></i> 财务充提审批</a>
        </nav>
    </div>

    <div class="admin-main p-10">
        <header class="mb-10 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900">会员管理</h2>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 在线客服中心</a>
                <p class="text-slate-400 text-sm">修改余额、QQ、密码及状态</p>
            </div>
        </header>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] tracking-widest font-black">
                        <th class="px-8 py-5">ID / 用户</th>
                        <th class="px-8 py-5">QQ 号</th>
                        <th class="px-8 py-5">余额</th>
                        <th class="px-8 py-5">修改</th>
                        <th class="px-8 py-5">操作</th>
                    </tr>
                </thead>
                <tbody id="user-list" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>

    <script>
        async function loadUsers() {
            const res = await fetch('/admin/api/users_list.php').then(r => r.json());
            const list = document.getElementById('user-list');
            list.innerHTML = res.data.map(u => `
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-5">
                        <p class="font-black text-slate-900">${u.username}</p>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">ID: ${u.id}</p>
                    </td>
                    <td class="px-8 py-5">
                        <input id="qq-${u.id}" type="text" value="${u.qq_number || ''}" class="bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold w-32">
                    </td>
                    <td class="px-8 py-5">
                        <input id="bal-${u.id}" type="number" value="${u.balance}" class="bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold w-32">
                    </td>
                    <td class="px-8 py-5">
                        <input id="pass-${u.id}" type="text" placeholder="新密码" class="bg-slate-50 border border-slate-100 rounded-lg px-2 py-1 text-xs font-bold w-32">
                    </td>
                    <td class="px-8 py-5">
                        <button onclick="updateUser(${u.id})" class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-[10px] font-black shadow-lg shadow-indigo-100">保存</button>
                    </td>
                </tr>
            `).join('');
        }

        async function updateUser(id) {
            const payload = {
                user_id: id,
                qq: document.getElementById('qq-'+id).value,
                balance: document.getElementById('bal-'+id).value,
                password: document.getElementById('pass-'+id).value
            };
            const res = await fetch('/admin/api/update_profile.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json());
            if(res.success) alert('修改成功');
            else alert(res.message);
        }

        loadUsers();
    </script>
</body>
</html>
