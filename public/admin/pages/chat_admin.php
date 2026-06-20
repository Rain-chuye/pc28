<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>聊天室监控 - PC28 管理后台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .room-tab.active { border-color: #4f46e5; color: #4f46e5; background: #f5f3ff; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="admin-header">
        <h1>聊天监控与管理</h1>
        <div class="flex gap-2">
            <button onclick="clearRoom()" class="bg-rose-500 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase"><i class="fas fa-trash-alt mr-1"></i> 清空当前频道</button>
        </div>
    </header>

    <div class="p-4 flex gap-2 bg-white sticky top-[60px] z-[800] shadow-sm">
        <button onclick="switchRoom('high')" id="tab-high" class="room-tab flex-1 py-3 border-b-4 border-transparent font-black text-xs uppercase active">🇨🇦 加拿大高倍</button>
        <button onclick="switchRoom('low')" id="tab-low" class="room-tab flex-1 py-3 border-b-4 border-transparent font-black text-xs uppercase text-slate-400">🇨🇦 加拿大标准</button>
        <button onclick="switchRoom('red_packet')" id="tab-red_packet" class="room-tab flex-1 py-3 border-b-4 border-transparent font-black text-xs uppercase text-slate-400">🧧 福利红包房</button>
    </div>

    <main class="max-w-2xl mx-auto p-4 space-y-4" id="chat-stream">
        <div class="p-10 text-center text-slate-300 font-bold text-xs">正在实时同步消息...</div>
    </main>

    <div class="fixed bottom-[80px] left-0 right-0 p-4 bg-white border-t border-slate-100 z-[900] space-y-4">
        <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
            <button onclick="addEmoji('🧧')" class="p-2 bg-slate-50 rounded-lg text-lg">🧧</button>
            <button onclick="addEmoji('🚀')" class="p-2 bg-slate-50 rounded-lg text-lg">🚀</button>
            <button onclick="addEmoji('💰')" class="p-2 bg-slate-50 rounded-lg text-lg">💰</button>
            <button onclick="addEmoji('🎉')" class="p-2 bg-slate-50 rounded-lg text-lg">🎉</button>
            <button onclick="openRedPacketModal()" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase">派发红包</button>
        </div>
        <div class="flex gap-3">
            <input type="text" id="admin-msg" class="flex-1 bg-slate-100 border-none rounded-xl px-5 outline-none font-bold text-sm" placeholder="以系统身份发送消息...">
            <button onclick="sendMsg()" class="bg-indigo-600 text-white w-12 h-12 rounded-xl flex items-center justify-center shadow-lg"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Red Packet Modal -->
    <div id="rpModal" class="fixed inset-0 z-[2000] bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-6">
        <div class="bg-white rounded-3xl p-8 w-full max-w-sm">
            <h3 class="font-black text-slate-800 mb-6 uppercase text-xs tracking-widest">发布群红包</h3>
            <div class="space-y-4">
                <input type="number" id="rp-amount" class="form-input" placeholder="总金额 (¥)">
                <input type="number" id="rp-count" class="form-input" placeholder="红包个数">
                <input type="number" id="rp-req" class="form-input" placeholder="流水门槛 (¥, 可不填)">
            </div>
            <div class="flex gap-4 mt-8">
                <button onclick="closeRp()" class="flex-1 py-3 text-slate-400 font-black text-[10px]">取消</button>
                <button onclick="submitRp()" class="flex-1 py-3 bg-orange-500 text-white rounded-xl font-black text-[10px]">确认派发</button>
            </div>
        </div>
    </div>

    <script>
        let currentRoom = 'high';
        function switchRoom(r) {
            currentRoom = r;
            document.querySelectorAll('.room-tab').forEach(t => {
                t.classList.toggle('active', t.id === 'tab-'+r);
                t.classList.toggle('text-slate-400', t.id !== 'tab-'+r);
            });
            loadMsgs();
        }

        async function loadMsgs() {
            const res = await fetch(`/admin/api/chat_management.php?action=get&room=${currentRoom}`).then(r => r.json());
            if(res.success) {
                document.getElementById('chat-stream').innerHTML = res.data.map(m => `
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center font-black text-[10px] text-slate-400">${m.username[0]}</div>
                        <div class="flex-1">
                            <div class="flex justify-between items-center mb-1">
                                <p class="text-[9px] font-black text-slate-400 uppercase">${m.username} <span class="ml-2 opacity-50">${m.created_at}</span></p>
                            </div>
                            <p class="text-xs font-bold text-slate-700 leading-relaxed">${m.message.replace(/\n/g, '<br>')}</p>
                        </div>
                    </div>
                `).join('');
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

        function addEmoji(e) { document.getElementById('admin-msg').value += e; }
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
