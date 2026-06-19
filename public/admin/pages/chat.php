<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>客服响应中心 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: -apple-system, sans-serif; }
        .admin-sidebar { height: 100vh; position: fixed; left: 0; top: 0; width: 260px; background: #0f172a; color: white; }
        .admin-main { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        .chat-bubble-admin { background: #4f46e5; color: white; border-radius: 18px 18px 2px 18px; }
        .chat-bubble-user { background: white; color: #1e293b; border-radius: 18px 18px 18px 2px; border: 1px solid #e2e8f0; }
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
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 bg-indigo-600 px-4 py-3 rounded-xl font-bold"><i class="fas fa-headset w-5"></i> 在线客服中心</a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5 text-slate-400"></i> 会员管理</a>
            <a href="/admin/pages/finance_list.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-wallet w-5 text-slate-400"></i> 财务充提审批</a>
            <a href="/admin/pages/profile.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-user-shield w-5 text-slate-400"></i> 修改管理账号</a>
        </nav>
    </div>

    <div class="admin-main">
        <header class="p-8 bg-white border-b border-slate-100 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black text-slate-900">客服响应中心</h2>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Support Dashboard</p>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-[10px] font-black text-green-500 bg-green-50 px-3 py-1 rounded-full uppercase">Service Online</span>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <!-- User List -->
            <div class="w-80 bg-white border-r border-slate-100 flex flex-col">
                <div class="p-4 bg-slate-50 border-b border-slate-100">
                    <input type="text" placeholder="搜索用户 ID..." class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold outline-none focus:border-indigo-500 transition-all">
                </div>
                <div id="user-list" class="flex-1 overflow-y-auto divide-y divide-slate-50">
                    <div class="p-10 text-center text-slate-300 text-xs font-bold uppercase">加载对话中...</div>
                </div>
            </div>

            <!-- Chat Space -->
            <div class="flex-1 flex flex-col bg-slate-50/50">
                <div id="chat-header" class="px-8 py-4 bg-white border-b border-slate-100 font-black text-slate-600 text-sm">
                    请在左侧选择一个对话
                </div>

                <div id="msg-box" class="flex-1 overflow-y-auto p-8 space-y-6">
                    <div class="h-full flex flex-col items-center justify-center text-slate-300">
                        <i class="fas fa-comments text-6xl mb-4"></i>
                        <p class="text-xs font-black uppercase tracking-widest">Select a user to start chatting</p>
                    </div>
                </div>

                <div class="p-6 bg-white border-t border-slate-100">
                    <div class="max-w-4xl mx-auto flex gap-4">
                        <input type="text" id="reply-input" class="flex-1 bg-slate-100 border-none rounded-2xl px-6 py-4 outline-none font-bold text-sm focus:ring-2 ring-indigo-100" placeholder="回复内容...">
                        <button onclick="sendReply()" class="bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black text-sm shadow-xl shadow-indigo-100 active:scale-95 transition-all">发送回复</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = 0;

        async function loadUsers() {
            const res = await fetch('/api/chat.php').then(r => r.json());
            const list = document.getElementById('user-list');
            if(res.data && res.data.length > 0) {
                list.innerHTML = res.data.map(u => `
                    <div onclick="selectUser(${u.sender_id})" class="p-5 cursor-pointer hover:bg-slate-50 transition-all flex items-center gap-4 ${currentUserId == u.sender_id ? 'bg-indigo-50 border-r-4 border-indigo-600' : ''}">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-black uppercase">${u.sender_id}</div>
                        <div>
                            <p class="font-black text-sm text-slate-800">用户 ${u.sender_id}</p>
                            <p class="text-[10px] text-slate-400 font-bold">查看最近消息</p>
                        </div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = '<div class="p-10 text-center text-slate-300 text-xs font-bold uppercase">暂无活跃对话</div>';
            }
        }

        async function selectUser(id) {
            currentUserId = id;
            document.getElementById('chat-header').innerHTML = `<div class="flex items-center gap-2"><span class="w-2 h-2 bg-green-500 rounded-full"></span> 正在与用户 <span class="text-indigo-600">${id}</span> 对话</div>`;
            loadMessages();
            loadUsers();
        }

        async function loadMessages() {
            if(!currentUserId) return;
            const res = await fetch(`/api/chat.php?user_id=${currentUserId}`).then(r => r.json());
            const box = document.getElementById('msg-box');
            if(res.data) {
                box.innerHTML = res.data.map(m => `
                    <div class="flex ${m.sender_id == 1 ? 'justify-end' : ''}">
                        <div class="max-w-[70%] p-4 text-sm font-bold shadow-sm ${m.sender_id == 1 ? 'chat-bubble-admin' : 'chat-bubble-user'}">
                            ${m.message}
                            <p class="text-[8px] mt-2 opacity-50 uppercase ${m.sender_id == 1 ? 'text-right' : ''}">${m.created_at}</p>
                        </div>
                    </div>
                `).join('');
                box.scrollTop = box.scrollHeight;
            }
        }

        async function sendReply() {
            const input = document.getElementById('reply-input');
            const msg = input.value;
            if(!msg || !currentUserId) return;
            await fetch('/api/chat.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({message: msg, receiver_id: currentUserId})
            });
            input.value = '';
            loadMessages();
        }

        loadUsers();
        setInterval(loadMessages, 3000);
    </script>
</body>
</html>
