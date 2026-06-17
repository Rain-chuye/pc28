<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理后台 - 加拿大 28</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-400 p-6 space-y-8">
        <h1 class="text-white font-black text-xl px-2">ADMIN PANEL</h1>
        <nav class="space-y-1">
            <a href="#" onclick="location.reload()" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fas fa-chart-pie"></i> 概览
            </a>
            <a href="#" onclick="loadPage('users')" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fas fa-users"></i> 用户管理
            </a>
            <a href="#" onclick="loadPage('finance')" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fas fa-wallet"></i> 财务审核
            </a>
            <a href="#" onclick="loadPage('odds')" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 hover:text-white transition-all">
                <i class="fas fa-percentage"></i> 赔率调整
            </a>
        </nav>
    </aside>

    <!-- Main -->
    <main class="flex-1 p-10">
        <header class="flex justify-between items-center mb-10">
            <h2 id="page-title" class="text-2xl font-black">控制台概览</h2>
            <div class="flex items-center gap-4">
                <span class="text-sm font-bold text-slate-500">管理员: admin</span>
                <button onclick="location.href='/api/logout.php'" class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center"><i class="fas fa-power-off text-rose-500"></i></button>
            </div>
        </header>

        <div id="content-area">
            <!-- Dashboard Stats -->
            <div class="grid grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">总充值</p>
                    <h3 class="text-2xl font-black">¥ <span id="total-deposit">0.00</span></h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">总提现</p>
                    <h3 class="text-2xl font-black">¥ <span id="total-withdraw">0.00</span></h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">注册用户</p>
                    <h3 class="text-2xl font-black"><span id="active-users">0</span></h3>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">待审核财务</p>
                    <h3 class="text-2xl font-black text-amber-500"><span id="pending-finance">0</span></h3>
                </div>
            </div>
        </div>
    </main>

    <script>
        function loadPage(page) {
            const title = {
                dashboard: '控制台概览',
                users: '用户管理',
                finance: '财务审核',
                odds: '赔率调整'
            };
            document.getElementById('page-title').innerText = title[page];

            if(page === 'finance') {
                fetch('/admin/pages/finance_list.php').then(r => r.text()).then(html => {
                    document.getElementById('content-area').innerHTML = html;
                    loadFinance();
                });
            } else if (page === 'users') {
                 document.getElementById('content-area').innerHTML = `
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs text-slate-400 uppercase tracking-widest">
                                    <th class="pb-4">ID</th>
                                    <th class="pb-4">用户名</th>
                                    <th class="pb-4">余额</th>
                                    <th class="pb-4">累计流水</th>
                                    <th class="pb-4">角色</th>
                                    <th class="pb-4">操作</th>
                                </tr>
                            </thead>
                            <tbody id="user-table-body"></tbody>
                        </table>
                    </div>
                 `;
                 loadUsers();
            }
        }

        async function loadStats() {
            try {
                const res = await fetch('/admin/api/stats.php');
                const data = await res.json();
                if(data.success) {
                    document.getElementById('total-deposit').innerText = parseFloat(data.deposit).toLocaleString();
                    document.getElementById('total-withdraw').innerText = parseFloat(data.withdraw).toLocaleString();
                    document.getElementById('active-users').innerText = data.users;
                    document.getElementById('pending-finance').innerText = data.pending;
                }
            } catch(e) {}
        }

        async function loadUsers() {
            const res = await fetch('/admin/api/users_list.php');
            const data = await res.json();
            const body = document.getElementById('user-table-body');
            body.innerHTML = data.data.map(u => `
                <tr class="border-t border-slate-50">
                    <td class="py-4 text-sm">${u.id}</td>
                    <td class="py-4 text-sm font-bold">${u.username} ${u.is_robot == 1 ? '<span class="text-[10px] bg-slate-100 px-1 rounded">机器人</span>' : ''}</td>
                    <td class="py-4 text-sm text-emerald-600 font-bold">¥ ${parseFloat(u.balance).toFixed(2)}</td>
                    <td class="py-4 text-sm">${parseFloat(u.total_turnover).toFixed(2)}</td>
                    <td class="py-4 text-sm">${u.role}</td>
                    <td class="py-4">
                        <button class="text-indigo-600 text-xs font-bold">编辑</button>
                    </td>
                </tr>
            `).join('');
        }

        loadStats();
    </script>
</body>
</html>
