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

    <div class="admin-main p-10">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">客服与机器人</h2>
            <p class="text-slate-400 text-sm">管理在线咨询及机器人关键词回复</p>
        </header>

        <div class="grid grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col h-[600px]">
                <h3 class="font-black text-slate-800 border-b pb-4 mb-6">在线咨询</h3>
                <div id="chat-list" class="flex-1 overflow-y-auto space-y-4 pr-4"></div>
                <div class="mt-6 pt-6 border-t flex gap-4">
                    <input type="text" id="reply-msg" class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none text-xs font-bold" placeholder="输入回复内容...">
                    <button onclick="sendReply()" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-black text-xs shadow-lg shadow-indigo-100">回复</button>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 space-y-6">
                <h3 class="font-black text-slate-800 border-b pb-4">机器人规则</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" id="rule-keyword" class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs font-bold outline-none" placeholder="关键词 (如: 赔率)">
                        <input type="text" id="rule-response" class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs font-bold outline-none" placeholder="回复内容">
                    </div>
                    <button onclick="addRule()" class="w-full py-4 bg-slate-900 text-white rounded-xl font-black text-xs">添加规则</button>
                </div>
                <div class="border-t pt-6">
                    <div id="rules-list" class="space-y-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;

        async function loadMessages() {
            const res = await fetch('/api/chat.php?action=get_all_admin').then(r => r.json());
            if(res.success) {
                const list = document.getElementById('chat-list');
                list.innerHTML = res.data.map(m => `
                    <div class="p-4 rounded-2xl ${m.sender_id === 0 ? 'bg-indigo-50 border border-indigo-100 ml-10' : 'bg-slate-50 border border-slate-100 mr-10'}" onclick="selectUser(${m.sender_id || m.receiver_id})">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[9px] font-black uppercase text-slate-400">${m.sender_name || 'Admin'}</span>
                            <span class="text-[9px] text-slate-300">${m.created_at}</span>
                        </div>
                        <p class="text-xs font-bold text-slate-700">${m.message}</p>
                    </div>
                `).join('');
                list.scrollTop = list.scrollHeight;
            }
        }

        function selectUser(id) {
            if(id === 0) return;
            currentUserId = id;
            document.getElementById('reply-msg').placeholder = `回复用户 ID: ${id}`;
        }

        async function sendReply() {
            if(!currentUserId) return alert('请先点击一条用户消息选择回复对象');
            const msg = document.getElementById('reply-msg').value;
            if(!msg) return;
            const res = await fetch('/api/chat.php?action=send_admin', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({user_id: currentUserId, message: msg})
            }).then(r => r.json());
            if(res.success) {
                document.getElementById('reply-msg').value = '';
                loadMessages();
            }
        }

        async function loadRules() {
            const res = await fetch('/api/chat.php?action=get_bot_rules').then(r => r.json());
            if(res.success) {
                document.getElementById('rules-list').innerHTML = res.data.map(r => `
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-600 rounded text-[9px] font-black uppercase mr-2">${r.keyword || '通用'}</span>
                            <span class="text-xs font-bold text-slate-600">${r.response}</span>
                        </div>
                        <button onclick="deleteRule(${r.id})" class="text-rose-400 hover:text-rose-600"><i class="fas fa-times"></i></button>
                    </div>
                `).join('');
            }
        }

        async function addRule() {
            const k = document.getElementById('rule-keyword').value;
            const r = document.getElementById('rule-response').value;
            if(!r) return alert('回复内容不能为空');
            await fetch('/api/chat.php?action=add_bot_rule', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({keyword: k, response: r})
            });
            loadRules();
        }

        async function deleteRule(id) {
            await fetch(`/api/chat.php?action=delete_bot_rule&id=${id}`);
            loadRules();
        }

        setInterval(loadMessages, 5000);
        loadMessages();
        loadRules();
    </script>
</body>
</html>
