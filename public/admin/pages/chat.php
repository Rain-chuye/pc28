<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>私聊客服管理 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .chat-img-admin { max-width: 150px; border-radius: 0.5rem; cursor: zoom-in; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="admin-header">
        <h1>客服私聊中心</h1>
        <div class="flex gap-2">
            <button onclick="clearAllChat()" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase">清空全部</button>
            <button onclick="loadAllChat()" class="text-indigo-600"><i class="fas fa-sync-alt"></i></button>
        </div>
    </header>

    <main id="chat-list" class="p-4 space-y-4 pb-32">
        <div class="p-10 text-center text-slate-300 font-bold text-xs uppercase italic">SYNCING PRIVATE LOGS...</div>
    </main>

    <div id="reply-panel" class="fixed bottom-[80px] left-0 right-0 bg-white border-t border-slate-100 p-4 shadow-xl z-[900] hidden">
        <div class="max-w-2xl mx-auto space-y-4">
            <div class="flex justify-between items-center px-1">
                <p class="text-[9px] font-black text-slate-400 uppercase">正在回复 UID: <span id="reply-uid"></span></p>
                <button onclick="clearSingleChat()" class="text-rose-500 font-black text-[9px] uppercase tracking-widest"><i class="fas fa-eraser mr-1"></i> 清除此人记录</button>
            </div>
            <div id="reply-preview" class="hidden relative inline-block">
                <img id="reply-img-preview" class="w-16 h-16 object-cover rounded-lg border border-slate-100">
                <button onclick="clearReplyImg()" class="absolute -top-1 -right-1 bg-rose-500 text-white w-4 h-4 rounded-full text-[8px]"><i class="fas fa-times"></i></button>
            </div>
            <div class="flex gap-3">
                <button onclick="document.getElementById('reply-file').click()" class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center border border-slate-100"><i class="fas fa-image"></i></button>
                <input type="file" id="reply-file" class="hidden" accept="image/*" onchange="handleReplyImg(this)">
                <input type="text" id="reply-msg" class="flex-1 bg-slate-100 border-none rounded-xl px-5 outline-none font-bold text-sm" placeholder="回复该会员...">
                <button onclick="sendReply()" class="bg-indigo-600 text-white w-12 h-12 rounded-xl flex items-center justify-center shadow-lg"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script>
        let currentTargetId = null;
        let replyImageBase64 = '';

        async function loadAllChat() {
            const res = await fetch('/api/chat.php?action=get_all_admin').then(r => r.json());
            if(res.success) {
                const container = document.getElementById('chat-list');
                const groups = {};
                res.data.forEach(m => {
                    const uid = m.sender_id == 0 ? m.receiver_id : m.sender_id;
                    if(!groups[uid]) groups[uid] = [];
                    groups[uid].push(m);
                });

                container.innerHTML = Object.entries(groups).map(([uid, msgs]) => {
                    const last = msgs[msgs.length - 1];
                    const name = last.sender_name || '会员' + uid;
                    return `
                        <div class="card p-5 border border-slate-100 hover:border-indigo-200 transition-all cursor-pointer" onclick="openReply(${uid})">
                            <div class="flex justify-between items-start mb-3">
                                <p class="text-xs font-black text-slate-800 uppercase tracking-tighter">${name} (UID: ${uid})</p>
                                <span class="text-[8px] font-black text-slate-300 uppercase">${last.created_at}</span>
                            </div>
                            <div class="space-y-3 max-h-40 overflow-y-auto no-scrollbar pr-2">
                                ${msgs.slice(-5).map(m => {
                                    const isSystem = m.sender_id == 0;
                                    const isImg = m.type === 'image';
                                    const body = isImg ? `<img src="${m.message}" class="chat-img-admin" onclick="event.stopPropagation();window.open(this.src)">` : m.message;
                                    return `<div class="flex ${isSystem ? 'justify-end' : ''}"><div class="max-w-[80%] p-2.5 rounded-xl text-[10px] font-bold ${isSystem ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-50 text-slate-600'}">${body}</div></div>`;
                                }).join('')}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        function openReply(uid) {
            currentTargetId = uid;
            document.getElementById('reply-uid').innerText = uid;
            document.getElementById('reply-panel').classList.remove('hidden');
            document.getElementById('reply-msg').focus();
        }

        async function clearSingleChat() {
            if(!confirm('确定清除该用户的聊天记录吗？')) return;
            await fetch(`/api/chat.php?action=clear_private&user_id=${currentTargetId}`);
            document.getElementById('reply-panel').classList.add('hidden');
            loadAllChat();
        }

        async function clearAllChat() {
            if(!confirm('危险！确定清除所有会员的客服私聊记录吗？')) return;
            await fetch(`/api/chat.php?action=clear_private&user_id=0`);
            loadAllChat();
        }

        function handleReplyImg(input) {
            const file = input.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.src = e.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const max = 800;
                    let w = img.width, h = img.height;
                    if(w > h) { if(w > max) { h *= max/w; w = max; } } else { if(h > max) { w *= max/h; h = max; } }
                    canvas.width = w; canvas.height = h;
                    ctx.drawImage(img, 0, 0, w, h);
                    replyImageBase64 = canvas.toDataURL('image/jpeg', 0.6);
                    document.getElementById('reply-img-preview').src = replyImageBase64;
                    document.getElementById('reply-preview').classList.remove('hidden');
                };
            };
            reader.readAsDataURL(file);
        }

        function clearReplyImg() { replyImageBase64 = ''; document.getElementById('reply-preview').classList.add('hidden'); }

        async function sendReply() {
            const msg = replyImageBase64 || document.getElementById('reply-msg').value.trim();
            const type = replyImageBase64 ? 'image' : 'text';
            if(!msg || !currentTargetId) return;

            const res = await fetch('/api/chat.php?action=send_admin', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_id: currentTargetId, message: msg, type: type })
            }).then(r => r.json());

            if(res.success) {
                document.getElementById('reply-msg').value = '';
                clearReplyImg();
                loadAllChat();
            } else alert(res.message);
        }

        loadAllChat();
        setInterval(loadAllChat, 10000);
    </script>
</body>
</html>
