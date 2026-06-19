<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>客服与机器人 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar { height: 100vh; position: fixed; left: 0; top: 0; width: 260px; background: #0f172a; color: white; }
        .admin-main { margin-left: 260px; min-height: 100vh; background: #f8fafc; }
        .user-item.active { background-color: #eff6ff; border-left: 4px solid #4f46e5; }
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
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5 text-slate-400"></i> 会员管理</a>
            <a href="/admin/pages/chat.php" class="bg-indigo-600 flex items-center gap-3 px-4 py-3 rounded-xl font-bold"><i class="fas fa-headset w-5"></i> 客服与机器人</a>
            <a href="/admin/pages/finance_list.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-wallet w-5 text-slate-400"></i> 财务充提审批</a>
        </nav>
    </div>

    <div class="admin-main p-10 flex flex-col">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">客服与机器人</h2>
            <p class="text-slate-400 text-sm">分级对话管理及自动回复规则</p>
        </header>

        <div class="grid grid-cols-12 gap-8 flex-1 min-h-0">
            <!-- User List -->
            <div class="col-span-3 bg-white rounded-[2rem] shadow-sm border border-slate-100 flex flex-col overflow-hidden">
                <div class="p-6 border-b bg-slate-50/50">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">咨询会员列表</h3>
                </div>
                <div id="user-list" class="flex-1 overflow-y-auto">
                    <div class="p-10 text-center text-slate-300 text-xs font-bold">无活跃咨询</div>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="col-span-5 bg-white rounded-[2rem] shadow-sm border border-slate-100 flex flex-col overflow-hidden">
                <div class="p-6 border-b flex justify-between items-center bg-white shrink-0">
                    <h3 id="chat-title" class="font-black text-slate-800">请选择对话</h3>
                    <div id="online-status" class="hidden flex items-center gap-1.5 text-[9px] font-black text-green-500 uppercase">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Active
                    </div>
                </div>
                <div id="chat-box" class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/30"></div>
                <div class="p-6 border-t bg-white flex gap-4 shrink-0">
                    <input type="text" id="reply-msg" class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none text-xs font-bold focus:border-indigo-500 transition-colors" placeholder="输入回复内容...">
                    <button onclick="sendReply()" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-black text-xs shadow-lg shadow-indigo-100 active:scale-95 transition-all">发送</button>
                </div>
            </div>

            <!-- Robot Rules -->
            <div class="col-span-4 bg-white rounded-[2rem] shadow-sm border border-slate-100 flex flex-col overflow-hidden">
                <div class="p-6 border-b bg-slate-50/50">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">机器人自动回复规则</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="space-y-3">
                        <input type="text" id="rule-keyword" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-bold outline-none focus:border-indigo-400" placeholder="触发关键词 (如: 赔率)">
                        <textarea id="rule-response" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-bold outline-none focus:border-indigo-400 h-20" placeholder="自动回复内容..."></textarea>
                        <button onclick="addRule()" class="w-full py-3 bg-slate-900 text-white rounded-xl font-black text-xs shadow-lg shadow-slate-200 active:scale-95">添加规则</button>
                    </div>
                    <div id="rules-list" class="space-y-2 overflow-y-auto max-h-[300px]"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let usersData = [];

        async function loadConversations() {
            const res = await fetch('/api/chat.php?action=get_all_admin').then(r => r.json());
            if(res.success) {
                // Group by user
                const groups = {};
                res.data.forEach(m => {
                    const uid = m.sender_id === 0 ? m.receiver_id : m.sender_id;
                    if(!groups[uid]) groups[uid] = {id: uid, name: m.sender_name || '用户'+uid, last_msg: '', last_time: ''};
                    groups[uid].last_msg = m.message;
                    groups[uid].last_time = m.created_at;
                });

                const list = document.getElementById('user-list');
                const html = Object.values(groups).sort((a,b) => b.id - a.id).map(u => `
                    <div onclick="selectUser(${u.id}, '${u.name}')" class="user-item p-6 border-b border-slate-50 cursor-pointer hover:bg-slate-50 transition-colors ${currentUserId == u.id ? 'active' : ''}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-black text-slate-800">${u.name}</span>
                            <span class="text-[8px] text-slate-300 font-bold">${u.last_time.split(' ')[1]}</span>
                        </div>
                        <p class="text-[10px] text-slate-400 font-bold truncate">${u.last_msg}</p>
                    </div>
                `).join('');
                list.innerHTML = html || '<div class="p-10 text-center text-slate-300 text-xs font-bold">无活跃咨询</div>';

                if(currentUserId) renderChat(res.data.filter(m => m.sender_id == currentUserId || m.receiver_id == currentUserId));
            }
        }

        function selectUser(id, name) {
            currentUserId = id;
            document.getElementById('chat-title').innerText = `正在与 ${name} 对话`;
            document.getElementById('online-status').classList.remove('hidden');
            loadConversations();
        }

        function renderChat(msgs) {
            const box = document.getElementById('chat-box');
            box.innerHTML = msgs.map(m => `
                <div class="flex ${m.sender_id === 0 ? 'justify-end' : ''}">
                    <div class="max-w-[80%] p-4 rounded-2xl shadow-sm ${m.sender_id === 0 ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-100 text-slate-700'}">
                        <p class="text-xs font-bold">${m.message}</p>
                        <p class="text-[8px] mt-1 opacity-50 font-black uppercase text-right">${m.created_at.split(' ')[1]}</p>
                    </div>
                </div>
            `).join('');
            box.scrollTop = box.scrollHeight;
        }

        async function sendReply() {
            if(!currentUserId) return alert('请先从左侧选择对话用户');
            const msg = document.getElementById('reply-msg').value;
            if(!msg) return;
            const res = await fetch('/api/chat.php?action=send_admin', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({user_id: currentUserId, message: msg})
            }).then(r => r.json());
            if(res.success) {
                document.getElementById('reply-msg').value = '';
                loadConversations();
            }
        }

        async function loadRules() {
            const res = await fetch('/api/chat.php?action=get_bot_rules').then(r => r.json());
            if(res.success) {
                document.getElementById('rules-list').innerHTML = res.data.map(r => `
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="flex-1 min-w-0 pr-4">
                            <span class="px-2 py-0.5 bg-slate-200 text-slate-600 rounded text-[9px] font-black uppercase mr-2">${r.keyword || '通用'}</span>
                            <span class="text-[10px] font-bold text-slate-500 truncate">${r.response}</span>
                        </div>
                        <button onclick="deleteRule(${r.id})" class="text-rose-400 hover:text-rose-600 shrink-0"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                `).join('');
            }
        }

        async function addRule() {
            const k = document.getElementById('rule-keyword').value;
            const r = document.getElementById('rule-response').value;
            if(!r) return alert('内容不能为空');
            await fetch('/api/chat.php?action=add_bot_rule', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({keyword: k, response: r})
            });
            document.getElementById('rule-keyword').value = '';
            document.getElementById('rule-response').value = '';
            loadRules();
        }

        async function deleteRule(id) {
            await fetch(`/api/chat.php?action=delete_bot_rule&id=${id}`);
            loadRules();
        }

        setInterval(loadConversations, 4000);
        loadConversations();
        loadRules();
    </script>
</body>
</html>
