<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>客服中心 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar { height: 100vh; position: fixed; left: 0; top: 0; width: 260px; background: #0f172a; color: white; }
        .admin-main { margin-left: 260px; min-height: 100vh; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="admin-sidebar p-6">
        <h1 class="text-xl font-black mb-10">PC28 <span class="text-indigo-400">PRO</span></h1>
        <nav class="space-y-2">
            <a href="/admin/index.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-home w-5"></i> 仪表盘</a>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 bg-indigo-600 px-4 py-3 rounded-xl font-bold"><i class="fas fa-headset w-5"></i> 在线客服</a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5"></i> 用户管理</a>
        </nav>
    </div>

    <div class="admin-main p-10 flex flex-col h-screen">
        <header class="mb-10 shrink-0">
            <h2 class="text-2xl font-black text-slate-900">客服响应中心</h2>
        </header>

        <div class="flex-1 flex gap-6 overflow-hidden">
            <!-- User List -->
            <div class="w-64 bg-white rounded-2xl border border-slate-100 flex flex-col overflow-hidden">
                <div class="p-4 border-b border-slate-50 font-black text-xs uppercase tracking-widest text-slate-400">消息列表</div>
                <div id="user-list" class="flex-1 overflow-y-auto divide-y divide-slate-50"></div>
            </div>

            <!-- Chat Box -->
            <div class="flex-1 bg-white rounded-2xl border border-slate-100 flex flex-col overflow-hidden shadow-sm">
                <div id="chat-header" class="p-5 border-b border-slate-50 font-black text-slate-700">请选择对话</div>
                <div id="msg-box" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/50"></div>
                <div class="p-4 border-t border-slate-50 flex gap-4">
                    <input type="text" id="reply-input" class="flex-1 bg-slate-50 border-none rounded-xl px-6 outline-none font-bold text-sm" placeholder="输入回复内容...">
                    <button onclick="sendReply()" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-black text-sm shadow-lg active:scale-95 transition-all">发送</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = 0;

        async function loadUsers() {
            const res = await fetch('/api/chat.php').then(r => r.json());
            const list = document.getElementById('user-list');
            list.innerHTML = res.data.map(u => `
                <div onclick="selectUser(${u.sender_id})" class="p-4 cursor-pointer hover:bg-slate-50 transition-colors ${currentUserId == u.sender_id ? 'bg-indigo-50 border-l-4 border-indigo-600' : ''}">
                    <p class="font-black text-sm text-slate-700">用户 ID: ${u.sender_id}</p>
                </div>
            `).join('');
        }

        async function selectUser(id) {
            currentUserId = id;
            document.getElementById('chat-header').innerText = `正在与用户 ${id} 对话`;
            loadMessages();
            loadUsers();
        }

        async function loadMessages() {
            if(!currentUserId) return;
            const res = await fetch(`/api/chat.php?user_id=${currentUserId}`).then(r => r.json());
            const box = document.getElementById('msg-box');
            box.innerHTML = res.data.map(m => `
                <div class="flex ${m.sender_id == 1 ? 'justify-end' : ''}">
                    <div class="max-w-[70%] ${m.sender_id == 1 ? 'bg-indigo-600 text-white shadow-indigo-100' : 'bg-white text-slate-800 border border-slate-100'} p-4 rounded-2xl shadow-sm font-bold text-sm">
                        ${m.message}
                        <p class="text-[8px] mt-1 opacity-50 uppercase">${m.created_at}</p>
                    </div>
                </div>
            `).join('');
            box.scrollTop = box.scrollHeight;
        }

        async function sendReply() {
            const msg = document.getElementById('reply-input').value;
            if(!msg || !currentUserId) return;
            await fetch('/api/chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({message: msg, receiver_id: currentUserId})
            });
            document.getElementById('reply-input').value = '';
            loadMessages();
        }

        loadUsers();
        setInterval(loadMessages, 3000);
    </script>
</body>
</html>
