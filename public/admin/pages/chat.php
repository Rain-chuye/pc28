<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客服中心 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .view-hidden { display: none !important; }
        .chat-bubble-admin { background: #4f46e5; color: white; border-radius: 1.25rem 1.25rem 0.25rem 1.25rem; }
        .chat-bubble-user { background: white; color: #1e293b; border-radius: 1.25rem 1.25rem 1.25rem 0.25rem; border: 1px solid #f1f5f9; }
    </style>
</head>
<body class="bg-slate-50">
    <!-- View 1: Conversation List -->
    <div id="view-list">
        <header class="admin-header">
            <h1>客服中心</h1>
            <button onclick="switchView('rules')" class="text-indigo-600 text-xs font-black uppercase"><i class="fas fa-robot mr-1"></i>机器人</button>
        </header>
        <main id="conversation-list" class="space-y-1">
            <div class="p-10 text-center text-slate-300 font-bold text-xs">正在载入咨询列表...</div>
        </main>
    </div>

    <!-- View 2: Chat Detail -->
    <div id="view-detail" class="view-hidden fixed inset-0 z-[2000] bg-white flex flex-col">
        <header class="p-4 border-b flex justify-between items-center bg-white shrink-0">
            <button onclick="switchView('list')" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400"><i class="fas fa-chevron-left"></i></button>
            <h1 id="detail-title" class="font-black text-sm">正在对话</h1>
            <div class="w-10"></div>
        </header>
        <div id="chat-box" class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50/50"></div>
        <div class="p-4 border-t bg-white flex gap-3 shrink-0">
            <input type="text" id="reply-msg" class="flex-1 bg-slate-50 border-none rounded-xl px-5 outline-none font-bold text-sm" placeholder="输入回复内容...">
            <button onclick="sendReply()" class="bg-indigo-600 text-white w-12 h-12 rounded-xl flex items-center justify-center shadow-lg"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- View 3: Robot Rules -->
    <div id="view-rules" class="view-hidden">
        <header class="admin-header">
            <button onclick="switchView('list')" class="text-slate-400"><i class="fas fa-chevron-left"></i></button>
            <h1>机器人规则</h1>
            <div class="w-6"></div>
        </header>
        <main class="p-4 space-y-4">
            <div class="card p-6 space-y-4">
                <input type="text" id="rule-keyword" class="form-input" placeholder="触发关键词 (如: 赔率)">
                <textarea id="rule-response" class="form-input h-20" placeholder="自动回复内容..."></textarea>
                <button onclick="addRule()" class="w-full btn-indigo">添加规则</button>
            </div>
            <div id="rules-list" class="space-y-2"></div>
        </main>
    </div>

    <script>
        let currentUserId = null;
        let view = 'list';

        function switchView(v) {
            view = v;
            document.getElementById('view-list').classList.toggle('view-hidden', v !== 'list');
            document.getElementById('view-detail').classList.toggle('view-hidden', v !== 'detail');
            document.getElementById('view-rules').classList.toggle('view-hidden', v !== 'rules');
            if(v === 'list') loadConversations();
        }

        async function loadConversations() {
            const res = await fetch('/api/chat.php?action=get_all_admin').then(r => r.json());
            if(res.success) {
                const groups = {};
                res.data.forEach(m => {
                    const uid = m.sender_id === 0 ? m.receiver_id : m.sender_id;
                    if(!groups[uid]) groups[uid] = {id: uid, name: m.sender_name || '用户'+uid, last_msg: '', last_time: '', msgs: []};
                    groups[uid].last_msg = m.message;
                    groups[uid].last_time = m.created_at;
                    groups[uid].msgs.push(m);
                });

                const list = document.getElementById('conversation-list');
                const html = Object.values(groups).sort((a,b) => b.id - a.id).map(u => `
                    <div onclick='openChat(${JSON.stringify(u)})' class="bg-white p-6 border-b border-slate-50 flex justify-between items-center active:bg-slate-50 transition-colors">
                        <div class="flex-1 min-w-0 pr-4">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-black text-slate-800">${u.name}</span>
                                <span class="text-[8px] text-slate-300 font-bold">${u.last_time.split(' ')[1]}</span>
                            </div>
                            <p class="text-[10px] text-slate-400 font-bold truncate">${u.last_msg}</p>
                        </div>
                        <i class="fas fa-chevron-right text-[10px] text-slate-200"></i>
                    </div>
                `).join('');
                list.innerHTML = html || '<div class="p-20 text-center text-slate-300 font-bold text-xs">无活跃咨询</div>';
            }
        }

        function openChat(user) {
            currentUserId = user.id;
            document.getElementById('detail-title').innerText = `对话: ${user.name}`;
            renderChat(user.msgs);
            switchView('detail');
        }

        function renderChat(msgs) {
            const box = document.getElementById('chat-box');
            box.innerHTML = msgs.map(m => `
                <div class="flex ${m.sender_id === 0 ? 'justify-end' : ''}">
                    <div class="max-w-[85%] p-4 shadow-sm ${m.sender_id === 0 ? 'chat-bubble-admin' : 'chat-bubble-user'}">
                        <p class="text-xs font-bold">${m.message}</p>
                        <p class="text-[8px] mt-1 opacity-50 font-black uppercase text-right">${m.created_at.split(' ')[1]}</p>
                    </div>
                </div>
            `).join('');
            box.scrollTop = box.scrollHeight;
        }

        async function sendReply() {
            const msg = document.getElementById('reply-msg').value;
            if(!msg) return;
            const res = await fetch('/api/chat.php?action=send_admin', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({user_id: currentUserId, message: msg})
            }).then(r => r.json());
            if(res.success) {
                document.getElementById('reply-msg').value = '';
                // Reload specific chat
                const allRes = await fetch('/api/chat.php?action=get_all_admin').then(r => r.json());
                renderChat(allRes.data.filter(m => m.sender_id == currentUserId || m.receiver_id == currentUserId));
            }
        }

        async function loadRules() {
            const res = await fetch('/api/chat.php?action=get_bot_rules').then(r => r.json());
            if(res.success) {
                document.getElementById('rules-list').innerHTML = res.data.map(r => `
                    <div class="card p-4 flex justify-between items-center">
                        <div class="flex-1 min-w-0 pr-4">
                            <p class="text-[8px] font-black text-indigo-600 uppercase mb-1">关键词: ${r.keyword || '通用'}</p>
                            <p class="text-xs font-bold text-slate-700 truncate">${r.response}</p>
                        </div>
                        <button onclick="deleteRule(${r.id})" class="text-rose-400"><i class="fas fa-trash-alt"></i></button>
                    </div>
                `).join('');
            }
        }

        async function addRule() {
            const k = document.getElementById('rule-keyword').value;
            const r = document.getElementById('rule-response').value;
            if(!r) return alert('内容不能为空');
            await fetch('/api/chat.php?action=add_bot_rule', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({keyword: k, response: r}) });
            document.getElementById('rule-keyword').value = ''; document.getElementById('rule-response').value = '';
            loadRules();
        }

        async function deleteRule(id) {
            await fetch(`/api/chat.php?action=delete_bot_rule&id=${id}`);
            loadRules();
        }

        loadConversations();
        loadRules();
        setInterval(() => { if(view === 'list') loadConversations(); }, 5000);
    </script>
</body>
</html>
