<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>聊天室监控 - 东爷国际</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: sans-serif; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .chat-scroll { flex: 1; overflow-y: auto; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
        .bubble { max-width: 80%; padding: 0.75rem 1rem; border-radius: 1rem; font-size: 13px; font-weight: 500; }
        .bubble-admin { background: #6366f1; color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
        .bubble-user { background: white; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid #f1f5f9; }
        .room-tab { padding: 0.5rem 1.5rem; border-radius: 100px; font-size: 11px; font-weight: 900; text-transform: uppercase; cursor: pointer; transition: all 0.2s; }
        .room-tab.active { background: #6366f1; color: white; }
        .room-tab:not(.active) { color: #94a3b8; }
    </style>
</head>
<body>
    <header class="bg-white border-b border-slate-100 p-6 flex justify-between items-center shrink-0 shadow-sm">
        <div class="flex items-center gap-6">
            <h1 class="text-lg font-black text-slate-800">公共聊天室监控</h1>
            <div class="flex bg-slate-50 p-1 rounded-full border border-slate-100">
                <div id="tab-low" onclick="switchRoom('low')" class="room-tab">标准房</div>
                <div id="tab-high" onclick="switchRoom('high')" class="room-tab active">至尊房</div>
            </div>
        </div>
        <div class="flex gap-3">
            <button onclick="openRedPacketModal()" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest"><i class="fas fa-gift mr-1"></i> 派发红包</button>
            <button onclick="clearRoom()" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fas fa-trash-alt"></i></button>
        </div>
    </header>

    <main id="chat-box" class="chat-scroll no-scrollbar bg-slate-50/50"></main>

    <footer class="p-6 bg-white border-t border-slate-100 shadow-2xl shrink-0">
        <div class="max-w-4xl mx-auto flex gap-4">
            <input type="text" id="admin-msg" class="flex-1 bg-slate-50 border-none rounded-2xl px-6 outline-none font-bold text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/10 transition-all" placeholder="以管理员身份发言..." onkeydown="if(event.key==='Enter') sendMsg()">
            <button onclick="sendMsg()" class="bg-indigo-600 text-white px-8 rounded-2xl font-black text-xs uppercase shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-transform active:scale-95">发送</button>
        </div>
    </footer>

    <div id="rpModal" class="fixed inset-0 z-[2000] bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-6">
        <div class="bg-white rounded-[2.5rem] w-full max-w-sm p-8 space-y-6 shadow-2xl">
            <div class="text-center">
                <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-3xl flex items-center justify-center mx-auto text-2xl mb-4"><i class="fas fa-gift"></i></div>
                <h2 class="text-xl font-black text-slate-800">发送全服红包</h2>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Global Red Packet</p>
            </div>
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-1">红包总金额</label>
                    <input type="number" id="rp-amount" class="w-full bg-slate-50 border-none rounded-xl p-4 font-black text-slate-800 outline-none" value="100">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-1">红包总个数</label>
                    <input type="number" id="rp-count" class="w-full bg-slate-50 border-none rounded-xl p-4 font-black text-slate-800 outline-none" value="10">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-1">领取流水要求</label>
                    <input type="number" id="rp-req" class="w-full bg-slate-50 border-none rounded-xl p-4 font-black text-slate-800 outline-none" value="0">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="closeRp()" class="flex-1 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black text-xs uppercase">取消</button>
                <button onclick="submitRp()" class="flex-1 py-4 bg-rose-500 text-white rounded-2xl font-black text-xs uppercase shadow-lg shadow-rose-100">立即派发</button>
            </div>
        </div>
    </div>

    <script>
        let currentRoom = 'high';
        function switchRoom(r) {
            currentRoom = r;
            document.querySelectorAll('.room-tab').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-'+r).classList.add('active');
            loadMsgs();
        }

        async function loadMsgs() {
            const res = await fetch(`/admin/api/chat_management.php?action=get&room=${currentRoom}`).then(r => r.json());
            if(res.success) {
                const box = document.getElementById('chat-box');
                const wasAtBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 100;
                box.innerHTML = res.data.reverse().map(m => {
                    const isSystem = m.user_id == 0;
                    return `
                        <div class="bubble ${isSystem ? 'bubble-admin' : 'bubble-user'}">
                            <div class="flex items-center gap-2 mb-1 opacity-50 text-[9px] font-black uppercase">
                                <span>${m.username}</span>
                                <span>${m.created_at.split(' ')[1]}</span>
                            </div>
                            <p class="font-bold leading-relaxed">${m.message.replace(/\n/g, '<br>')}</p>
                        </div>
                    `;
                }).join('');
                if(wasAtBottom) box.scrollTop = box.scrollHeight;
            }
        }

        async function sendMsg() {
            const input = document.getElementById('admin-msg');
            if(!input.value) return;
            await fetch(`/admin/api/chat_management.php?action=send_broadcast&room=${currentRoom}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ message: input.value, type: 'text' })
            });
            input.value = ''; loadMsgs();
        }

        async function clearRoom() {
            if(!confirm('确定要清空该房间所有聊天记录吗？')) return;
            await fetch(`/admin/api/chat_management.php?action=clear&room=${currentRoom}`);
            loadMsgs();
        }

        function openRedPacketModal() { document.getElementById('rpModal').classList.remove('hidden'); }
        function closeRp() { document.getElementById('rpModal').classList.add('hidden'); }
        async function submitRp() {
            const amount = document.getElementById('rp-amount').value;
            const count = document.getElementById('rp-count').value;
            const req = document.getElementById('rp-req').value;
            await fetch(`/admin/api/chat_management.php?action=send_broadcast&room=${currentRoom}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ type: 'red_packet', amount, count, min_turnover: req })
            });
            closeRp(); loadMsgs();
        }

        setInterval(loadMsgs, 3000);
        switchRoom('high');
    </script>
</body>
</html>
